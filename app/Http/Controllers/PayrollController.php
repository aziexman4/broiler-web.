<?php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display payroll management dashboard and history.
     */
    public function index()
    {
        // Fetch worker users (or all non-owner users / all users as fallback)
        $workers = User::where('role', 'worker')->get();
        if ($workers->isEmpty()) {
            $workers = User::all();
        }

        // Fetch closed flocks
        $closedFlocks = Flock::where('status', 'closed')->orderBy('name', 'asc')->get();

        // Fetch payroll histories eager loading user & flock
        $payrolls = Payroll::with(['user', 'flock'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('payrolls.index', compact('workers', 'closedFlocks', 'payrolls'));
    }

    /**
     * Store a newly created payroll entry and calculate IP performance bonus.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'flock_id' => 'required|exists:flocks,id',
        ]);

        $worker = User::findOrFail($validated['user_id']);
        $flock = Flock::with(['harvests', 'dailyRecords'])->findOrFail($validated['flock_id']);

        // Calculate Performance Index (IP) for the chosen flock
        $dailyRecords = $flock->dailyRecords;
        $harvests = $flock->harvests;

        $totalMortality = $dailyRecords->sum('mortality');
        $totalCulling = $dailyRecords->sum('culling');
        $totalFeed = $dailyRecords->sum('feed_consumed_kg');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        $totalHarvestSold = $harvests->sum('total_chickens_sold');
        $totalHarvestWeight = $harvests->sum('total_weight_kg');

        $fcr = $totalHarvestWeight > 0 ? ($totalFeed / $totalHarvestWeight) : 0;
        $avgWeightKg = $totalHarvestSold > 0 ? ($totalHarvestWeight / $totalHarvestSold) : 0;
        $livability = $flock->initial_population > 0 ? (($livePopulation / $flock->initial_population) * 100) : 0;

        $startDate = \Carbon\Carbon::parse($flock->start_date);
        $endDate = $harvests->count() > 0 
            ? \Carbon\Carbon::parse($harvests->max('harvest_date')) 
            : \Carbon\Carbon::now();
        
        $harvestAge = max(1, $startDate->diffInDays($endDate));
        $ip = ($fcr > 0 && $harvestAge > 0) ? round(($livability * $avgWeightKg) / ($fcr * $harvestAge) * 100, 2) : 0;

        // Bonus calculation logic: If IP > 300, bonus = 500,000 Rp, else 0
        $bonusAmount = ($ip > 300) ? 500000 : 0;
        $baseSalary = $worker->base_salary > 0 ? $worker->base_salary : 2500000;
        $totalPaid = $baseSalary + $bonusAmount;

        Payroll::create([
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => $baseSalary,
            'bonus_amount' => $bonusAmount,
            'total_paid' => $totalPaid,
            'payment_date' => now()->toDateString(),
        ]);

        $statusMsg = $bonusAmount > 0
            ? "Gaji berhasil diterbitkan! Pekerja mendapatkan bonus performa Rp " . number_format($bonusAmount, 0, ',', '.') . " (IP: {$ip})."
            : "Gaji berhasil diterbitkan tanpa bonus performa (IP: {$ip}).";

        return redirect()->route('payrolls.index')->with('success', $statusMsg);
    }

    /**
     * Preview Payslip as printable HTML.
     */
    public function payslipPreview(Payroll $payroll)
    {
        $payroll->load(['user', 'flock']);

        return view('reports.payslip-preview', compact('payroll'));
    }
}
