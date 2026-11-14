<?php

namespace App\Http\Controllers;

use App\Models\Flock;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Get all closed flocks for benchmarking
        $flocks = Flock::with(['worker', 'harvests', 'dailyRecords'])
            ->where('status', 'closed')
            ->orderBy('closed_at', 'asc')
            ->get();

        $analyticsData = [];
        $totalIP = 0;
        $totalFCR = 0;
        $totalLivability = 0;
        $validFlockCount = 0;

        foreach ($flocks as $flock) {
            $dailyRecords = $flock->dailyRecords;
            $harvests = $flock->harvests;

            if ($harvests->isEmpty()) continue;

            $totalMortality = $dailyRecords->sum('mortality');
            $totalCulling = $dailyRecords->sum('culling');
            $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);
            
            // Calculate total feed taking into account both legacy and new phase columns
            $totalFeedLegacy = $dailyRecords->sum('feed_consumed_kg');
            $totalFeedPhased = $dailyRecords->sum('feed_pre_starter_kg') + $dailyRecords->sum('feed_starter_kg') + $dailyRecords->sum('feed_finisher_kg');
            $totalFeedConsumed = $totalFeedLegacy > 0 ? $totalFeedLegacy : $totalFeedPhased;

            $totalHarvestSold = $harvests->sum('total_chickens_sold');
            $totalHarvestWeight = $harvests->sum('total_weight_kg');

            $fcr = $totalHarvestWeight > 0 ? round($totalFeedConsumed / $totalHarvestWeight, 3) : 0;
            $livability = $flock->initial_population > 0 ? round(($livePopulation / $flock->initial_population) * 100, 2) : 0;
            $avgWeightKg = $totalHarvestSold > 0 ? round($totalHarvestWeight / $totalHarvestSold, 2) : 0;

            $startDate = Carbon::parse($flock->start_date);
            $endDate = Carbon::parse($flock->closed_at);
            $harvestAge = max(1, intval($startDate->diffInDays($endDate)));

            $ip = ($fcr > 0 && $harvestAge > 0) ? round(($livability * $avgWeightKg) / ($fcr * $harvestAge) * 100, 2) : 0;

            $analyticsData[] = [
                'flock_name' => $flock->name,
                'breed' => $flock->breed,
                'worker_name' => $flock->worker->name ?? 'N/A',
                'start_date' => $startDate->format('d M Y'),
                'closed_at' => $endDate->format('d M Y'),
                'harvest_age' => $harvestAge,
                'livability' => $livability,
                'avg_weight_kg' => $avgWeightKg,
                'fcr' => $fcr,
                'ip' => $ip
            ];

            if ($ip > 0) {
                $totalIP += $ip;
                $totalFCR += $fcr;
                $totalLivability += $livability;
                $validFlockCount++;
            }
        }

        $farmAverages = [
            'ip' => $validFlockCount > 0 ? round($totalIP / $validFlockCount, 2) : 0,
            'fcr' => $validFlockCount > 0 ? round($totalFCR / $validFlockCount, 3) : 0,
            'livability' => $validFlockCount > 0 ? round($totalLivability / $validFlockCount, 2) : 0,
        ];

        return view('analytics.index', compact('analyticsData', 'farmAverages'));
    }
}
