<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\FeedRequest;
use App\Models\Flock;
use App\Models\Harvest;
use App\Models\Payroll;
use App\Models\SystemSetting;
use App\Services\FlockMetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the role-separated operational or enterprise dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->can('manage-enterprise')) {
            $totalCashIn = (int) CashFlow::where('type', 'in')->sum('amount');
            $totalCashOut = (int) CashFlow::where('type', 'out')->sum('amount');
            $cashBalance = $totalCashIn - $totalCashOut;

            $marketPrice = (int) (SystemSetting::where('key', 'global_market_price')->value('value') ?? 23000);
            $docPrice = (int) (SystemSetting::where('key', 'doc_price')->value('value') ?? 8000);
            $feedPrice = (int) (SystemSetting::where('key', 'feed_price')->value('value') ?? 9000);

            $activeFlocks = Flock::with(['coop', 'worker', 'dailyRecords', 'harvests'])
                ->where('status', 'active')
                ->orderBy('start_date', 'desc')
                ->get();

            $estimatedActiveRevenue = 0;
            $estimatedActiveCost = 0;
            $totalActivePopulation = 0;

            $activeFlockSummaries = $activeFlocks->map(function (Flock $flock) use (
                $marketPrice,
                $docPrice,
                $feedPrice,
                &$estimatedActiveRevenue,
                &$estimatedActiveCost,
                &$totalActivePopulation
            ) {
                $metrics = FlockMetricsService::calculateForFlock($flock, $flock->dailyRecords, $flock->harvests);
                $latestRecord = $flock->dailyRecords->sortByDesc('record_date')->first();
                $avgWeightGram = $latestRecord->average_weight_gram ?? (40 + ($metrics['harvest_age'] * 45));
                $avgWeightKg = $avgWeightGram / 1000;

                $flockCost = ($flock->initial_population * $docPrice) + ($metrics['total_feed_kg'] * $feedPrice);
                $flockRevenue = $metrics['live_population'] * $avgWeightKg * $marketPrice;

                $estimatedActiveCost += $flockCost;
                $estimatedActiveRevenue += $flockRevenue;
                $totalActivePopulation += $metrics['live_population'];

                return [
                    'flock' => $flock,
                    'live_population' => $metrics['live_population'],
                    'harvest_age' => $metrics['harvest_age'],
                    'total_feed_kg' => $metrics['total_feed_kg'],
                    'livability' => $metrics['livability'],
                    'estimated_revenue' => $flockRevenue,
                ];
            });

            $estimatedActiveNetProfit = $estimatedActiveRevenue - $estimatedActiveCost;
            $realizedNetProfit = (float) Harvest::sum('net_profit');
            $realizedRevenue = (float) Harvest::sum('gross_revenue');
            $closedFlocksCount = Flock::where('status', 'closed')->count();
            $pendingFeedRequestsCount = FeedRequest::where('status', 'pending')->count();

            $financialMetrics = [
                'total_cash_in' => $totalCashIn,
                'total_cash_out' => $totalCashOut,
                'cash_balance' => $cashBalance,
                'estimated_revenue' => $estimatedActiveRevenue,
                'estimated_active_net_profit' => $estimatedActiveNetProfit,
                'net_profit' => $realizedNetProfit,
                'realized_revenue' => $realizedRevenue,
                'total_active_population' => $totalActivePopulation,
                'active_flocks_count' => $activeFlocks->count(),
                'closed_flocks_count' => $closedFlocksCount,
                'pending_feed_requests_count' => $pendingFeedRequestsCount,
            ];

            return view('dashboard', [
                'financialMetrics' => $financialMetrics,
                'activeFlockSummaries' => $activeFlockSummaries,
                'latestPayroll' => null,
                'workerStats' => null,
            ]);
        }

        // Worker role: operational active flocks + personal latest payroll/bonus only
        $today = Carbon::today()->toDateString();

        $activeFlocks = Flock::with(['coop', 'dailyRecords', 'harvests'])
            ->where('worker_id', $user->id)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        $totalActivePopulation = 0;

        $activeFlockSummaries = $activeFlocks->map(function (Flock $flock) use ($today, &$totalActivePopulation) {
            $metrics = FlockMetricsService::calculateForFlock($flock, $flock->dailyRecords, $flock->harvests);
            $latestRecord = $flock->dailyRecords->sortByDesc('record_date')->first();
            $latestRecordDate = $latestRecord ? Carbon::parse($latestRecord->record_date)->toDateString() : null;

            $totalActivePopulation += $metrics['live_population'];

            return [
                'flock' => $flock,
                'live_population' => $metrics['live_population'],
                'harvest_age' => $metrics['harvest_age'],
                'total_feed_kg' => $metrics['total_feed_kg'],
                'livability' => $metrics['livability'],
                'latest_record_date' => $latestRecordDate,
                'recorded_today' => $latestRecordDate === $today,
            ];
        });

        $latestPayroll = Payroll::with('flock.coop')
            ->where('user_id', $user->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $workerStats = [
            'active_flocks_count' => $activeFlocks->count(),
            'total_active_population' => $totalActivePopulation,
            'closed_flocks_count' => Flock::where('worker_id', $user->id)->where('status', 'closed')->count(),
            'total_payslips_count' => Payroll::where('user_id', $user->id)->count(),
        ];

        return view('dashboard', [
            'financialMetrics' => null,
            'activeFlockSummaries' => $activeFlockSummaries,
            'latestPayroll' => $latestPayroll,
            'workerStats' => $workerStats,
        ]);
    }
}
