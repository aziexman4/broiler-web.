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

        // Assigned worker can view daily record report & CSV export
        $this->actingAs($workerA)->get(route('reports.daily-record', $flockA))->assertOk();
        $this->actingAs($workerA)->get(route('flocks.export-csv', $flockA))->assertOk();

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
        $responseRange->assertSee('Periode: 01 Agu 2026 — 31 Agu 2026');
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
}
