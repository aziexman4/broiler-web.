<?php

namespace App\Http\Controllers;

use App\Models\Flock;
use App\Services\FlockMetricsService;
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
            $harvests = $flock->harvests;

            if ($harvests->isEmpty()) continue;

            $metrics = FlockMetricsService::calculateForFlock($flock, $flock->dailyRecords, $harvests);
            $fcr = $metrics['fcr'];
            $livability = $metrics['livability'];
            $avgWeightKg = $metrics['avg_weight_kg'];
            $harvestAge = $metrics['harvest_age'];
            $ip = $metrics['ip'];

            $analyticsData[] = [
                'flock_name' => $flock->name,
                'breed' => $flock->breed,
                'worker_name' => $flock->worker->name ?? 'N/A',
                'start_date' => Carbon::parse($flock->start_date)->format('d M Y'),
                'closed_at' => Carbon::parse($flock->closed_at)->format('d M Y'),
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
