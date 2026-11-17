<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Coop;
use App\Models\FeedInventory;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Export Flock summary and records to CSV (Streamed).
     */
    public function exportCsv(Flock $flock)
    {
        Gate::authorize('view', $flock);

        $filename = "laporan_kandang_" . Str::slug($flock->name) . "_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $dailyRecords = $flock->dailyRecords()->orderBy('record_date', 'asc')->get();
        $harvests = $flock->harvests()->orderBy('harvest_date', 'asc')->get();

        $totalMortality = $dailyRecords->sum('mortality');
        $totalCulling = $dailyRecords->sum('culling');
        $totalFeed = $dailyRecords->sum('feed_consumed_kg');
        $livePopulation = max(0, $flock->initial_population - $totalMortality - $totalCulling);

        $totalHarvestSold = $harvests->sum('total_chickens_sold');
        $totalHarvestWeight = $harvests->sum('total_weight_kg');
        $totalRevenue = $harvests->sum('revenue');

        $fcr = $totalHarvestWeight > 0 ? round($totalFeed / $totalHarvestWeight, 3) : 0;
        $avgWeightKg = $totalHarvestSold > 0 ? round($totalHarvestWeight / $totalHarvestSold, 2) : 0;

        $callback = function () use ($flock, $dailyRecords, $harvests, $totalMortality, $totalCulling, $totalFeed, $livePopulation, $totalHarvestSold, $totalHarvestWeight, $totalRevenue, $fcr, $avgWeightKg) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Summary Header
            fputcsv($file, ['RINGKASAN KINERJA KANDANG BROILER']);
            fputcsv($file, ['Nama Kandang', $flock->name]);
            fputcsv($file, ['Ras / Breed', $flock->breed]);
            fputcsv($file, ['Tanggal Chick In', \Carbon\Carbon::parse($flock->start_date)->format('Y-m-d')]);
            fputcsv($file, ['Populasi Awal (Ekor)', $flock->initial_population]);
            fputcsv($file, ['Populasi Hidup Saat Ini (Ekor)', $livePopulation]);
            fputcsv($file, ['Total Kematian (Ekor)', $totalMortality]);
            fputcsv($file, ['Total Afkir (Ekor)', $totalCulling]);
            fputcsv($file, ['Total Konsumsi Pakan (Kg)', $totalFeed]);
            fputcsv($file, ['Total Ayam Dipanen (Ekor)', $totalHarvestSold]);
            fputcsv($file, ['Total Berat Panen (Kg)', $totalHarvestWeight]);
            fputcsv($file, ['FCR (Feed Conversion Ratio)', $fcr]);
            fputcsv($file, ['Rata-rata Bobot Panen (Kg/Ekor)', $avgWeightKg]);
            fputcsv($file, ['Total Pendapatan (Rp)', $totalRevenue]);
            fputcsv($file, []);

            // Daily Records Section
            fputcsv($file, ['CATATAN HARIAN']);
            fputcsv($file, ['Tanggal', 'Kematian (Ekor)', 'Afkir (Ekor)', 'Pakan Consumed (Kg)']);
            foreach ($dailyRecords as $record) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($record->record_date)->format('Y-m-d'),
                    $record->mortality,
                    $record->culling,
                    $record->feed_consumed_kg
                ]);
            }
            fputcsv($file, []);

            // Harvest Records Section
            fputcsv($file, ['DATA PANEN']);
            fputcsv($file, ['Tanggal Panen', 'Jumlah Terjual (Ekor)', 'Total Berat (Kg)', 'Nama Pembeli', 'Pendapatan (Rp)']);
            foreach ($harvests as $harvest) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($harvest->harvest_date)->format('Y-m-d'),
                    $harvest->total_chickens_sold,
                    $harvest->total_weight_kg,
                    $harvest->buyer_name,
                    $harvest->revenue
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview Daily Record Sheet as printable HTML.
     */
    public function dailyRecordPreview(Flock $flock)
    {
        Gate::authorize('view', $flock);

        $flock->load('coop');
        $coop = $flock->coop;
        $dailyRecords = $flock->dailyRecords()->orderBy('record_date', 'asc')->get();

        $totalMortality = $dailyRecords->sum('mortality') + $dailyRecords->sum('culling');
        $totalFeedLegacy = $dailyRecords->sum('feed_consumed_kg');
        $totalFeedPhased = $dailyRecords->sum('feed_pre_starter_kg') + $dailyRecords->sum('feed_starter_kg') + $dailyRecords->sum('feed_finisher_kg');
        $totalFeed = $totalFeedLegacy > 0 ? $totalFeedLegacy : $totalFeedPhased;
        $livePopulation = max(0, $flock->initial_population - $totalMortality);

        return view('reports.daily-record-preview', compact(
            'flock', 'coop', 'dailyRecords', 'totalMortality', 'totalFeed', 'livePopulation'
        ));
    }

    /**
     * Preview Cash Flow Statement as printable HTML.
     */
    public function cashFlowPreview(Request $request)
    {
        $coop = Coop::where('is_headquarters', true)->first();
        $summary = CashFlow::getFilteredSummary($request);

        return view('reports.cashflow-preview', [
            'coop' => $coop,
            'records' => $summary['records'],
            'total_in' => $summary['total_in'],
            'total_out' => $summary['total_out'],
            'balance' => $summary['balance'],
            'activeFilterType' => $summary['activeFilterType'],
            'periodLabel' => $summary['periodLabel'],
        ]);
    }

    /**
     * Preview Inventory Audit Report as printable HTML.
     */
    public function inventoryAuditPreview()
    {
        $coop = Coop::where('is_headquarters', true)->first();
        $records = FeedInventory::with('flock')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalIn = FeedInventory::where('transaction_type', 'in')->sum('quantity_kg');
        $totalOut = FeedInventory::where('transaction_type', 'out')->sum('quantity_kg');
        $totalValue = FeedInventory::where('transaction_type', 'in')
            ->selectRaw('SUM(quantity_kg * price_per_kg) as total')
            ->value('total') ?? 0;

        return view('reports.inventory-audit-preview', compact('coop', 'records', 'totalIn', 'totalOut', 'totalValue'));
    }
}
