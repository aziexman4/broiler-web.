<?php

namespace App\Services;

use App\Models\Flock;
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
        $totalRevenue = (float) $harvests->sum('revenue');

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
}
