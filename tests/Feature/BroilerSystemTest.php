<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Coop;
use App\Models\Flock;
use App\Models\User;
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
}
