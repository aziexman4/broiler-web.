<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Flock;
use App\Models\Harvest;
use App\Models\Payroll;
use App\Models\SystemSetting;
use App\Services\FlockMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class HarvestController extends Controller
{
    /**
     * Ensure only enterprise owners can run harvests on active flocks.
     */
    private function authorizeActiveHarvest(Flock $flock): void
    {
        Gate::authorize('manage-enterprise');
        abort_if(
            $flock->status === 'closed',
            403,
            'Siklus kandang ini sudah ditutup (Closed) dan tidak dapat dipanen ulang.'
        );
    }

    /**
     * Record harvest financials, optionally close flock (final harvest vs thinning), and post cash-in transaction atomically.
     */
    private function recordHarvestAndCloseFlock(Flock $flock, array $params, bool $closeFlock = true): Harvest
    {
        return DB::transaction(function () use ($flock, $params, $closeFlock) {
            $totalFeedConsumed = (float) $flock->dailyRecords()
                ->sum(DB::raw('feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg'));
            $cumulativeFeedCost = $totalFeedConsumed * $params['feed_price_per_kg'];
            $priorFeedCost = (float) $flock->harvests()->sum('total_feed_cost');
            $priorChickensSold = (int) $flock->harvests()->sum('total_chickens_sold');

            $docPrice = (float) (SystemSetting::where('key', 'doc_price')->value('value') ?? 8000);
            $fullDocCost = $flock->initial_population * $docPrice;
            $priorDocAllocated = $flock->initial_population > 0
                ? round($fullDocCost * min(1, $priorChickensSold / $flock->initial_population), 2)
                : 0.0;

            if ($closeFlock) {
                $totalFeedCost = max(0.0, $cumulativeFeedCost - $priorFeedCost);
                $allocatedDocCost = max(0.0, $fullDocCost - $priorDocAllocated);
            } else {
                $shareRatio = $flock->initial_population > 0
                    ? min(1.0, $params['total_chickens_sold'] / $flock->initial_population)
                    : 1.0;
                $totalFeedCost = round(max(0.0, $cumulativeFeedCost - $priorFeedCost) * $shareRatio, 2);
                $allocatedDocCost = round($fullDocCost * $shareRatio, 2);
            }

            $grossRevenue = $params['total_weight_kg'] * $params['chicken_price_per_kg'];
            $netProfit = $grossRevenue - $totalFeedCost - $allocatedDocCost;

            $harvest = $flock->harvests()->create([
                'harvest_date' => $params['harvest_date'],
                'total_chickens_sold' => $params['total_chickens_sold'],
                'total_weight_kg' => $params['total_weight_kg'],
                'buyer_name' => $params['buyer_name'],
                'chicken_price_per_kg' => $params['chicken_price_per_kg'],
                'feed_price_per_kg' => $params['feed_price_per_kg'],
                'total_feed_cost' => $totalFeedCost,
                'gross_revenue' => $grossRevenue,
                'net_profit' => $netProfit,
                'revenue' => $grossRevenue,
            ]);

            if ($closeFlock) {
                $flock->update([
                    'status' => 'closed',
                    'closed_at' => $params['harvest_date'],
                ]);
            }

            if ($grossRevenue > 0) {
                $labelPrefix = $closeFlock ? 'Pendapatan Panen' : 'Pendapatan Panen Penjarangan';
                CashFlow::create([
                    'transaction_date' => $params['harvest_date'],
                    'description' => "{$labelPrefix} {$flock->name} ({$params['buyer_name']})",
                    'type' => 'in',
                    'amount' => (int) round($grossRevenue),
                ]);
            }

            return $harvest;
        });
    }

    /**
     * Display global financial and harvest history dashboard.
     */
    public function index()
    {
        $harvests = Harvest::with('flock')
            ->orderBy('harvest_date', 'asc')
            ->get();

        $payrollsByFlock = Payroll::selectRaw('flock_id, SUM(total_paid) as total_labor')
            ->groupBy('flock_id')
            ->pluck('total_labor', 'flock_id');

        foreach ($harvests as $harvest) {
            $laborCost = (float) ($payrollsByFlock[$harvest->flock_id] ?? 0);
            $harvest->labor_cost = $laborCost;
            $harvest->true_net_profit = (float) $harvest->net_profit - $laborCost;
        }

        $total_net_profit = $harvests->sum('true_net_profit');
        $total_operating_profit = $harvests->sum('net_profit');
        $total_labor_cost = $harvests->sum('labor_cost');
        $total_revenue = $harvests->sum('gross_revenue');
        $total_weight = $harvests->sum('total_weight_kg');

        $chartLabels = $harvests->map(function ($harvest) {
            $flockName = $harvest->flock ? $harvest->flock->name : 'Kandang';
            $date = \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y');
            return "{$flockName} ({$date})";
        });

        $chartData = $harvests->pluck('true_net_profit');

        return view('harvests.history', compact(
            'harvests',
            'total_net_profit',
            'total_operating_profit',
            'total_labor_cost',
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
        $this->authorizeActiveHarvest($flock);

        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $alreadyHarvested = (int) $flock->harvests()->sum('total_chickens_sold');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling - $alreadyHarvested);

        $defaultMarketPrice = (int) (SystemSetting::where('key', 'global_market_price')->value('value') ?? 23000);
        $defaultFeedPrice = (int) (SystemSetting::where('key', 'feed_price')->value('value') ?? 9000);

        return view('harvests.create', compact('flock', 'livePopulation', 'alreadyHarvested', 'defaultMarketPrice', 'defaultFeedPrice'));
    }

    /**
     * Store a newly created harvest record and calculate finances (supports partial thinning or final close).
     */
    public function store(Request $request, Flock $flock)
    {
        $this->authorizeActiveHarvest($flock);

        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $alreadyHarvested = (int) $flock->harvests()->sum('total_chickens_sold');
        $availableChickens = max(0, $flock->initial_population - $totalMortality - $totalCulling - $alreadyHarvested);

        $validated = $request->validate([
            'harvest_date' => 'required|date',
            'chickens_sold' => ['nullable', 'integer', 'min:1', 'max:' . max(1, $availableChickens)],
            'total_weight_kg' => 'required|numeric|min:0.01',
            'chicken_price_per_kg' => 'required|numeric|min:0',
            'feed_price_per_kg' => 'required|numeric|min:0',
            'buyer_name' => 'required|string|max:255',
            'close_flock' => 'nullable|boolean',
        ]);

        $shouldCloseFlock = !$request->has('close_flock') || $request->boolean('close_flock');
        $totalChickensSold = isset($validated['chickens_sold']) && $validated['chickens_sold'] !== null
            ? (int) $validated['chickens_sold']
            : $availableChickens;

        $this->recordHarvestAndCloseFlock($flock, [
            'harvest_date' => $validated['harvest_date'],
            'total_chickens_sold' => $totalChickensSold,
            'total_weight_kg' => (float) $validated['total_weight_kg'],
            'buyer_name' => $validated['buyer_name'],
            'chicken_price_per_kg' => (float) $validated['chicken_price_per_kg'],
            'feed_price_per_kg' => (float) $validated['feed_price_per_kg'],
        ], $shouldCloseFlock);

        $msg = $shouldCloseFlock
            ? 'Data panen akhir berhasil disimpan & tercatat di Arus Kas. Kandang ini sekarang ditutup.'
            : 'Panen penjarangan berhasil disimpan & tercatat di Arus Kas. Siklus kandang tetap aktif.';

        return redirect()->route('flocks.show', $flock)->with('success', $msg);
    }

    /**
     * Auto-harvest using existing projected data (1-Click Harvest)
     */
    public function autoStore(Flock $flock)
    {
        $this->authorizeActiveHarvest($flock);

        $totalMortality = $flock->dailyRecords()->sum('mortality');
        $totalCulling = $flock->dailyRecords()->sum('culling');
        $alreadyHarvested = (int) $flock->harvests()->sum('total_chickens_sold');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling - $alreadyHarvested);

        $latestRecord = $flock->dailyRecords()->orderBy('record_date', 'desc')->first();
        $avgWeightGram = $latestRecord->average_weight_gram ?? 1600;
        $totalWeightKg = ($livePopulation * $avgWeightGram) / 1000;

        $marketPrice = (float) (SystemSetting::where('key', 'global_market_price')->value('value') ?? 23000);
        $feedPrice = (float) (SystemSetting::where('key', 'feed_price')->value('value') ?? 9000);

        $this->recordHarvestAndCloseFlock($flock, [
            'harvest_date' => now()->format('Y-m-d'),
            'total_chickens_sold' => $livePopulation,
            'total_weight_kg' => round($totalWeightKg, 2),
            'buyer_name' => 'Auto-Harvest (Proyeksi Otomatis)',
            'chicken_price_per_kg' => $marketPrice,
            'feed_price_per_kg' => $feedPrice,
        ]);

        return redirect()->route('flocks.show', $flock)
            ->with('success', 'Panen berhasil diselesaikan secara otomatis & tercatat di Arus Kas!');
    }

    /**
     * Export Flock harvest report to printable HTML or downloadable PDF via DomPDF.
     */
    public function exportPdf(Request $request, Flock $flock)
    {
        Gate::authorize('manage-enterprise');
        Gate::authorize('view', $flock);

        $flock->load(['coop', 'worker', 'dailyRecords', 'harvests']);
        $coop = $flock->coop;
        $harvest = $flock->harvests->sortByDesc('harvest_date')->first();

        $metrics = FlockMetricsService::calculateForFlock($flock, $flock->dailyRecords, $flock->harvests);
        $totalMortality = $metrics['total_mortality'];
        $totalCulling = $metrics['total_culling'];
        $totalFeed = $metrics['total_feed_kg'];
        $livePopulation = $metrics['live_population'];
        $totalHarvestWeight = $metrics['total_harvest_weight_kg'];
        $fcr = $metrics['fcr'];
        $avgWeight = $metrics['avg_weight_kg'];
        $livability = $metrics['livability'];
        $harvestAge = $metrics['harvest_age'];
        $ip = $metrics['ip'];

        // Financials
        $docPrice = (float) (SystemSetting::where('key', 'doc_price')->value('value') ?? 8000);
        $totalDocCost = $flock->initial_population * $docPrice;
        
        $totalFeedCost = (float) $flock->harvests->sum('total_feed_cost');
        $grossRevenue = (float) $flock->harvests->sum('gross_revenue');
        
        // OPEX (Labor)
        $laborCost = (float) Payroll::where('flock_id', $flock->id)->sum('total_paid');

        $cogs = $totalDocCost + $totalFeedCost;
        $totalExpenses = $cogs + $laborCost;
        $trueNetProfit = $grossRevenue - $totalExpenses;

        $profitMargin = $grossRevenue > 0 ? round(($trueNetProfit / $grossRevenue) * 100, 2) : 0;
        $bepPricePerKg = $totalHarvestWeight > 0 ? round($totalExpenses / $totalHarvestWeight, 2) : 0;

        $viewData = compact(
            'flock', 'coop', 'harvest', 'livePopulation', 'totalMortality', 'totalCulling',
            'totalFeed', 'fcr', 'avgWeight', 'livability', 'harvestAge', 'ip',
            'docPrice', 'totalDocCost', 'totalFeedCost', 'grossRevenue', 'laborCost',
            'cogs', 'totalExpenses', 'trueNetProfit', 'profitMargin', 'bepPricePerKg'
        );

        if ($request->boolean('download')) {
            $filename = 'laporan_panen_' . Str::slug($flock->name) . '_' . date('Y-m-d') . '.pdf';
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.harvest-preview', $viewData + ['isPdf' => true])
                ->setPaper('a4', 'portrait')
                ->download($filename);
        }

        return view('reports.harvest-preview', $viewData);
    }
}
