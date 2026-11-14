<?php

namespace App\Http\Controllers;

use App\Models\Coop;
use App\Models\Flock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FlockController extends Controller
{
    /**
     * Display a listing of the flocks filtered by user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isOwner()) {
            $flocks = Flock::with(['worker', 'coop'])->latest()->get();
        } else {
            $flocks = Flock::with(['worker', 'coop'])
                ->where('worker_id', $user->id)
                ->latest()
                ->get();
        }

        $workers = User::where('role', 'worker')->get();
        $globalMarketPrice = \App\Models\SystemSetting::where('key', 'global_market_price')->first()->value ?? 23000;

        return view('flocks.index', compact('flocks', 'workers', 'globalMarketPrice'));
    }

    /**
     * Show the form for creating a new flock.
     */
    public function create()
    {
        Gate::authorize('create', Flock::class);

        $workers = User::where('role', 'worker')->get();
        $coops = Coop::all();

        return view('flocks.create', compact('workers', 'coops'));
    }

    /**
     * Store a newly created flock in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Flock::class);

        $validated = $request->validate([
            'coop_id' => 'nullable|exists:coops,id',
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'start_date' => 'required|date',
            'initial_population' => 'required|integer|min:1',
            'worker_id' => 'nullable|exists:users,id',
        ]);

        $validated['status'] = 'active';

        $flock = Flock::create($validated);

        return redirect()->route('flocks.show', $flock)->with('success', 'Kandang berhasil ditambahkan!');
    }

    /**
     * Display the specified flock with metrics, charts JSON, and harvest records.
     */
    public function show(Flock $flock)
    {
        Gate::authorize('view', $flock);

        $dailyRecords = $flock->dailyRecords()->orderBy('record_date', 'asc')->get();
        $harvests = $flock->harvests()->orderBy('harvest_date', 'desc')->get();

        $totalMortality = $dailyRecords->sum('mortality');
        $totalCulling = $dailyRecords->sum('culling');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);
        $totalFeedConsumed = $dailyRecords->sum('feed_consumed_kg');

        $totalHarvestSold = $harvests->sum('total_chickens_sold');
        $totalHarvestWeight = $harvests->sum('total_weight_kg');
        $totalRevenue = $harvests->sum('revenue');

        $fcr = $totalHarvestWeight > 0 ? round($totalFeedConsumed / $totalHarvestWeight, 3) : 0;
        $livability = $flock->initial_population > 0 ? round(($livePopulation / $flock->initial_population) * 100, 2) : 0;
        $avgWeightKg = $totalHarvestSold > 0 ? round($totalHarvestWeight / $totalHarvestSold, 2) : 0;

        $startDate = \Carbon\Carbon::parse($flock->start_date);
        $endDate = $harvests->count() > 0 
            ? \Carbon\Carbon::parse($harvests->max('harvest_date')) 
            : \Carbon\Carbon::now();
        
        $harvestAge = max(1, intval($startDate->diffInDays($endDate)));
        $ip = ($fcr > 0 && $harvestAge > 0) ? round(($livability * $avgWeightKg) / ($fcr * $harvestAge) * 100, 2) : 0;

        $chartDates = $dailyRecords->pluck('record_date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M'))->toArray();
        $chartMortality = $dailyRecords->pluck('mortality')->toArray();
        $chartFeed = $dailyRecords->pluck('feed_consumed_kg')->toArray();

        // Feature: BEP Growth Chart & Individual P&L
        $marketPrice = \App\Models\SystemSetting::where('key', 'global_market_price')->first()->value ?? 23000;
        $docPrice = \App\Models\SystemSetting::where('key', 'doc_price')->first()->value ?? 8000;
        $feedPrice = \App\Models\SystemSetting::where('key', 'feed_price')->first()->value ?? 9000;

        $bepLabels = [];
        $actualWeights = [];
        $bepTargets = [];
        $cumulativeFeed = 0;
        $dayCounter = 1;
        $currentPopulation = $flock->initial_population;

        foreach ($dailyRecords as $record) {
            $bepLabels[] = "Hari " . $dayCounter;
            $actualWeights[] = $record->average_weight_gram ?? (40 + ($dayCounter * 45)); 
            
            $cumulativeFeed += $record->feed_consumed_kg;
            $currentPopulation -= ($record->mortality + $record->culling);
            if ($currentPopulation <= 0) $currentPopulation = 1;
            
            $bepTotalCost = ($flock->initial_population * $docPrice) + ($cumulativeFeed * $feedPrice);
            $bepTargetKg = $bepTotalCost / $marketPrice;
            $bepTargetGramPerBird = ($bepTargetKg / $currentPopulation) * 1000;
            
            $bepTargets[] = round($bepTargetGramPerBird, 2);
            $dayCounter++;
        }

        $bepChartData = json_encode([
            'labels' => $bepLabels,
            'actualWeights' => $actualWeights,
            'bepTargets' => $bepTargets
        ]);

        // Individual P&L Calculation (Forecasting based on Current Alive Birds)
        $totalCost = ($flock->initial_population * $docPrice) + ($totalFeedConsumed * $feedPrice);
        
        $latestRecordObj = $flock->dailyRecords()->orderBy('record_date', 'desc')->first();
        $latestAvgWeightGram = $latestRecordObj->average_weight_gram ?? (40 + ($harvestAge * 45));
        $latestAvgWeightKg = $latestAvgWeightGram / 1000;
        
        $estimatedRevenue = $livePopulation * $latestAvgWeightKg * $marketPrice;
        $netProfit = $estimatedRevenue - $totalCost;

        $latestHarvest = $harvests->first();
        $dailyRecords = $dailyRecords->reverse();
        
        $feedRequests = $flock->feedRequests()->latest()->get();

        return view('flocks.show', compact(
            'flock', 'dailyRecords', 'harvests', 'totalMortality', 'totalCulling', 
            'livePopulation', 'totalFeedConsumed', 'totalHarvestSold', 'totalHarvestWeight', 
            'totalRevenue', 'fcr', 'ip', 'livability', 'avgWeightKg', 'harvestAge',
            'chartDates', 'chartMortality', 'chartFeed', 'latestHarvest', 'bepChartData',
            'totalCost', 'estimatedRevenue', 'netProfit', 'feedRequests'
        ));
    }

    /**
     * Show the form for editing the specified flock.
     */
    public function edit(Flock $flock)
    {
        Gate::authorize('update', $flock);

        $workers = User::where('role', 'worker')->get();
        $coops = Coop::all();

        return view('flocks.edit', compact('flock', 'workers', 'coops'));
    }

    /**
     * Update the specified flock in storage.
     */
    public function update(Request $request, Flock $flock)
    {
        Gate::authorize('update', $flock);

        $validated = $request->validate([
            'coop_id' => 'nullable|exists:coops,id',
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'start_date' => 'required|date',
            'initial_population' => 'required|integer|min:1',
            'worker_id' => 'nullable|exists:users,id',
        ]);

        $flock->update($validated);

        return redirect()->route('flocks.show', $flock)->with('success', 'Data kandang berhasil diperbarui!');
    }

    /**
     * Remove the specified flock from storage.
     */
    public function destroy(Flock $flock)
    {
        Gate::authorize('delete', $flock);

        $flock->delete();

        return redirect()->route('flocks.index')->with('success', 'Kandang berhasil dihapus!');
    }
}
