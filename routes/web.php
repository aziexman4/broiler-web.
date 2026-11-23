<?php

use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\CoopController;
use App\Http\Controllers\DailyRecordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedInventoryController;
use App\Http\Controllers\FlockController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\OvkController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

// Halaman Utama (Landing Page / Splash Screen)
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Berbasis Peran (Owner: Eksekutif & Finansial, Worker: Operasional & Slip Gaji)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Semua rute di dalam sini dilindungi oleh sistem Login
Route::middleware('auth')->group(function () {
    // Pengaturan Akun
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Modul Kandang Utama & Cetak Laporan Operasional
    Route::resource('flocks', FlockController::class);
    Route::get('/flocks/{flock}/report/daily-record', [ReportController::class, 'dailyRecordPreview'])->name('reports.daily-record');

    // Modul Slip Gaji Pekerja (Self-Service & Ownership Guard)
    Route::get('/my-payslips', [PayrollController::class, 'workerIndex'])->name('worker.payslips.index');
    Route::get('/payrolls/{payroll}/payslip', [PayrollController::class, 'payslipPreview'])->name('payrolls.payslip');

    // Modul Catatan Harian
    Route::get('/flocks/{flock}/daily-records/create', [DailyRecordController::class, 'create'])->name('daily_records.create');
    Route::post('/flocks/{flock}/daily-records', [DailyRecordController::class, 'store'])->name('daily_records.store');
    Route::put('/daily-records/{dailyRecord}', [DailyRecordController::class, 'update'])->name('daily_records.update');
    Route::delete('/daily-records/{dailyRecord}', [DailyRecordController::class, 'destroy'])->name('daily_records.destroy');

    // Feed Logistics Routes
    Route::post('/flocks/{flock}/feed-requests', [\App\Http\Controllers\FeedRequestController::class, 'store'])->name('feed_requests.store');
    Route::post('/feed-requests/{feedRequest}/ship', [\App\Http\Controllers\FeedRequestController::class, 'ship'])->name('feed_requests.ship');
    Route::post('/feed-requests/{feedRequest}/receive', [\App\Http\Controllers\FeedRequestController::class, 'receive'])->name('feed_requests.receive');

    // ==========================================
    // ENTERPRISE MODULES (Owner Only)
    // ==========================================
    Route::middleware(['can:manage-enterprise'])->group(function () {
        // System Settings
        Route::post('/settings/update-price', [SystemSettingController::class, 'updatePrice'])->name('settings.updatePrice');

        // Modul Gudang Pakan
        Route::resource('feeds', FeedInventoryController::class)->only(['index', 'store']);
        Route::patch('/feeds/{feed}/inline-update', [FeedInventoryController::class, 'inlineUpdate'])->name('feeds.inline-update');
        Route::get('/feed', [FeedInventoryController::class, 'index'])->name('feed.index');

        // Modul Inventaris OVK (Obat, Vaksin, Kimia)
        Route::resource('ovk', OvkController::class)->only(['index', 'store']);

        // Modul Arus Kas Operasional (Cash Flow)
        Route::resource('cash-flows', CashFlowController::class)->only(['index', 'store']);

        // Modul Master Unit & Lokasi Kandang
        Route::resource('coops', CoopController::class)->only(['index', 'store', 'update', 'destroy']);

        // Modul Manajemen Karyawan / Pekerja (HRD)
        Route::resource('workers', WorkerController::class)->only(['index', 'store', 'update', 'destroy']);

        // Modul Log Aktivitas (Audit Trail)
        Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity_logs.index');

        // Laporan Preview (Cetak dengan Pratinjau)
        Route::get('/reports/cash-flow', [ReportController::class, 'cashFlowPreview'])->name('reports.cash-flow');
        Route::get('/reports/inventory-audit', [ReportController::class, 'inventoryAuditPreview'])->name('reports.inventory-audit');

        // Modul Analitik & Benchmarking
        Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');

        // Modul Riwayat Finansial Global
        Route::get('/financial-history', [HarvestController::class, 'index'])->name('financial.history');

        // Laporan Akhir Panen, CSV & P&L Kandang (Owner Only)
        Route::get('/flocks/{flock}/export-csv', [ReportController::class, 'exportCsv'])->name('flocks.export-csv');
        Route::get('/flocks/{flock}/export-pdf', [HarvestController::class, 'exportPdf'])->name('flocks.export-pdf');

        // Modul Panen Otomatis & Finansial (Owner Only)
        Route::get('/flocks/{flock}/harvests/create', [HarvestController::class, 'create'])->name('harvests.create');
        Route::post('/flocks/{flock}/harvests', [HarvestController::class, 'store'])->name('harvests.store');
        Route::post('/flocks/{flock}/harvests/auto', [HarvestController::class, 'autoStore'])->name('harvests.auto');

        // Modul Penggajian Pekerja (Payroll)
        Route::resource('payrolls', PayrollController::class)->only(['index', 'store']);
    });
});

require __DIR__ . '/auth.php';
