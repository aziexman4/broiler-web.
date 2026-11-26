<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Coop;
use App\Models\FeedInventory;
use App\Models\Flock;
use App\Models\User;
use App\Services\FlockMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroilerSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_cannot_access_enterprise_routes(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($worker)->get(route('coops.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('workers.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('cash-flows.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('payrolls.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('reports.cash-flow'))->assertForbidden();
    }

    public function test_worker_cannot_view_or_export_unassigned_flock_reports(): void
    {
        $workerA = User::factory()->create(['role' => 'worker']);
        $workerB = User::factory()->create(['role' => 'worker']);

        $flockA = Flock::create([
            'name' => 'Kandang Uji A',
            'breed' => 'Cobb 500',
            'start_date' => '2026-10-01',
            'initial_population' => 4000,
            'status' => 'active',
            'worker_id' => $workerA->id,
        ]);

        // Assigned worker can view daily record report, while financial CSV export is Owner-only
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($workerA)->get(route('reports.daily-record', $flockA))->assertOk();
        $this->actingAs($workerA)->get(route('flocks.export-csv', $flockA))->assertForbidden();
        $this->actingAs($owner)->get(route('flocks.export-csv', $flockA))->assertOk();

        // Unassigned worker is forbidden by FlockPolicy
        $this->actingAs($workerB)->get(route('reports.daily-record', $flockA))->assertForbidden();
        $this->actingAs($workerB)->get(route('flocks.export-csv', $flockA))->assertForbidden();
    }

    public function test_cash_flow_mutually_exclusive_month_and_range_filters(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Coop::create([
            'name' => 'Kandang Hanau',
            'address' => 'Kec. Hanau, Kab. Seruyan',
            'manager_name' => 'Harries',
            'is_headquarters' => true,
        ]);

        CashFlow::create([
            'transaction_date' => '2026-04-10',
            'description' => 'Pembelian Sekam April',
            'type' => 'out',
            'amount' => 2000000,
        ]);

        CashFlow::create([
            'transaction_date' => '2026-04-20',
            'description' => 'Penjualan Pupuk April',
            'type' => 'in',
            'amount' => 3500000,
        ]);

        CashFlow::create([
            'transaction_date' => '2026-08-15',
            'description' => 'Penjualan Karung Agustus',
            'type' => 'in',
            'amount' => 1500000,
        ]);

        // Mode: Month (April 2026) ignores stray start_date
        $responseMonth = $this->actingAs($owner)->get(route('cash-flows.index', [
            'filter_mode' => 'month',
            'month' => '2026-04',
            'start_date' => '2026-01-01',
        ]));

        $responseMonth->assertOk();
        $responseMonth->assertViewHas('total_in', 3500000);
        $responseMonth->assertViewHas('total_out', 2000000);
        $responseMonth->assertViewHas('balance', 1500000);

        // Mode: Range (August 2026) ignores stray month
        $responseRange = $this->actingAs($owner)->get(route('reports.cash-flow', [
            'filter_mode' => 'range',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'month' => '2026-04',
        ]));

        $responseRange->assertOk();
        $responseRange->assertViewHas('total_in', 1500000);
        $responseRange->assertViewHas('total_out', 0);
        $responseRange->assertSee('Periode: 01 Agt 2026 — 31 Agt 2026');
    }

    public function test_feed_inventory_period_filter_and_audit_report_button_placement(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Coop::create([
            'name' => 'Kandang Hanau',
            'address' => 'Kec. Hanau, Kab. Seruyan',
            'manager_name' => 'Harries',
            'is_headquarters' => true,
        ]);

        $flock = Flock::create([
            'name' => 'Kandang Hanau - Siklus Uji',
            'breed' => 'Cobb 500',
            'start_date' => '2026-04-01',
            'initial_population' => 5000,
            'status' => 'active',
        ]);

        FeedInventory::create([
            'transaction_date' => '2026-04-05',
            'feed_name' => 'BR-1 Starter',
            'transaction_type' => 'in',
            'quantity_kg' => 5000,
            'price_per_kg' => 8800,
        ]);

        FeedInventory::create([
            'transaction_date' => '2026-04-12',
            'feed_name' => 'Starter Distribusi',
            'transaction_type' => 'out',
            'quantity_kg' => 1200,
            'flock_id' => $flock->id,
        ]);

        FeedInventory::create([
            'transaction_date' => '2026-09-10',
            'feed_name' => 'BR-2 Finisher',
            'transaction_type' => 'in',
            'quantity_kg' => 3000,
            'price_per_kg' => 9000,
        ]);

        // Month filter on Feed Index
        $feedResponse = $this->actingAs($owner)->get(route('feeds.index', [
            'filter_mode' => 'month',
            'month' => '2026-04',
        ]));

        $feedResponse->assertOk();
        $feedResponse->assertViewHas('totalIn', 5000.0);
        $feedResponse->assertViewHas('totalOut', 1200.0);
        $feedResponse->assertSee('Cetak Audit Gudang A4');

        // Inventory Audit A4 Preview respects filter
        $auditResponse = $this->actingAs($owner)->get(route('reports.inventory-audit', [
            'filter_mode' => 'month',
            'month' => '2026-04',
        ]));

        $auditResponse->assertOk();
        $auditResponse->assertSee('Periode: April 2026');
        $auditResponse->assertViewHas('totalValue', 5000 * 8800.0);

        // Flock detail dropdown no longer contains global audit report link
        $flockDetail = $this->actingAs($owner)->get(route('flocks.show', $flock));
        $flockDetail->assertOk();
        $flockDetail->assertDontSee(route('reports.inventory-audit'));
    }

    public function test_flocks_index_exposes_coop_and_status_filters(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $coopA = Coop::create([
            'name' => 'Kandang Hanau',
            'address' => 'Kec. Hanau',
            'manager_name' => 'Harries',
            'is_headquarters' => true,
        ]);
        $coopB = Coop::create([
            'name' => 'Kandang Plasma C',
            'address' => 'Kec. Danau Sembuluh',
            'manager_name' => 'Mujawiri',
            'is_headquarters' => false,
        ]);

        Flock::create([
            'coop_id' => $coopA->id,
            'name' => 'Kandang Hanau - Siklus Uji',
            'breed' => 'Cobb 500',
            'start_date' => '2026-10-01',
            'initial_population' => 5000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->get(route('flocks.index'));
        $response->assertOk();
        $response->assertViewHas('coops', function ($coops) use ($coopA, $coopB) {
            return $coops->contains('id', $coopA->id) && $coops->contains('id', $coopB->id);
        });
        $response->assertSee('Semua Unit Kandang');
        $response->assertSee('Semua Status');
    }

    public function test_flock_metrics_service_is_consistent_across_controllers_and_bonus_tiers(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker', 'base_salary' => 3000000]);

        $flock = Flock::create([
            'name' => 'Kandang Konsistensi',
            'breed' => 'Cobb 500',
            'start_date' => '2026-09-01',
            'closed_at' => '2026-10-06',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $worker->id,
        ]);

        $flock->dailyRecords()->create([
            'record_date' => '2026-09-15',
            'mortality' => 20,
            'culling' => 0,
            'feed_pre_starter_kg' => 1000,
            'feed_starter_kg' => 1000,
            'feed_finisher_kg' => 1000,
        ]);

        $flock->harvests()->create([
            'harvest_date' => '2026-10-06',
            'total_chickens_sold' => 980,
            'total_weight_kg' => 1960,
            'buyer_name' => 'PT Uji Konsistensi',
            'chicken_price_per_kg' => 23000,
            'feed_price_per_kg' => 9000,
            'total_feed_cost' => 27000000,
            'gross_revenue' => 45080000,
            'net_profit' => 18080000,
            'revenue' => 45080000,
        ]);

        $metrics = FlockMetricsService::calculateForFlock($flock);
        $this->assertSame(1.531, $metrics['fcr']);
        $this->assertSame(98.0, $metrics['livability']);
        $this->assertSame(2.0, $metrics['avg_weight_kg']);
        $this->assertSame(35, $metrics['harvest_age']);
        $this->assertSame(365.77, $metrics['ip']);

        $showResponse = $this->actingAs($owner)->get(route('flocks.show', $flock));
        $showResponse->assertOk();
        $showResponse->assertViewHas('fcr', $metrics['fcr']);
        $showResponse->assertViewHas('ip', $metrics['ip']);

        $pdfResponse = $this->actingAs($owner)->get(route('flocks.export-pdf', $flock));
        $pdfResponse->assertOk();
        $pdfResponse->assertViewHas('fcr', $metrics['fcr']);
        $pdfResponse->assertViewHas('ip', $metrics['ip']);

        $this->assertSame(20, FlockMetricsService::evaluateIpBonus(410, 3000000)['bonus_percentage']);
        $this->assertSame(15, FlockMetricsService::evaluateIpBonus(365.77, 3000000)['bonus_percentage']);
        $this->assertSame(10, FlockMetricsService::evaluateIpBonus(320, 3000000)['bonus_percentage']);
        $this->assertSame(0, FlockMetricsService::evaluateIpBonus(280, 3000000)['bonus_percentage']);
    }

    public function test_a4_reports_show_owner_and_worker_signatures_by_scope(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Pemilik Hanau']);
        $worker = User::factory()->create(['role' => 'worker', 'name' => 'Mujawiri Apandie']);

        $hqCoop = Coop::create([
            'name' => 'Kandang Hanau',
            'address' => 'Kec. Hanau',
            'manager_name' => 'Muhammad Harries Rasyadi',
            'is_headquarters' => true,
        ]);

        $branchCoop = Coop::create([
            'name' => 'Kandang Plasma C',
            'address' => 'Kec. Danau Sembuluh',
            'manager_name' => 'Mujawiri Apandie',
            'is_headquarters' => false,
        ]);

        $flock = Flock::create([
            'coop_id' => $branchCoop->id,
            'name' => 'Kandang Plasma C - Siklus 1',
            'breed' => 'Hubbard',
            'start_date' => '2026-09-01',
            'initial_population' => 3500,
            'status' => 'closed',
            'worker_id' => $worker->id,
        ]);

        $payroll = \App\Models\Payroll::create([
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => 2700000,
            'bonus_amount' => 270000,
            'total_paid' => 2970000,
            'payment_date' => '2026-10-08',
        ]);

        // Global reports -> Owner only
        $cashFlowHtml = $this->actingAs($owner)->get(route('reports.cash-flow'));
        $cashFlowHtml->assertOk();
        $cashFlowHtml->assertSee('( Pemilik Hanau )');
        $cashFlowHtml->assertDontSee('( Mujawiri Apandie )');

        $auditHtml = $this->actingAs($owner)->get(route('reports.inventory-audit'));
        $auditHtml->assertOk();
        $auditHtml->assertSee('( Pemilik Hanau )');

        // Flock & Payroll reports -> Both Worker (left) and Owner (right)
        $dailyHtml = $this->actingAs($owner)->get(route('reports.daily-record', $flock));
        $dailyHtml->assertOk();
        $dailyHtml->assertSee('( Mujawiri Apandie )');
        $dailyHtml->assertSee('( Pemilik Hanau )');

        $harvestHtml = $this->actingAs($owner)->get(route('flocks.export-pdf', $flock));
        $harvestHtml->assertOk();
        $harvestHtml->assertSee('( Mujawiri Apandie )');
        $harvestHtml->assertSee('( Pemilik Hanau )');

        $payslipHtml = $this->actingAs($owner)->get(route('payrolls.payslip', $payroll));
        $payslipHtml->assertOk();
        $payslipHtml->assertSee('( Mujawiri Apandie )');
        $payslipHtml->assertSee('( Pemilik Hanau )');
    }

    public function test_worker_can_view_own_payslips_and_is_blocked_from_others(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Pemilik Utama']);
        $workerA = User::factory()->create(['role' => 'worker', 'name' => 'Worker Satu', 'base_salary' => 2800000]);
        $workerB = User::factory()->create(['role' => 'worker', 'name' => 'Worker Dua', 'base_salary' => 3000000]);

        $flockA = Flock::create([
            'name' => 'Kandang Worker Satu',
            'breed' => 'Cobb',
            'start_date' => '2026-08-01',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $workerA->id,
        ]);

        $flockB = Flock::create([
            'name' => 'Kandang Worker Dua',
            'breed' => 'Ross',
            'start_date' => '2026-08-01',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $workerB->id,
        ]);

        $payrollA = \App\Models\Payroll::create([
            'user_id' => $workerA->id,
            'flock_id' => $flockA->id,
            'base_salary' => 2800000,
            'bonus_amount' => 420000,
            'total_paid' => 3220000,
            'payment_date' => '2026-09-10',
        ]);

        $payrollB = \App\Models\Payroll::create([
            'user_id' => $workerB->id,
            'flock_id' => $flockB->id,
            'base_salary' => 3000000,
            'bonus_amount' => 600000,
            'total_paid' => 3600000,
            'payment_date' => '2026-09-10',
        ]);

        // Worker A sees their own payslip page & menu link, never Worker B's payroll
        $myPayslips = $this->actingAs($workerA)->get(route('worker.payslips.index'));
        $myPayslips->assertOk();
        $myPayslips->assertSee('Slip Gaji Saya');
        $myPayslips->assertSee('Kandang Worker Satu');
        $myPayslips->assertSee('3.220.000');
        $myPayslips->assertDontSee('Kandang Worker Dua');
        $myPayslips->assertDontSee('3.600.000');

        // Worker A can print their own payslip but gets 403 for Worker B's payslip & company payroll index
        $this->actingAs($workerA)->get(route('payrolls.payslip', $payrollA))->assertOk();
        $this->actingAs($workerA)->get(route('payrolls.payslip', $payrollB))->assertForbidden();
        $this->actingAs($workerA)->get(route('payrolls.index'))->assertForbidden();

        // Owner can print any worker's payslip
        $this->actingAs($owner)->get(route('payrolls.payslip', $payrollA))->assertOk();
        $this->actingAs($owner)->get(route('payrolls.payslip', $payrollB))->assertOk();
    }

    public function test_worker_has_no_financial_leak_and_cannot_access_harvest_pnl_pdf(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker']);

        $flock = Flock::create([
            'name' => 'Kandang Operasional Worker',
            'breed' => 'Ross 308',
            'start_date' => '2026-10-01',
            'initial_population' => 1500,
            'status' => 'active',
            'worker_id' => $worker->id,
        ]);

        // flocks/index for worker must not show Rupiah amounts or market price widget
        $indexResponse = $this->actingAs($worker)->get(route('flocks.index'));
        $indexResponse->assertOk();
        $indexResponse->assertViewHas('globalMarketPrice', null);
        $indexResponse->assertDontSee('Harga Pasar Global');
        $indexResponse->assertDontSee('Rp ');

        // Harvest P&L PDF export is Owner-only, whereas Daily Record sheet stays accessible for assigned worker
        $this->actingAs($worker)->get(route('flocks.export-pdf', $flock))->assertForbidden();
        $this->actingAs($owner)->get(route('flocks.export-pdf', $flock))->assertOk();
        $this->actingAs($worker)->get(route('reports.daily-record', $flock))->assertOk();

        // flocks/show exposes Daily Record button to worker while hiding financial cards & P&L export
        $showResponse = $this->actingAs($worker)->get(route('flocks.show', $flock));
        $showResponse->assertOk();
        $showResponse->assertSee('Cetak Recording Harian');
        $showResponse->assertDontSee('Proyeksi Finansial');
        $showResponse->assertDontSee(route('flocks.export-pdf', $flock));
    }

    public function test_closed_flock_rejects_daily_records_and_feed_requests(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker']);

        $flock = Flock::create([
            'name' => 'Kandang Tutup Buku',
            'breed' => 'Cobb 500',
            'start_date' => '2026-09-01',
            'closed_at' => '2026-10-05',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $worker->id,
        ]);

        $record = $flock->dailyRecords()->create([
            'record_date' => '2026-09-10',
            'mortality' => 2,
            'culling' => 0,
            'feed_pre_starter_kg' => 20,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ]);

        $pendingRequest = $flock->feedRequests()->create([
            'feed_type' => 'Starter',
            'amount_kg' => 100,
            'status' => 'pending',
        ]);

        $shippedRequest = $flock->feedRequests()->create([
            'feed_type' => 'Finisher',
            'amount_kg' => 100,
            'status' => 'shipped',
        ]);

        // View hides mutation forms/buttons on closed flock
        $workerShow = $this->actingAs($worker)->get(route('flocks.show', $flock));
        $workerShow->assertOk();
        $workerShow->assertSee('Siklus Tutup Buku');
        $workerShow->assertDontSee('+ Catat Harian');
        $workerShow->assertDontSee('+ Minta Pakan');
        $workerShow->assertDontSee('Terima Pakan');
        $workerShow->assertDontSee('id="slide-over-title"', false);

        // Controllers block all mutation endpoints with HTTP 403
        $this->actingAs($worker)->get(route('daily_records.create', $flock))->assertForbidden();
        $this->actingAs($worker)->postJson(route('daily_records.store', $flock), [
            'record_date' => '2026-09-11',
            'mortality' => 1,
            'culling' => 0,
            'feed_pre_starter_kg' => 10,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertForbidden();
        $this->actingAs($worker)->put(route('daily_records.update', $record), [
            'record_date' => '2026-09-10',
            'mortality' => 3,
            'culling' => 0,
            'feed_pre_starter_kg' => 20,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertForbidden();
        $this->actingAs($worker)->delete(route('daily_records.destroy', $record))->assertForbidden();

        $this->actingAs($worker)->post(route('feed_requests.store', $flock), [
            'feed_type' => 'Starter',
            'amount_kg' => 50,
        ])->assertForbidden();
        $this->actingAs($owner)->post(route('feed_requests.ship', $pendingRequest))->assertForbidden();
        $this->actingAs($worker)->post(route('feed_requests.receive', $shippedRequest))->assertForbidden();
    }

    public function test_safe_delete_guards_on_coops_workers_and_flocks(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $workerWithPayroll = User::factory()->create(['role' => 'worker', 'name' => 'Pekerja Payroll']);
        $workerWithRecord = User::factory()->create(['role' => 'worker', 'name' => 'Pekerja Record']);
        $cleanWorker = User::factory()->create(['role' => 'worker', 'name' => 'Pekerja Bersih']);

        $hqCoop = Coop::create([
            'name' => 'Kandang Pusat',
            'address' => 'Pusat',
            'manager_name' => 'Owner',
            'is_headquarters' => true,
        ]);

        $branchWithFlock = Coop::create([
            'name' => 'Kandang Cabang Isi',
            'address' => 'Cabang',
            'manager_name' => 'Staff',
            'is_headquarters' => false,
        ]);

        $cleanCoop = Coop::create([
            'name' => 'Kandang Cabang Kosong',
            'address' => 'Cabang 2',
            'manager_name' => 'Staff 2',
            'is_headquarters' => false,
        ]);

        $closedFlock = Flock::create([
            'coop_id' => $branchWithFlock->id,
            'name' => 'Siklus Closed',
            'breed' => 'Cobb',
            'start_date' => '2026-09-01',
            'closed_at' => '2026-10-01',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $workerWithPayroll->id,
        ]);

        \App\Models\Payroll::create([
            'user_id' => $workerWithPayroll->id,
            'flock_id' => $closedFlock->id,
            'base_salary' => 2500000,
            'bonus_amount' => 250000,
            'total_paid' => 2750000,
            'payment_date' => '2026-10-05',
        ]);

        $activeRecordedFlock = Flock::create([
            'coop_id' => $branchWithFlock->id,
            'name' => 'Siklus Berisi Record',
            'breed' => 'Ross',
            'start_date' => '2026-10-15',
            'initial_population' => 1000,
            'status' => 'active',
            'worker_id' => $workerWithRecord->id,
        ]);

        $activeRecordedFlock->dailyRecords()->create([
            'record_date' => '2026-10-16',
            'mortality' => 1,
            'culling' => 0,
            'feed_pre_starter_kg' => 15,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ]);

        $cleanActiveFlock = Flock::create([
            'name' => 'Siklus Baru Kosong',
            'breed' => 'Cobb',
            'start_date' => '2026-10-20',
            'initial_population' => 500,
            'status' => 'active',
        ]);

        // CoopController@destroy
        $this->actingAs($owner)->delete(route('coops.destroy', $hqCoop))->assertSessionHas('error');
        $this->assertDatabaseHas('coops', ['id' => $hqCoop->id]);

        $this->actingAs($owner)->delete(route('coops.destroy', $branchWithFlock))->assertSessionHas('error');
        $this->assertDatabaseHas('coops', ['id' => $branchWithFlock->id]);

        $this->actingAs($owner)->delete(route('coops.destroy', $cleanCoop))->assertSessionHas('success');
        $this->assertDatabaseMissing('coops', ['id' => $cleanCoop->id]);

        // WorkerController@destroy
        $this->actingAs($owner)->delete(route('workers.destroy', $workerWithPayroll))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $workerWithPayroll->id]);

        $this->actingAs($owner)->delete(route('workers.destroy', $workerWithRecord))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $workerWithRecord->id]);

        $this->actingAs($owner)->delete(route('workers.destroy', $cleanWorker))->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $cleanWorker->id]);

        // FlockController@destroy
        $this->actingAs($owner)->delete(route('flocks.destroy', $closedFlock))->assertSessionHas('error');
        $this->assertDatabaseHas('flocks', ['id' => $closedFlock->id]);

        $this->actingAs($owner)->delete(route('flocks.destroy', $cleanActiveFlock))->assertSessionHas('success');
        $this->assertDatabaseMissing('flocks', ['id' => $cleanActiveFlock->id]);
    }

    public function test_dashboard_separates_owner_financials_from_worker_operations(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Owner Dashboard']);
        $worker = User::factory()->create(['role' => 'worker', 'name' => 'Worker Dashboard']);

        CashFlow::create([
            'transaction_date' => '2026-10-01',
            'description' => 'Modal Kas Awal',
            'type' => 'in',
            'amount' => 50000000,
        ]);

        $activeFlock = Flock::create([
            'name' => 'Kandang Aktif Worker',
            'breed' => 'Cobb 500',
            'start_date' => '2026-10-10',
            'initial_population' => 1000,
            'status' => 'active',
            'worker_id' => $worker->id,
        ]);

        $closedFlock = Flock::create([
            'name' => 'Kandang Panen Worker',
            'breed' => 'Cobb 500',
            'start_date' => '2026-08-01',
            'closed_at' => '2026-09-05',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $worker->id,
        ]);

        \App\Models\Payroll::create([
            'user_id' => $worker->id,
            'flock_id' => $closedFlock->id,
            'base_salary' => 2800000,
            'bonus_amount' => 420000,
            'total_paid' => 3220000,
            'payment_date' => '2026-09-10',
        ]);

        // Owner dashboard sees all financial KPIs
        $ownerDash = $this->actingAs($owner)->get(route('dashboard'));
        $ownerDash->assertOk();
        $ownerDash->assertSee('Total Saldo Kas');
        $ownerDash->assertSee('Estimasi Pendapatan');
        $ownerDash->assertSee('Laba Bersih Panen');
        $ownerDash->assertSee('50.000.000');

        // Worker dashboard hides enterprise financials and shows operational active flocks + personal latest payroll
        $workerDash = $this->actingAs($worker)->get(route('dashboard'));
        $workerDash->assertOk();
        $workerDash->assertDontSee('Total Saldo Kas');
        $workerDash->assertDontSee('Estimasi Pendapatan');
        $workerDash->assertDontSee('Laba Bersih Panen');
        $workerDash->assertDontSee('50.000.000');
        $workerDash->assertSee('Kandang Aktif Worker');
        $workerDash->assertSee('Input Recording Harian');
        $workerDash->assertSee('Gaji &amp; Bonus Terakhir', false);
        $workerDash->assertSee('3.220.000');
    }

    public function test_harvest_and_payroll_sync_cash_flow_and_enforce_owner_guards(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker', 'base_salary' => 3000000]);

        $flock = Flock::create([
            'name' => 'Kandang Panen & CashFlow',
            'breed' => 'Cobb 500',
            'start_date' => '2026-10-01',
            'initial_population' => 1000,
            'status' => 'active',
            'worker_id' => $worker->id,
        ]);

        // Worker cannot access flock edit/update or harvest routes
        $this->actingAs($worker)->get(route('flocks.edit', $flock))->assertForbidden();
        $this->actingAs($worker)->put(route('flocks.update', $flock), [
            'name' => 'Ubah Ilegal',
            'breed' => 'Ross',
            'start_date' => '2026-10-01',
            'initial_population' => 900,
        ])->assertForbidden();
        $this->actingAs($worker)->get(route('harvests.create', $flock))->assertForbidden();
        $this->actingAs($worker)->post(route('harvests.auto', $flock))->assertForbidden();

        // Owner updates DOC & feed prices + global market price
        $this->actingAs($owner)->post(route('settings.updatePrice'), [
            'global_market_price' => 24000,
            'doc_price' => 8500,
            'feed_price' => 9200,
        ])->assertRedirect();

        $this->assertDatabaseHas('system_settings', ['key' => 'global_market_price', 'value' => '24000']);
        $this->assertDatabaseHas('system_settings', ['key' => 'doc_price', 'value' => '8500']);
        $this->assertDatabaseHas('system_settings', ['key' => 'feed_price', 'value' => '9200']);

        // Owner records harvest -> closes flock + creates CashFlow IN automatically
        $this->actingAs($owner)->post(route('harvests.store', $flock), [
            'harvest_date' => '2026-11-05',
            'total_weight_kg' => 2000,
            'chicken_price_per_kg' => 24000,
            'feed_price_per_kg' => 9200,
            'buyer_name' => 'PT Mitra Broiler',
        ])->assertRedirect(route('flocks.show', $flock));

        $flock->refresh();
        $this->assertSame('closed', $flock->status);
        $this->assertDatabaseHas('cash_flows', [
            'type' => 'in',
            'amount' => 48000000,
        ]);

        // Closed flock cannot be edited or re-harvested even by Owner
        $this->actingAs($owner)->get(route('flocks.edit', $flock))->assertForbidden();
        $this->actingAs($owner)->get(route('harvests.create', $flock))->assertForbidden();
        $this->actingAs($owner)->post(route('harvests.auto', $flock))->assertForbidden();

        // Owner issues payroll -> creates CashFlow OUT automatically
        $this->actingAs($owner)->post(route('payrolls.store'), [
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => 3000000,
            'bonus_amount' => 300000,
        ])->assertRedirect(route('payrolls.index'));

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'out',
            'amount' => 3300000,
        ]);
    }

    public function test_feed_request_ship_validates_central_stock_and_pending_status(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker']);

        $flock = Flock::create([
            'name' => 'Kandang Cek Stok Gudang',
            'breed' => 'Cobb',
            'start_date' => '2026-11-01',
            'initial_population' => 1000,
            'status' => 'active',
            'worker_id' => $worker->id,
        ]);

        $feedRequest = $flock->feedRequests()->create([
            'feed_type' => 'Starter',
            'amount_kg' => 200,
            'status' => 'pending',
        ]);

        // Shipping without central stock must fail with session error and remain pending
        $this->actingAs($owner)->post(route('feed_requests.ship', $feedRequest))->assertSessionHas('error');
        $feedRequest->refresh();
        $this->assertSame('pending', $feedRequest->status);

        // Add sufficient central stock, then ship succeeds once and duplicate ship is blocked
        FeedInventory::create([
            'transaction_date' => '2026-11-01',
            'feed_name' => 'Starter Gudang',
            'transaction_type' => 'in',
            'quantity_kg' => 500,
            'price_per_kg' => 9000,
        ]);

        $this->actingAs($owner)->post(route('feed_requests.ship', $feedRequest))->assertSessionHas('success');
        $feedRequest->refresh();
        $this->assertSame('shipped', $feedRequest->status);

        // Second ship attempt must fail with error (not double-deduct)
        $this->actingAs($owner)->post(route('feed_requests.ship', $feedRequest))->assertSessionHas('error');
        $this->assertSame(1, FeedInventory::where('flock_id', $flock->id)->where('transaction_type', 'out')->count());
    }

    public function test_payroll_duplicate_guard_and_daily_record_integrity_rules(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker', 'base_salary' => 2900000]);

        // Assigning non-worker to flock fails validation
        $this->actingAs($owner)->post(route('flocks.store'), [
            'name' => 'Flock Invalid Worker',
            'breed' => 'Cobb',
            'start_date' => '2026-10-01',
            'initial_population' => 1000,
            'worker_id' => $owner->id,
        ])->assertSessionHasErrors('worker_id');

        $flock = Flock::create([
            'name' => 'Kandang Integrity Daily',
            'breed' => 'Cobb 500',
            'start_date' => '2026-10-10',
            'initial_population' => 100,
            'stock_pre_starter_kg' => 50,
            'status' => 'active',
            'worker_id' => $worker->id,
        ]);

        // Date before chick-in rejected
        $this->actingAs($worker)->postJson(route('daily_records.store', $flock), [
            'record_date' => '2026-10-09',
            'mortality' => 1,
            'culling' => 0,
            'feed_pre_starter_kg' => 1,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertStatus(422);

        // Valid record deducts stock + logs auto FeedInventory out
        $this->actingAs($worker)->postJson(route('daily_records.store', $flock), [
            'record_date' => '2026-10-11',
            'mortality' => 5,
            'culling' => 0,
            'feed_pre_starter_kg' => 2.5,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertOk();

        // Duplicate date rejected
        $this->actingAs($worker)->postJson(route('daily_records.store', $flock), [
            'record_date' => '2026-10-11',
            'mortality' => 1,
            'culling' => 0,
            'feed_pre_starter_kg' => 1,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertStatus(422);

        // Exceeding remaining live population rejected
        $this->actingAs($worker)->postJson(route('daily_records.store', $flock), [
            'record_date' => '2026-10-12',
            'mortality' => 96,
            'culling' => 0,
            'feed_pre_starter_kg' => 1,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
        ])->assertStatus(422);

        // Deleting record reverts flock feed stock & deletes auto FeedInventory out row
        $record = $flock->dailyRecords()->firstOrFail();
        $this->actingAs($worker)->delete(route('daily_records.destroy', $record))->assertRedirect();
        $flock->refresh();
        $this->assertEquals(50.0, (float) $flock->stock_pre_starter_kg);
        $this->assertDatabaseMissing('feed_inventories', ['flock_id' => $flock->id, 'quantity_kg' => 2.5]);

        // Payroll rejects active flock, non-worker, and duplicate payroll per (flock, worker)
        $this->actingAs($owner)->post(route('payrolls.store'), [
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
        ])->assertSessionHas('error');

        $flock->update(['status' => 'closed', 'closed_at' => '2026-11-01']);

        $this->actingAs($owner)->post(route('payrolls.store'), [
            'user_id' => $owner->id,
            'flock_id' => $flock->id,
        ])->assertSessionHasErrors('user_id');

        $this->actingAs($owner)->post(route('payrolls.store'), [
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => 2900000,
            'bonus_amount' => 290000,
        ])->assertSessionHas('success');

        $this->actingAs($owner)->post(route('payrolls.store'), [
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => 2900000,
            'bonus_amount' => 290000,
        ])->assertSessionHas('error');

        $this->assertSame(1, \App\Models\Payroll::where('flock_id', $flock->id)->where('user_id', $worker->id)->count());
    }

    public function test_registration_guard_ovk_feed_cash_sync_and_dompdf_downloads(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $worker = User::factory()->create(['role' => 'worker']);

        // Disabled public registration returns 403
        config(['app.allow_public_registration' => false]);
        $this->get('/register')->assertForbidden();
        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertForbidden();

        // OVK stock guard + CashFlow sync
        $this->actingAs($owner)->post(route('ovk.store'), [
            'record_date' => '2026-11-10',
            'item_name' => 'Vitamin Uji',
            'category' => 'obat',
            'transaction_type' => 'out',
            'quantity' => 3,
            'unit' => 'botol',
        ])->assertSessionHas('error');

        $this->actingAs($owner)->post(route('ovk.store'), [
            'record_date' => '2026-11-10',
            'item_name' => 'Vitamin Uji',
            'category' => 'obat',
            'transaction_type' => 'in',
            'quantity' => 10,
            'unit' => 'botol',
            'total_price' => 250000,
            'sync_cash_flow' => '1',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('cash_flows', ['type' => 'out', 'amount' => 250000]);

        // FeedInventory stock guard + CashFlow sync
        $this->actingAs($owner)->post(route('feeds.store'), [
            'transaction_date' => '2026-11-10',
            'feed_name' => 'Pakai Berlebih',
            'transaction_type' => 'out',
            'quantity_kg' => 50,
        ])->assertSessionHas('error');

        $this->actingAs($owner)->post(route('feeds.store'), [
            'transaction_date' => '2026-11-10',
            'feed_name' => 'BR-1 Masuk',
            'transaction_type' => 'in',
            'quantity_kg' => 100,
            'price_per_kg' => 9150,
            'sync_cash_flow' => '1',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('cash_flows', ['type' => 'out', 'amount' => 915000]);

        // Direct PDF binary download via DomPDF
        $flock = Flock::create([
            'name' => 'Kandang PDF',
            'breed' => 'Cobb',
            'start_date' => '2026-10-01',
            'closed_at' => '2026-11-05',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $worker->id,
        ]);

        $payroll = \App\Models\Payroll::create([
            'user_id' => $worker->id,
            'flock_id' => $flock->id,
            'base_salary' => 2500000,
            'bonus_amount' => 250000,
            'total_paid' => 2750000,
            'payment_date' => '2026-11-06',
        ]);

        $harvestPdf = $this->actingAs($owner)->get(route('flocks.export-pdf', ['flock' => $flock, 'download' => 1]));
        $harvestPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $harvestPdf->getContent());

        $payslipPdf = $this->actingAs($worker)->get(route('payrolls.payslip', ['payroll' => $payroll, 'download' => 1]));
        $payslipPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $payslipPdf->getContent());
    }

    public function test_daily_records_create_form_profile_guards_audit_logs_and_remaining_a4_pdfs(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Owner Audit']);
        $workerWithHistory = User::factory()->create(['role' => 'worker', 'name' => 'Worker Riwayat']);
        $cleanWorker = User::factory()->create(['role' => 'worker', 'name' => 'Worker Clean']);

        $coop = Coop::create([
            'name' => 'Kandang A4 Test',
            'address' => 'Hanau',
            'manager_name' => 'Owner Audit',
            'is_headquarters' => true,
        ]);

        $flock = Flock::create([
            'coop_id' => $coop->id,
            'name' => 'Flock Form & PDF',
            'breed' => 'Cobb 500',
            'start_date' => '2026-11-01',
            'initial_population' => 1000,
            'stock_pre_starter_kg' => 100,
            'status' => 'active',
            'worker_id' => $workerWithHistory->id,
        ]);

        // 1. daily_records/create renders 3-phase feed inputs & submits cleanly
        $createPage = $this->actingAs($workerWithHistory)->get(route('daily_records.create', $flock));
        $createPage->assertOk();
        $createPage->assertSee('feed_pre_starter_kg');
        $createPage->assertSee('feed_starter_kg');
        $createPage->assertSee('feed_finisher_kg');
        $createPage->assertSee('average_weight_gram');
        $createPage->assertDontSee('feed_consumed_kg');

        $this->actingAs($workerWithHistory)->post(route('daily_records.store', $flock), [
            'record_date' => '2026-11-02',
            'mortality' => 2,
            'culling' => 1,
            'feed_pre_starter_kg' => 15,
            'feed_starter_kg' => 0,
            'feed_finisher_kg' => 0,
            'average_weight_gram' => 65,
        ])->assertRedirect(route('flocks.show', $flock));

        // 2. ProfileController@destroy blocks owner & worker with history, allows clean worker
        $this->actingAs($owner)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertDatabaseHas('users', ['id' => $owner->id]);

        $this->actingAs($workerWithHistory)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertDatabaseHas('users', ['id' => $workerWithHistory->id]);

        $this->actingAs($cleanWorker)->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $cleanWorker->id]);

        // 3. Extended LogsActivity + grouped ActivityLogController search
        $this->actingAs($owner)->post(route('cash-flows.store'), [
            'transaction_date' => '2026-11-03',
            'description' => 'Pembelian Sekam Audit',
            'type' => 'out',
            'amount' => 350000,
        ])->assertRedirect(route('cash-flows.index'));

        $this->assertDatabaseHas('activity_logs', ['model_type' => 'CashFlow', 'action' => 'created']);
        $this->assertDatabaseHas('activity_logs', ['model_type' => 'DailyRecord', 'action' => 'created']);

        $logsResponse = $this->actingAs($owner)->get(route('activity_logs.index', ['search' => 'CashFlow']));
        $logsResponse->assertOk();
        $logsResponse->assertSee('CashFlow');

        // 4. All 3 remaining A4 reports support direct ?download=1 DomPDF binary export
        $dailyPdf = $this->actingAs($workerWithHistory)->get(route('reports.daily-record', ['flock' => $flock, 'download' => 1]));
        $dailyPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $dailyPdf->getContent());

        $cashFlowPdf = $this->actingAs($owner)->get(route('reports.cash-flow', ['download' => 1]));
        $cashFlowPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $cashFlowPdf->getContent());

        $auditPdf = $this->actingAs($owner)->get(route('reports.inventory-audit', ['download' => 1]));
        $auditPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $auditPdf->getContent());
    }

    public function test_analytics_coop_benchmarks_profit_sorting_and_a4_pdf_preview(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Owner Analitik']);
        $workerA = User::factory()->create(['role' => 'worker', 'name' => 'Worker Hanau']);
        $workerB = User::factory()->create(['role' => 'worker', 'name' => 'Worker Seruyan']);

        $coopA = Coop::create([
            'name' => 'Kandang Hanau Uji',
            'address' => 'Hanau',
            'manager_name' => 'Harries PJ',
            'is_headquarters' => true,
        ]);

        $coopB = Coop::create([
            'name' => 'Kandang Seruyan Uji',
            'address' => 'Seruyan',
            'manager_name' => 'Danang PJ',
            'is_headquarters' => false,
        ]);

        $flockA = Flock::create([
            'coop_id' => $coopA->id,
            'name' => 'Siklus Hanau S1',
            'breed' => 'Cobb 500',
            'start_date' => '2026-08-01',
            'closed_at' => '2026-09-05',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $workerA->id,
        ]);
        $flockA->dailyRecords()->create([
            'record_date' => '2026-09-05',
            'mortality' => 20,
            'culling' => 0,
            'feed_pre_starter_kg' => 500,
            'feed_starter_kg' => 1000,
            'feed_finisher_kg' => 1500,
            'average_weight_gram' => 2000,
        ]);
        $flockA->harvests()->create([
            'harvest_date' => '2026-09-05',
            'total_chickens_sold' => 980,
            'total_weight_kg' => 1960,
            'buyer_name' => 'PT Jaya',
            'chicken_price_per_kg' => 23000,
            'feed_price_per_kg' => 9000,
            'total_feed_cost' => 27000000,
            'gross_revenue' => 45080000,
            'net_profit' => 10080000,
        ]);

        $flockB = Flock::create([
            'coop_id' => $coopB->id,
            'name' => 'Siklus Seruyan S1',
            'breed' => 'Ross 308',
            'start_date' => '2026-09-10',
            'closed_at' => '2026-10-15',
            'initial_population' => 1000,
            'status' => 'closed',
            'worker_id' => $workerB->id,
        ]);
        $flockB->dailyRecords()->create([
            'record_date' => '2026-10-15',
            'mortality' => 50,
            'culling' => 10,
            'feed_pre_starter_kg' => 600,
            'feed_starter_kg' => 1100,
            'feed_finisher_kg' => 1700,
            'average_weight_gram' => 1800,
        ]);
        $flockB->harvests()->create([
            'harvest_date' => '2026-10-15',
            'total_chickens_sold' => 940,
            'total_weight_kg' => 1692,
            'buyer_name' => 'PT Mitra',
            'chicken_price_per_kg' => 22000,
            'feed_price_per_kg' => 9000,
            'total_feed_cost' => 30600000,
            'gross_revenue' => 37224000,
            'net_profit' => -1376000,
        ]);

        // Active flock on Coop A (must appear in active benchmarks without polluting closed averages)
        $activeFlock = Flock::create([
            'coop_id' => $coopA->id,
            'name' => 'Siklus Hanau Aktif S2',
            'breed' => 'Cobb 500',
            'start_date' => now()->subDays(20)->toDateString(),
            'initial_population' => 1000,
            'status' => 'active',
            'worker_id' => $workerA->id,
        ]);
        $activeFlock->dailyRecords()->create([
            'record_date' => now()->subDays(10)->toDateString(),
            'mortality' => 5,
            'culling' => 0,
            'feed_pre_starter_kg' => 300,
            'feed_starter_kg' => 200,
            'feed_finisher_kg' => 0,
            'average_weight_gram' => 450,
        ]);
        $activeFlock->dailyRecords()->create([
            'record_date' => now()->toDateString(),
            'mortality' => 5,
            'culling' => 0,
            'feed_pre_starter_kg' => 0,
            'feed_starter_kg' => 600,
            'feed_finisher_kg' => 300,
            'average_weight_gram' => 1150,
        ]);

        // Worker is blocked from enterprise analytics & report preview
        $this->actingAs($workerA)->get(route('analytics.index'))->assertForbidden();
        $this->actingAs($workerA)->get(route('reports.analytics'))->assertForbidden();

        // Owner sees leaderboard, active monitoring, clickable cycle links, net profit, and target IP 300
        $indexResponse = $this->actingAs($owner)->get(route('analytics.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Monitoring Performa Kandang Sedang Aktif');
        $indexResponse->assertSee('Siklus Hanau Aktif S2');
        $indexResponse->assertSee('activeGrowthChart');
        $indexResponse->assertSee('activeVsHistIpChart');
        $indexResponse->assertSee('Perbandingan Performa Antar Unit Kandang');
        $indexResponse->assertSee('Siklus Hanau S1');
        $indexResponse->assertSee('Siklus Seruyan S1');
        $indexResponse->assertSee(route('flocks.show', $flockA));
        $indexResponse->assertSee(route('flocks.show', $activeFlock));
        $indexResponse->assertSee('fcrWeightChart');
        $indexResponse->assertViewHas('farmAverages', fn ($avg) => $avg['cycles_count'] === 2);
        $indexResponse->assertViewHas('activeFlockBenchmarks', function ($activeList) use ($activeFlock) {
            return count($activeList) === 1
                && $activeList[0]['flock_id'] === $activeFlock->id
                && $activeList[0]['est_ip'] > 0
                && count($activeList[0]['growth_points']) === 2;
        });
        $indexResponse->assertViewHas('coopBenchmarks', function ($benchmarks) use ($coopA) {
            return count($benchmarks) === 2 && $benchmarks[0]['coop_id'] === $coopA->id;
        });

        // Print preview & PDF export (all coops & scoped to Coop B with unit signer)
        $previewAll = $this->actingAs($owner)->get(route('reports.analytics'));
        $previewAll->assertOk();
        $previewAll->assertSee('Seluruh Unit Kandang');
        $previewAll->assertSee('Siklus Hanau Aktif S2');
        $previewAll->assertSee('Owner Analitik');

        $previewScoped = $this->actingAs($owner)->get(route('reports.analytics', ['coop_id' => $coopB->id]));
        $previewScoped->assertOk();
        $previewScoped->assertSee('Kandang Seruyan Uji');
        $previewScoped->assertSee('Danang PJ');
        $previewScoped->assertSee('Siklus Seruyan S1');
        $previewScoped->assertDontSee('Siklus Hanau S1');
        $previewScoped->assertDontSee('Siklus Hanau Aktif S2');

        $pdfDownload = $this->actingAs($owner)->get(route('reports.analytics', ['coop_id' => $coopA->id, 'download' => 1]));
        $pdfDownload->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdfDownload->getContent());
    }
}
