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

        // Fetch closed flocks with worker, coop, harvests, daily records, and issued payrolls
        $closedFlocks = Flock::with(['worker', 'coop', 'harvests', 'dailyRecords', 'payrolls.user'])
            ->where('status', 'closed')
            ->orderByDesc('closed_at')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($closedFlocks as $flock) {
            $metrics = $this->calculateFlockIpAndBonus($flock, $flock->worker);
            $paidWorkerIds = $flock->payrolls
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $flock->worker_name = $flock->worker->name ?? 'Belum Ditugaskan';
            $flock->coop_name = $flock->coop->name ?? 'Tanpa Unit';
            $flock->worker_base_salary = $metrics['base_salary'];
            $flock->total_chickens_sold = $metrics['total_chickens_sold'];
            $flock->total_weight_kg = $metrics['total_weight_kg'];
            $flock->total_feed_kg = $metrics['total_feed_kg'];
            $flock->livability = $metrics['livability'];
            $flock->harvest_age = $metrics['harvest_age'];
            $flock->achieved_fcr = $metrics['fcr'];
            $flock->achieved_ip = $metrics['ip'];
            $flock->net_profit = $metrics['net_profit'];
            $flock->bonus_percentage = $metrics['bonus_percentage'];
            $flock->bonus_tier_label = $metrics['bonus_tier_label'];
            $flock->recommended_bonus = $metrics['recommended_bonus'];
            $flock->tonnage_rate_per_kg = $metrics['tonnage_rate_per_kg'];
            $flock->recommended_tonnage_bonus = $metrics['recommended_tonnage_bonus'];
            $flock->paid_worker_ids = $paidWorkerIds;
            $flock->is_assigned_worker_paid = $flock->worker_id
                ? in_array((int) $flock->worker_id, $paidWorkerIds, true)
                : $flock->payrolls->isNotEmpty();
        }

        // Put unpaid closed cycles at the top so Owner sees ready-to-pay cycles first
        $closedFlocks = $closedFlocks->sortBy(fn ($f) => $f->is_assigned_worker_paid ? 1 : 0)->values();

        // Fetch payroll histories eager loading user & flock performance context
        $payrolls = Payroll::with(['user', 'flock.coop', 'flock.harvests', 'flock.dailyRecords'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $payrolls->getCollection()->transform(function (Payroll $payroll) {
            if ($payroll->flock) {
                $m = FlockMetricsService::calculateForFlock($payroll->flock, $payroll->flock->dailyRecords, $payroll->flock->harvests);
                $tier = FlockMetricsService::evaluateIpBonus($m['ip'], (int) $payroll->base_salary, (float) $m['total_harvest_weight_kg']);
                $payroll->achieved_ip = $m['ip'];
                $payroll->bonus_tier_label = $tier['bonus_tier_label'];
                $payroll->bonus_percentage = $tier['bonus_percentage'];
            }
            return $payroll;
        });

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
     * Calculate productivity metrics (FCR, IP, Net Profit) and bonus tiers for a closed flock.
     */
    private function calculateFlockIpAndBonus(Flock $flock, ?User $worker = null): array
    {
        $metrics = FlockMetricsService::calculateForFlock($flock, $flock->dailyRecords, $flock->harvests);
        $baseSalary = ($worker && $worker->base_salary > 0) ? (int) $worker->base_salary : 2500000;
        $bonus = FlockMetricsService::evaluateIpBonus($metrics['ip'], $baseSalary, (float) $metrics['total_harvest_weight_kg']);

        return [
            'total_chickens_sold' => $metrics['total_harvest_sold'],
            'total_weight_kg' => $metrics['total_harvest_weight_kg'],
            'total_feed_kg' => $metrics['total_feed_kg'],
            'livability' => $metrics['livability'],
            'harvest_age' => $metrics['harvest_age'],
            'fcr' => $metrics['fcr'],
            'ip' => $metrics['ip'],
            'net_profit' => $metrics['net_profit'],
            'base_salary' => $baseSalary,
            'bonus_percentage' => $bonus['bonus_percentage'],
            'bonus_tier_label' => $bonus['bonus_tier_label'],
            'recommended_bonus' => $bonus['recommended_bonus'],
            'tonnage_rate_per_kg' => $bonus['tonnage_rate_per_kg'],
            'recommended_tonnage_bonus' => $bonus['recommended_tonnage_bonus'],
        ];
    }

    /**
     * Display the authenticated worker's own payroll and IP bonus history.
     */
    public function workerIndex(Request $request)
    {
        $user = $request->user();

        $payrolls = Payroll::with(['user', 'flock.coop', 'flock.harvests', 'flock.dailyRecords'])
            ->where('user_id', $user->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (Payroll $payroll) {
                if ($payroll->flock) {
                    $m = FlockMetricsService::calculateForFlock($payroll->flock, $payroll->flock->dailyRecords, $payroll->flock->harvests);
                    $tier = FlockMetricsService::evaluateIpBonus($m['ip'], (int) $payroll->base_salary, (float) $m['total_harvest_weight_kg']);
                    $payroll->achieved_ip = $m['ip'];
                    $payroll->bonus_tier_label = $tier['bonus_tier_label'];
                    $payroll->bonus_percentage = $tier['bonus_percentage'];
                }
                return $payroll;
            });

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

        $payroll->load(['user', 'flock.coop', 'flock.harvests', 'flock.dailyRecords']);
        $coop = $payroll->flock?->coop;
        $flockMetrics = $payroll->flock
            ? FlockMetricsService::calculateForFlock($payroll->flock, $payroll->flock->dailyRecords, $payroll->flock->harvests)
            : null;
        $bonusEval = $flockMetrics
            ? FlockMetricsService::evaluateIpBonus($flockMetrics['ip'], (int) $payroll->base_salary, (float) $flockMetrics['total_harvest_weight_kg'])
            : null;

        $viewData = compact('payroll', 'coop', 'flockMetrics', 'bonusEval');

        if ($request->boolean('download')) {
            $slipNo = 'PAY-' . str_pad((string) $payroll->id, 5, '0', STR_PAD_LEFT);
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.payslip-preview', $viewData + ['isPdf' => true])
                ->setPaper('a4', 'portrait')
                ->download("slip_gaji_{$slipNo}.pdf");
        }

        return view('reports.payslip-preview', $viewData);
    }
}
