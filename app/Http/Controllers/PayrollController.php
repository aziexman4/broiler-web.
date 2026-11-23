<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Flock;
use App\Models\Payroll;
use App\Models\User;
use App\Services\FlockMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollController extends Controller
{
    /**
     * Display payroll management dashboard and history with IP-based bonus evaluation.
     */
    public function index()
    {
        // Fetch worker users (or all non-owner users / all users as fallback)
        $workers = User::where('role', 'worker')->orderBy('name', 'asc')->get();
        if ($workers->isEmpty()) {
            $workers = User::all();
        }

        // Fetch closed flocks with worker, harvests, and daily records
        $closedFlocks = Flock::with(['worker', 'harvests', 'dailyRecords'])
            ->where('status', 'closed')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($closedFlocks as $flock) {
            $metrics = $this->calculateFlockIpAndBonus($flock, $flock->worker);

            $flock->worker_name = $flock->worker->name ?? 'Belum Ditugaskan';
            $flock->worker_base_salary = $metrics['base_salary'];
            $flock->total_chickens_sold = $metrics['total_chickens_sold'];
            $flock->total_weight_kg = $metrics['total_weight_kg'];
            $flock->total_feed_kg = $metrics['total_feed_kg'];
            $flock->harvest_age = $metrics['harvest_age'];
            $flock->achieved_fcr = $metrics['fcr'];
            $flock->achieved_ip = $metrics['ip'];
            $flock->bonus_percentage = $metrics['bonus_percentage'];
            $flock->bonus_tier_label = $metrics['bonus_tier_label'];
            $flock->recommended_bonus = $metrics['recommended_bonus'];
        }

        // Fetch payroll histories eager loading user & flock
        $payrolls = Payroll::with(['user', 'flock'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('payrolls.index', compact('workers', 'closedFlocks', 'payrolls'));
    }

    /**
     * Store a newly created payroll entry and calculate IP performance bonus.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'worker')],
            'flock_id' => ['required', 'exists:flocks,id'],
            'base_salary' => ['nullable', 'integer', 'min:0'],
            'bonus_amount' => ['nullable', 'integer', 'min:0'],
        ], [
            'user_id.exists' => 'Pekerja tidak valid atau tidak berstatus worker.',
        ]);

        $worker = User::findOrFail($validated['user_id']);
        $flock = Flock::with(['harvests', 'dailyRecords'])->findOrFail($validated['flock_id']);

        if ($flock->status !== 'closed') {
            return redirect()->route('payrolls.index')
                ->with('error', "Gaji siklus {$flock->name} belum dapat diterbitkan karena siklus masih berstatus Active (belum panen/tutup buku).");
        }

        $metrics = $this->calculateFlockIpAndBonus($flock, $worker);

        $baseSalary = isset($validated['base_salary']) && $validated['base_salary'] !== null
            ? (int) $validated['base_salary']
            : $metrics['base_salary'];

        $bonusAmount = isset($validated['bonus_amount']) && $validated['bonus_amount'] !== null
            ? (int) $validated['bonus_amount']
            : $metrics['recommended_bonus'];

        $totalPaid = $baseSalary + $bonusAmount;
        $ip = $metrics['ip'];
        $fcr = $metrics['fcr'];

        $duplicateError = null;

        DB::transaction(function () use ($worker, $flock, $baseSalary, $bonusAmount, $totalPaid, &$duplicateError) {
            Flock::lockForUpdate()->findOrFail($flock->id);

            $alreadyPaid = Payroll::where('flock_id', $flock->id)
                ->where('user_id', $worker->id)
                ->exists();

            if ($alreadyPaid) {
                $duplicateError = "Slip gaji untuk pekerja {$worker->name} pada siklus {$flock->name} sudah pernah diterbitkan sebelumnya.";
                return;
            }

            Payroll::create([
                'user_id' => $worker->id,
                'flock_id' => $flock->id,
                'base_salary' => $baseSalary,
                'bonus_amount' => $bonusAmount,
                'total_paid' => $totalPaid,
                'payment_date' => now()->toDateString(),
            ]);

            if ($totalPaid > 0) {
                CashFlow::create([
                    'transaction_date' => now()->toDateString(),
                    'description' => "Pembayaran Gaji & Bonus: {$worker->name} ({$flock->name})",
                    'type' => 'out',
                    'amount' => $totalPaid,
                ]);
            }
        });

        if ($duplicateError !== null) {
            return redirect()->route('payrolls.index')->with('error', $duplicateError);
        }

        $statusMsg = $bonusAmount > 0
            ? "Gaji berhasil diterbitkan! Pekerja {$worker->name} mendapatkan bonus performa Rp " . number_format($bonusAmount, 0, ',', '.') . " (IP: {$ip}, FCR: {$fcr}, Kategori: {$metrics['bonus_tier_label']})."
            : "Gaji berhasil diterbitkan tanpa bonus performa (IP: {$ip}, FCR: {$fcr}).";

        return redirect()->route('payrolls.index')->with('success', $statusMsg);
    }

    /**
     * Calculate productivity metrics (FCR, IP) and bonus tier for a closed flock.
     */
    private function calculateFlockIpAndBonus(Flock $flock, ?User $worker = null): array
    {
        $metrics = FlockMetricsService::calculateForFlock($flock);
        $baseSalary = ($worker && $worker->base_salary > 0) ? (int) $worker->base_salary : 2500000;
        $bonus = FlockMetricsService::evaluateIpBonus($metrics['ip'], $baseSalary);

        return [
            'total_chickens_sold' => $metrics['total_harvest_sold'],
            'total_weight_kg' => $metrics['total_harvest_weight_kg'],
            'total_feed_kg' => $metrics['total_feed_kg'],
            'harvest_age' => $metrics['harvest_age'],
            'fcr' => $metrics['fcr'],
            'ip' => $metrics['ip'],
            'base_salary' => $baseSalary,
            'bonus_percentage' => $bonus['bonus_percentage'],
            'bonus_tier_label' => $bonus['bonus_tier_label'],
            'recommended_bonus' => $bonus['recommended_bonus'],
        ];
    }

    /**
     * Display the authenticated worker's own payroll and IP bonus history.
     */
    public function workerIndex(Request $request)
    {
        $user = $request->user();

        $payrolls = Payroll::with(['user', 'flock.coop'])
            ->where('user_id', $user->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('payrolls.worker-index', compact('payrolls'));
    }

    /**
     * Preview Payslip as printable HTML or download as PDF.
     */
    public function payslipPreview(Request $request, Payroll $payroll)
    {
        $user = auth()->user();
        abort_if(
            !$user->isOwner() && (int) $payroll->user_id !== (int) $user->id,
            403,
            'Anda tidak memiliki izin mengakses slip gaji pekerja lain.'
        );

        $payroll->load(['user', 'flock.coop']);
        $coop = $payroll->flock?->coop;

        if ($request->boolean('download')) {
            $slipNo = 'PAY-' . str_pad((string) $payroll->id, 5, '0', STR_PAD_LEFT);
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.payslip-preview', compact('payroll', 'coop') + ['isPdf' => true])
                ->setPaper('a4', 'portrait')
                ->download("slip_gaji_{$slipNo}.pdf");
        }

        return view('reports.payslip-preview', compact('payroll', 'coop'));
    }
}
