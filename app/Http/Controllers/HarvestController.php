<?php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Models\Harvest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class HarvestController extends Controller
{
    /**
     * Display global financial and harvest history dashboard.
     */
    public function index()
    {
        $harvests = Harvest::with('flock')
            ->orderBy('harvest_date', 'asc')
            ->get();

        $total_net_profit = $harvests->sum('net_profit');
        $total_revenue = $harvests->sum('gross_revenue');
        $total_weight = $harvests->sum('total_weight_kg');

        $chartLabels = $harvests->map(function ($harvest) {
            $flockName = $harvest->flock ? $harvest->flock->name : 'Kandang';
            $date = \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y');
            return "{$flockName} ({$date})";
        });

        $chartData = $harvests->pluck('net_profit');

        return view('harvests.history', compact(
            'harvests',
            'total_net_profit',
            'total_revenue',
            'total_weight',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Show the form for creating a new harvest record for a flock.
     */
    public function create(Flock $flock)
    {
        Gate::authorize('update', $flock);

        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        return view('harvests.create', compact('flock', 'livePopulation'));
    }

    /**
     * Store a newly created harvest record and automatically calculate finances & close flock.
     */
    public function store(Request $request, Flock $flock)
    {
        Gate::authorize('update', $flock);

        $validated = $request->validate([
            'harvest_date' => 'required|date',
            'total_weight_kg' => 'required|numeric|min:0.01',
            'chicken_price_per_kg' => 'required|numeric|min:0',
            'feed_price_per_kg' => 'required|numeric|min:0',
            'buyer_name' => 'required|string|max:255',
        ]);

        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $totalChickensSold = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        $totalFeedConsumed = (float) $flock->dailyRecords()->sum(DB::raw('feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg'));
        $totalFeedCost = $totalFeedConsumed * $validated['feed_price_per_kg'];
        $grossRevenue = $validated['total_weight_kg'] * $validated['chicken_price_per_kg'];
        
        $docPrice = \App\Models\SystemSetting::where('key', 'doc_price')->first()->value ?? 8000;
        $totalDocCost = $flock->initial_population * $docPrice;
        
        $netProfit = $grossRevenue - $totalFeedCost - $totalDocCost;

        $harvest = $flock->harvests()->create([
            'harvest_date' => $validated['harvest_date'],
            'total_chickens_sold' => $totalChickensSold,
            'total_weight_kg' => $validated['total_weight_kg'],
            'buyer_name' => $validated['buyer_name'],
            'chicken_price_per_kg' => $validated['chicken_price_per_kg'],
            'feed_price_per_kg' => $validated['feed_price_per_kg'],
            'total_feed_cost' => $totalFeedCost,
            'gross_revenue' => $grossRevenue,
            'net_profit' => $netProfit,
            'revenue' => $grossRevenue,
        ]);

        $flock->update([
            'status' => 'closed',
            'closed_at' => $validated['harvest_date'],
        ]);

        return redirect()->route('flocks.show', $flock)
            ->with('success', 'Data panen berhasil disimpan. Kandang ini sekarang ditutup.');
    }

    /**
     * Auto-harvest using existing projected data (1-Click Harvest)
     */
    public function autoStore(Flock $flock)
    {
        Gate::authorize('update', $flock);

        // Calculate live population
        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        // Get latest weight
        $latestRecord = $flock->dailyRecords()->orderBy('record_date', 'desc')->first();
        // Fallback to average 1.6kg if no daily records
        $avgWeightGram = $latestRecord->average_weight_gram ?? 1600; 
        
        $totalWeightKg = ($livePopulation * $avgWeightGram) / 1000;
        
        $marketPrice = \App\Models\SystemSetting::where('key', 'global_market_price')->first()->value ?? 23000;
        $feedPrice = \App\Models\SystemSetting::where('key', 'feed_price')->first()->value ?? 9000;
        $docPrice = \App\Models\SystemSetting::where('key', 'doc_price')->first()->value ?? 8000;
        
        $totalFeedConsumed = (float) $flock->dailyRecords()->sum(DB::raw('feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg'));
        $totalFeedCost = $totalFeedConsumed * $feedPrice;
        $totalDocCost = $flock->initial_population * $docPrice;
        
        $grossRevenue = $totalWeightKg * $marketPrice;
        $netProfit = $grossRevenue - $totalFeedCost - $totalDocCost;

        $flock->harvests()->create([
            'harvest_date' => now()->format('Y-m-d'),
            'total_chickens_sold' => $livePopulation,
            'total_weight_kg' => round($totalWeightKg, 2),
            'buyer_name' => 'Auto-Harvest (Proyeksi Otomatis)',
            'chicken_price_per_kg' => $marketPrice,
            'feed_price_per_kg' => $feedPrice,
            'total_feed_cost' => $totalFeedCost,
            'gross_revenue' => $grossRevenue,
            'net_profit' => $netProfit,
            'revenue' => $grossRevenue,
        ]);

        $flock->update([
            'status' => 'closed',
            'closed_at' => now()->format('Y-m-d'),
        ]);

        return redirect()->route('flocks.show', $flock)
            ->with('success', 'Panen berhasil diselesaikan secara otomatis menggunakan data proyeksi harian!');
    }

    /**
     * Export Flock harvest report to printable PDF using DomPDF.
     */
    public function exportPdf(Flock $flock)
    {
        Gate::authorize('view', $flock);

        $flock->load(['coop', 'dailyRecords', 'harvests']);
        $coop = $flock->coop;
        $harvest = $flock->harvests()->latest('harvest_date')->first();

        $dailyRecords = $flock->dailyRecords;
        $totalMortality = $dailyRecords->sum('mortality');
        $totalCulling = $dailyRecords->sum('culling');
        $totalFeedLegacy = $dailyRecords->sum('feed_consumed_kg');
        $totalFeedPhased = $dailyRecords->sum('feed_pre_starter_kg') + $dailyRecords->sum('feed_starter_kg') + $dailyRecords->sum('feed_finisher_kg');
        $totalFeed = $totalFeedLegacy > 0 ? $totalFeedLegacy : $totalFeedPhased;
        
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        $totalHarvestSold = $harvest ? $harvest->total_chickens_sold : 0;
        $totalHarvestWeight = $harvest ? $harvest->total_weight_kg : 0;

        $fcr = $totalHarvestWeight > 0 ? round($totalFeed / $totalHarvestWeight, 2) : 0;
        $avgWeight = $totalHarvestSold > 0 ? round($totalHarvestWeight / $totalHarvestSold, 2) : 0;
        $livability = $flock->initial_population > 0 ? round(($livePopulation / $flock->initial_population) * 100, 2) : 0;

        $startDate = \Carbon\Carbon::parse($flock->start_date);
        $harvestDate = $harvest ? \Carbon\Carbon::parse($harvest->harvest_date) : \Carbon\Carbon::now();
        $harvestAge = max(1, intval($startDate->diffInDays($harvestDate)));
        $ip = ($fcr > 0 && $harvestAge > 0) ? round(($livability * $avgWeight) / ($fcr * $harvestAge) * 100, 2) : 0;

        // Financials
        $docPrice = \App\Models\SystemSetting::where('key', 'doc_price')->first()->value ?? 8000;
        $totalDocCost = $flock->initial_population * $docPrice;
        
        $totalFeedCost = $harvest ? $harvest->total_feed_cost : 0;
        $grossRevenue = $harvest ? $harvest->gross_revenue : 0;
        
        // OPEX (Labor)
        $payroll = \App\Models\Payroll::where('flock_id', $flock->id)->first();
        $laborCost = $payroll ? $payroll->total_paid : 0;

        $cogs = $totalDocCost + $totalFeedCost;
        $totalExpenses = $cogs + $laborCost;
        $trueNetProfit = $grossRevenue - $totalExpenses;

        $profitMargin = $grossRevenue > 0 ? round(($trueNetProfit / $grossRevenue) * 100, 2) : 0;
        $bepPricePerKg = $totalHarvestWeight > 0 ? round($totalExpenses / $totalHarvestWeight, 2) : 0;

        return view('reports.harvest-preview', compact(
            'flock', 'coop', 'harvest', 'livePopulation', 'totalMortality', 'totalCulling', 
            'totalFeed', 'fcr', 'avgWeight', 'livability', 'harvestAge', 'ip',
            'docPrice', 'totalDocCost', 'totalFeedCost', 'grossRevenue', 'laborCost',
            'cogs', 'totalExpenses', 'trueNetProfit', 'profitMargin', 'bepPricePerKg'
        ));
    }
}
