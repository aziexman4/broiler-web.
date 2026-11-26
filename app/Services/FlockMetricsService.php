<?php

namespace App\Services;

use App\Models\Coop;
use App\Models\Flock;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FlockMetricsService
{
    /**
     * Calculate standardized productivity metrics (FCR, Livability, Avg Weight, Harvest Age, IP) for a flock.
     */
    public static function calculateForFlock(
        Flock $flock,
        ?Collection $dailyRecords = null,
        ?Collection $harvests = null
    ): array {
        if ($dailyRecords === null) {
            $flock->loadMissing('dailyRecords');
            $dailyRecords = $flock->dailyRecords;
        }

        if ($harvests === null) {
            $flock->loadMissing('harvests');
            $harvests = $flock->harvests;
        }

        $totalMortality = (int) $dailyRecords->sum('mortality');
        $totalCulling = (int) $dailyRecords->sum('culling');
        $livePopulation = max(0, (int) $flock->initial_population - $totalMortality - $totalCulling);

        $totalFeed = (float) $dailyRecords->sum(
            fn ($record) => (float) $record->feed_pre_starter_kg
                + (float) $record->feed_starter_kg
                + (float) $record->feed_finisher_kg
        );

        $totalHarvestSold = (int) $harvests->sum('total_chickens_sold');
        $totalHarvestWeight = (float) $harvests->sum('total_weight_kg');
        $totalRevenue = (float) $harvests->sum(
            fn ($harvest) => (float) ($harvest->gross_revenue > 0 ? $harvest->gross_revenue : $harvest->revenue)
        );
        $totalNetProfit = round((float) $harvests->sum('net_profit'), 2);

        $fcr = $totalHarvestWeight > 0 ? round($totalFeed / $totalHarvestWeight, 3) : 0.0;
        $livability = $flock->initial_population > 0
            ? round(($livePopulation / $flock->initial_population) * 100, 2)
            : 0.0;
        $avgWeightKg = $totalHarvestSold > 0 ? round($totalHarvestWeight / $totalHarvestSold, 2) : 0.0;

        $startDate = Carbon::parse($flock->start_date);
        $endDate = $harvests->isNotEmpty()
            ? Carbon::parse($harvests->max('harvest_date'))
            : ($flock->closed_at ? Carbon::parse($flock->closed_at) : Carbon::now());

        $harvestAge = max(1, intval($startDate->diffInDays($endDate)));
        $ip = ($fcr > 0 && $harvestAge > 0)
            ? round(($livability * $avgWeightKg) / ($fcr * $harvestAge) * 100, 2)
            : 0.0;

        return [
            'total_mortality' => $totalMortality,
            'total_culling' => $totalCulling,
            'live_population' => $livePopulation,
            'total_feed_kg' => round($totalFeed, 2),
            'total_harvest_sold' => $totalHarvestSold,
            'total_harvest_weight_kg' => round($totalHarvestWeight, 2),
            'total_revenue' => $totalRevenue,
            'net_profit' => $totalNetProfit,
            'fcr' => $fcr,
            'livability' => $livability,
            'avg_weight_kg' => $avgWeightKg,
            'harvest_age' => $harvestAge,
            'ip' => $ip,
        ];
    }

    /**
     * Determine the tiered IP bonus percentage, label, and recommended bonus amount (Phase 2 formula).
     */
    public static function evaluateIpBonus(float $ip, int $baseSalary): array
    {
        if ($ip >= 400) {
            $bonusPercentage = 20;
            $bonusTierLabel = 'Sangat Baik';
        } elseif ($ip >= 350) {
            $bonusPercentage = 15;
            $bonusTierLabel = 'Baik';
        } elseif ($ip >= 300) {
            $bonusPercentage = 10;
            $bonusTierLabel = 'Cukup / Standar';
        } else {
            $bonusPercentage = 0;
            $bonusTierLabel = 'Kurang';
        }

        return [
            'bonus_percentage' => $bonusPercentage,
            'bonus_tier_label' => $bonusTierLabel,
            'recommended_bonus' => (int) round($baseSalary * ($bonusPercentage / 100)),
        ];
    }

    /**
     * Build shared closed-flock benchmarking data, farm averages, and per-coop leaderboard.
     */
    public static function buildBenchmarkReport(?int $coopId = null): array
    {
        $coops = Coop::orderByDesc('is_headquarters')
            ->orderBy('name')
            ->get(['id', 'name', 'manager_name', 'is_headquarters']);

        $flocks = Flock::with(['coop', 'worker', 'harvests', 'dailyRecords'])
            ->where('status', 'closed')
            ->when($coopId !== null, fn ($query) => $query->where('coop_id', $coopId))
            ->orderBy('closed_at', 'asc')
            ->get();

        $analyticsData = [];
        $totalIP = 0.0;
        $totalFCR = 0.0;
        $totalLivability = 0.0;
        $totalAvgWeight = 0.0;
        $totalNetProfit = 0.0;
        $validFlockCount = 0;

        foreach ($flocks as $flock) {
            $harvests = $flock->harvests;
            if ($harvests->isEmpty()) {
                continue;
            }

            $metrics = self::calculateForFlock($flock, $flock->dailyRecords, $harvests);
            $bonusTier = self::evaluateIpBonus($metrics['ip'], 0);
            $closedCarbon = $flock->closed_at ? Carbon::parse($flock->closed_at) : Carbon::parse($harvests->max('harvest_date'));

            $row = [
                'flock_id' => $flock->id,
                'coop_id' => $flock->coop_id,
                'coop_name' => $flock->coop->name ?? 'Tanpa Unit',
                'flock_name' => $flock->name,
                'breed' => $flock->breed,
                'worker_name' => $flock->worker->name ?? 'N/A',
                'start_date' => Carbon::parse($flock->start_date)->format('d M Y'),
                'closed_at' => $closedCarbon->format('d M Y'),
                'closed_timestamp' => $closedCarbon->timestamp,
                'harvest_age' => $metrics['harvest_age'],
                'livability' => $metrics['livability'],
                'avg_weight_kg' => $metrics['avg_weight_kg'],
                'fcr' => $metrics['fcr'],
                'ip' => $metrics['ip'],
                'bonus_tier_label' => $bonusTier['bonus_tier_label'],
                'bonus_percentage' => $bonusTier['bonus_percentage'],
                'gross_revenue' => $metrics['total_revenue'],
                'net_profit' => $metrics['net_profit'],
            ];

            $analyticsData[] = $row;
            $totalNetProfit += $metrics['net_profit'];

            if ($metrics['ip'] > 0) {
                $totalIP += $metrics['ip'];
                $totalFCR += $metrics['fcr'];
                $totalLivability += $metrics['livability'];
                $totalAvgWeight += $metrics['avg_weight_kg'];
                $validFlockCount++;
            }
        }

        $farmAverages = [
            'ip' => $validFlockCount > 0 ? round($totalIP / $validFlockCount, 2) : 0.0,
            'fcr' => $validFlockCount > 0 ? round($totalFCR / $validFlockCount, 3) : 0.0,
            'livability' => $validFlockCount > 0 ? round($totalLivability / $validFlockCount, 2) : 0.0,
            'avg_weight_kg' => $validFlockCount > 0 ? round($totalAvgWeight / $validFlockCount, 2) : 0.0,
            'total_net_profit' => round($totalNetProfit, 2),
            'cycles_count' => count($analyticsData),
        ];

        $recordsByCoop = collect($analyticsData)->groupBy('coop_id');
        $coopBenchmarks = $coops
            ->filter(fn ($coop) => $coopId === null || (int) $coop->id === $coopId)
            ->map(function ($coop) use ($recordsByCoop) {
                $unitRows = $recordsByCoop->get($coop->id, collect());
                $validRows = $unitRows->filter(fn ($item) => $item['ip'] > 0);
                $validCount = $validRows->count();

                return [
                    'coop_id' => $coop->id,
                    'coop_name' => $coop->name,
                    'manager_name' => $coop->manager_name ?? '-',
                    'is_headquarters' => (bool) $coop->is_headquarters,
                    'cycles_count' => $unitRows->count(),
                    'avg_ip' => $validCount > 0 ? round($validRows->avg('ip'), 2) : 0.0,
                    'avg_fcr' => $validCount > 0 ? round($validRows->avg('fcr'), 3) : 0.0,
                    'avg_livability' => $validCount > 0 ? round($validRows->avg('livability'), 2) : 0.0,
                    'avg_weight_kg' => $validCount > 0 ? round($validRows->avg('avg_weight_kg'), 2) : 0.0,
                    'total_net_profit' => round((float) $unitRows->sum('net_profit'), 2),
                ];
            })
            ->sortByDesc('avg_ip')
            ->values()
            ->all();

        $unitHistIpMap = collect($coopBenchmarks)->pluck('avg_ip', 'coop_id');

        $settings = SystemSetting::whereIn('key', ['global_market_price', 'doc_price', 'feed_price'])
            ->pluck('value', 'key');
        $marketPrice = (int) ($settings['global_market_price'] ?? 23000);
        $docPrice = (int) ($settings['doc_price'] ?? 8000);
        $feedPrice = (int) ($settings['feed_price'] ?? 9000);

        $activeFlocks = Flock::with(['coop', 'worker', 'dailyRecords', 'harvests'])
            ->where('status', 'active')
            ->when($coopId !== null, fn ($query) => $query->where('coop_id', $coopId))
            ->orderBy('start_date', 'asc')
            ->get();

        $activeFlockBenchmarks = [];
        foreach ($activeFlocks as $activeFlock) {
            $base = self::calculateForFlock($activeFlock, $activeFlock->dailyRecords, $activeFlock->harvests);
            $sortedDaily = $activeFlock->dailyRecords->sortBy('record_date')->values();
            $latestDaily = $sortedDaily->last();
            $ageDays = $base['harvest_age'];

            $avgWeightGram = ($latestDaily && (int) $latestDaily->average_weight_gram > 0)
                ? (int) $latestDaily->average_weight_gram
                : (40 + ($ageDays * 45));
            $avgWeightKg = round($avgWeightGram / 1000, 3);
            $estimatedBiomassKg = round($base['live_population'] * ($avgWeightGram / 1000), 2);

            $estFcr = $estimatedBiomassKg > 0
                ? round($base['total_feed_kg'] / $estimatedBiomassKg, 3)
                : 0.0;
            $estIp = ($estFcr > 0 && $ageDays > 0)
                ? round(($base['livability'] * ($avgWeightGram / 1000)) / ($estFcr * $ageDays) * 100, 2)
                : 0.0;
            $estBonusTier = self::evaluateIpBonus($estIp, 0);

            $estimatedCost = round(($activeFlock->initial_population * $docPrice) + ($base['total_feed_kg'] * $feedPrice), 2);
            $estimatedRevenue = round($estimatedBiomassKg * $marketPrice, 2);
            $estimatedProfit = round($estimatedRevenue - $estimatedCost, 2);

            $latestDailyMortalityPct = ($latestDaily && $activeFlock->initial_population > 0)
                ? round(((int) $latestDaily->mortality / $activeFlock->initial_population) * 100, 2)
                : 0.0;

            $historicalCoopIp = (float) ($unitHistIpMap[$activeFlock->coop_id] ?? 0.0);
            $ipDelta = $historicalCoopIp > 0 ? round($estIp - $historicalCoopIp, 2) : null;

            $growthPoints = $sortedDaily->map(fn ($rec, $idx) => [
                'day' => $idx + 1,
                'date' => Carbon::parse($rec->record_date)->format('d M'),
                'weight_gram' => (int) ($rec->average_weight_gram ?? 0),
            ])->all();

            $activeFlockBenchmarks[] = [
                'flock_id' => $activeFlock->id,
                'coop_id' => $activeFlock->coop_id,
                'coop_name' => $activeFlock->coop->name ?? 'Tanpa Unit',
                'flock_name' => $activeFlock->name,
                'breed' => $activeFlock->breed,
                'worker_name' => $activeFlock->worker->name ?? 'N/A',
                'start_date' => Carbon::parse($activeFlock->start_date)->format('d M Y'),
                'age_days' => $ageDays,
                'progress_percent' => min(100, (int) round(($ageDays / 35) * 100)),
                'initial_population' => (int) $activeFlock->initial_population,
                'live_population' => $base['live_population'],
                'livability' => $base['livability'],
                'total_feed_kg' => $base['total_feed_kg'],
                'avg_weight_gram' => $avgWeightGram,
                'avg_weight_kg' => $avgWeightKg,
                'estimated_biomass_kg' => $estimatedBiomassKg,
                'est_fcr' => $estFcr,
                'est_ip' => $estIp,
                'est_bonus_tier_label' => $estBonusTier['bonus_tier_label'],
                'historical_coop_ip' => $historicalCoopIp,
                'ip_delta_vs_coop' => $ipDelta,
                'estimated_cost' => $estimatedCost,
                'estimated_revenue' => $estimatedRevenue,
                'estimated_profit' => $estimatedProfit,
                'is_bep_reached' => $estimatedProfit >= 0,
                'has_mortality_warning' => $latestDailyMortalityPct > 0.5,
                'latest_mortality_pct' => $latestDailyMortalityPct,
                'growth_points' => $growthPoints,
            ];
        }

        $activeCol = collect($activeFlockBenchmarks);
        $validActive = $activeCol->filter(fn ($item) => $item['est_ip'] > 0);
        $activeSummary = [
            'count' => $activeCol->count(),
            'total_live_population' => (int) $activeCol->sum('live_population'),
            'avg_est_ip' => $validActive->isNotEmpty() ? round($validActive->avg('est_ip'), 2) : 0.0,
            'avg_est_fcr' => $validActive->isNotEmpty() ? round($validActive->avg('est_fcr'), 3) : 0.0,
            'total_estimated_profit' => round((float) $activeCol->sum('estimated_profit'), 2),
        ];

        return compact(
            'analyticsData',
            'farmAverages',
            'coopBenchmarks',
            'activeFlockBenchmarks',
            'activeSummary',
            'coops'
        );
    }
}
