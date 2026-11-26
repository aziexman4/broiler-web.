<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CashFlow;
use App\Models\Coop;
use App\Models\DailyRecord;
use App\Models\FeedInventory;
use App\Models\FeedRequest;
use App\Models\Flock;
use App\Models\Harvest;
use App\Models\OvkInventory;
use App\Models\Payroll;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\FlockMetricsService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FarmDataSeeder extends Seeder
{
    /**
     * Seed consistent operational, agronomic, inventory, HR, and cash-flow datasets.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Reset transactional domain tables so re-running `db:seed` stays clean and deterministic.
            ActivityLog::query()->delete();
            Payroll::query()->delete();
            Harvest::query()->delete();
            DailyRecord::query()->delete();
            FeedRequest::query()->delete();
            FeedInventory::query()->delete();
            OvkInventory::query()->delete();
            CashFlow::query()->delete();
            Flock::query()->delete();

            $owner = User::where('role', 'owner')->first();
            $harries = User::where('email', 'harries@smartcoop.com')->first();
            $danang = User::where('email', 'danang@smartcoop.com')->first();
            $mujawiri = User::where('email', 'mujawiri@smartcoop.com')->first();

            $today = Carbon::today();
            $docPrice = 8000;
            $marketPrice = 23000;
            $defaultFeedPrice = 9000;

            // -------------------------------------------------------------
            // 0. SYSTEM SETTINGS (Reference Market, DOC & Feed Prices)
            // -------------------------------------------------------------
            SystemSetting::updateOrCreate(['key' => 'global_market_price'], ['value' => (string) $marketPrice]);
            SystemSetting::updateOrCreate(['key' => 'doc_price'], ['value' => (string) $docPrice]);
            SystemSetting::updateOrCreate(['key' => 'feed_price'], ['value' => (string) $defaultFeedPrice]);

            // -------------------------------------------------------------
            // 1. MASTER COOP UNITS (Kandang Hanau HQ + 2 Branch Units)
            // -------------------------------------------------------------
            $coopHanau = Coop::updateOrCreate(
                ['name' => 'Kandang Hanau'],
                [
                    'tagline' => 'Sistem Informasi Manajemen Kepegawaian & Monitoring Produktivitas Kandang Ayam Broiler',
                    'address' => 'Desa Pembuang Hulu I, Kec. Hanau, Kab. Seruyan, Kalimantan Tengah',
                    'phone' => '(0538) 21088',
                    'email' => 'hanau@smartcoop.com',
                    'manager_name' => $harries ? $harries->name : 'Muhammad Harries Rasyadi',
                    'is_headquarters' => true,
                ]
            );

            $coopSeruyan = Coop::updateOrCreate(
                ['name' => 'Kandang Seruyan Utama'],
                [
                    'tagline' => 'Unit Produksi Broiler Wilayah Seruyan Hilir',
                    'address' => 'Jl. Ahmad Yani, Kuala Pembuang, Kec. Seruyan Hilir, Kab. Seruyan, Kalimantan Tengah',
                    'phone' => '(0538) 22104',
                    'email' => 'seruyan@smartcoop.com',
                    'manager_name' => $danang ? $danang->name : 'Danang Onky Romadhoni',
                    'is_headquarters' => false,
                ]
            );

            $coopPlasmaC = Coop::updateOrCreate(
                ['name' => 'Kandang Plasma C'],
                [
                    'tagline' => 'Unit Kemitraan Plasma Broiler Wilayah Danau Sembuluh',
                    'address' => 'Desa Telaga Pulang, Kec. Danau Sembuluh, Kab. Seruyan, Kalimantan Tengah',
                    'phone' => '(0538) 23019',
                    'email' => 'plasmac@smartcoop.com',
                    'manager_name' => $mujawiri ? $mujawiri->name : 'Mujawiri Apandie',
                    'is_headquarters' => false,
                ]
            );

            // Initial farm working capital so running cash balance stays positive and realistic.
            CashFlow::create([
                'transaction_date' => $today->copy()->subDays(290)->toDateString(),
                'description' => 'Setoran Modal Kerja Awal Operasional Peternakan (3 Unit Kandang)',
                'type' => 'in',
                'amount' => 1650000000,
            ]);

            // -------------------------------------------------------------
            // 2. CENTRAL FEED INVENTORY PROCUREMENTS (Synced to CashFlow)
            // -------------------------------------------------------------
            $feedPurchases = [
                ['days_ago' => 285, 'name' => 'BR-0 Pre-Starter Crumble (PT Japfa Comfeed)', 'qty' => 36000, 'price' => 9100, 'notes' => 'Pengadaan Pakan Pre-Starter Batch 1'],
                ['days_ago' => 284, 'name' => 'BR-1 Starter Super (PT Japfa Comfeed)', 'qty' => 48000, 'price' => 8900, 'notes' => 'Pengadaan Pakan Starter Batch 1'],
                ['days_ago' => 275, 'name' => 'BR-2 Finisher Premium (PT Charoen Pokphand)', 'qty' => 65000, 'price' => 8800, 'notes' => 'Pengadaan Pakan Finisher Batch 1'],
                ['days_ago' => 135, 'name' => 'BR-1 Starter Super (PT Japfa Comfeed)', 'qty' => 24000, 'price' => 8950, 'notes' => 'Restok Pakan Starter Pertengahan Tahun'],
                ['days_ago' => 115, 'name' => 'BR-2 Finisher Premium (PT Charoen Pokphand)', 'qty' => 38000, 'price' => 8900, 'notes' => 'Restok Pakan Finisher Siklus 3'],
                ['days_ago' => 25, 'name' => 'BR-1 & BR-2 Campuran (PT Japfa Comfeed)', 'qty' => 20000, 'price' => 9000, 'notes' => 'Pengadaan Stok Pakan Siklus Aktif Terkini'],
            ];

            foreach ($feedPurchases as $purchase) {
                $txDate = $today->copy()->subDays($purchase['days_ago'])->toDateString();
                FeedInventory::create([
                    'transaction_date' => $txDate,
                    'feed_name' => $purchase['name'],
                    'transaction_type' => 'in',
                    'quantity_kg' => $purchase['qty'],
                    'price_per_kg' => $purchase['price'],
                    'notes' => $purchase['notes'],
                ]);

                CashFlow::create([
                    'transaction_date' => $txDate,
                    'description' => "Pembelian Pakan: {$purchase['name']} ({$purchase['qty']} kg)",
                    'type' => 'out',
                    'amount' => (int) ($purchase['qty'] * $purchase['price']),
                ]);
            }

            // -------------------------------------------------------------
            // 3. NON-OVERLAPPING FLOCK DEFINITIONS (9 Closed + 3 Active)
            //    Kandang Hanau uses explicit PKL cycle dates:
            //    - Siklus 3: 16 Sep 2026 -> 20 Okt 2026 (34 hari panen)
            //    - Siklus 4 (Aktif): Jumat 06 Nov 2026 -> hari ini (penuh)
            // -------------------------------------------------------------
            $hanauActiveStart = Carbon::create(2026, 11, 6)->startOfDay();
            $hanauActiveDays = $today->greaterThanOrEqualTo($hanauActiveStart)
                ? min(35, (int) $hanauActiveStart->diffInDays($today) + 1)
                : 1;

            $flockDefinitions = [
                // Unit A: Kandang Hanau (Harries — Tier IP >= 400 -> Bonus 20%)
                [
                    'coop' => $coopHanau,
                    'name' => 'Kandang Hanau - Siklus 1',
                    'breed' => 'Cobb 500',
                    'initial_population' => 4500,
                    'status' => 'closed',
                    'start_date' => '2026-02-18',
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $harries,
                    'price_per_kg' => 22600,
                    'feed_price_per_kg' => 8800,
                    'buyer' => 'CV Unggas Jaya',
                    'tier' => 'high',
                ],
                [
                    'coop' => $coopHanau,
                    'name' => 'Kandang Hanau - Siklus 2',
                    'breed' => 'Cobb 500',
                    'initial_population' => 5000,
                    'status' => 'closed',
                    'start_date' => '2026-05-05',
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $harries,
                    'price_per_kg' => 23200,
                    'feed_price_per_kg' => 8900,
                    'buyer' => 'PT Ayam Makmur',
                    'tier' => 'high',
                ],
                [
                    'coop' => $coopHanau,
                    'name' => 'Kandang Hanau - Siklus 3',
                    'breed' => 'Cobb 500',
                    'initial_population' => 5000,
                    'status' => 'closed',
                    'start_date' => '2026-09-16', // 5 hari sebelum 21 Sep 2026
                    'harvest_age' => 34,          // Selesai panen dalam 34 hari (20 Okt 2026)
                    'days_recorded' => 34,
                    'worker' => $harries,
                    'price_per_kg' => 22900,
                    'feed_price_per_kg' => 8850,
                    'buyer' => 'UD Berkah Broiler',
                    'tier' => 'high',
                    'ews_spike_day' => 12,
                ],
                [
                    'coop' => $coopHanau,
                    'name' => 'Kandang Hanau - Siklus 4 (Aktif)',
                    'breed' => 'Cobb 500',
                    'initial_population' => 5000,
                    'status' => 'active',
                    'start_date' => $hanauActiveStart->toDateString(), // Jumat, 2 minggu setelah panen 20 Okt -> 06 Nov 2026
                    'harvest_age' => null,
                    'days_recorded' => $hanauActiveDays, // Terisi penuh s.d. hari ini (6-25 Nov = 20 hari)
                    'worker' => $harries,
                    'tier' => 'high',
                    'planned_shipments' => ['Pre-Starter' => 3300, 'Starter' => 3200],
                    'pending_request' => ['feed_type' => 'Finisher', 'amount_kg' => 3500, 'days_ago' => 1],
                ],

                // Unit B: Kandang Seruyan Utama (Danang — Tier IP 350-399 -> Bonus 15%)
                [
                    'coop' => $coopSeruyan,
                    'name' => 'Kandang Seruyan Utama - Siklus 1',
                    'breed' => 'Ross 308',
                    'initial_population' => 4000,
                    'status' => 'closed',
                    'start_days_ago' => 260,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $danang,
                    'price_per_kg' => 22200,
                    'feed_price_per_kg' => 8750,
                    'buyer' => 'PT Mitra Broiler',
                    'tier' => 'mid',
                ],
                [
                    'coop' => $coopSeruyan,
                    'name' => 'Kandang Seruyan Utama - Siklus 2',
                    'breed' => 'Ross 308',
                    'initial_population' => 4500,
                    'status' => 'closed',
                    'start_days_ago' => 185,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $danang,
                    'price_per_kg' => 23200,
                    'feed_price_per_kg' => 8950,
                    'buyer' => 'CV Sentosa Poultry',
                    'tier' => 'mid',
                ],
                [
                    'coop' => $coopSeruyan,
                    'name' => 'Kandang Seruyan Utama - Siklus 3',
                    'breed' => 'Ross 308',
                    'initial_population' => 4500,
                    'status' => 'closed',
                    'start_days_ago' => 95,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $danang,
                    'price_per_kg' => 22800,
                    'feed_price_per_kg' => 8900,
                    'buyer' => 'CV Sumber Makmur',
                    'tier' => 'mid',
                ],
                [
                    'coop' => $coopSeruyan,
                    'name' => 'Kandang Seruyan Utama - Siklus 4 (Aktif)',
                    'breed' => 'Ross 308',
                    'initial_population' => 4500,
                    'status' => 'active',
                    'start_days_ago' => 18, // Starter phase; recorded days 1..18 (ends yesterday) + EWS spike on day 14
                    'harvest_age' => null,
                    'days_recorded' => 18,
                    'worker' => $danang,
                    'tier' => 'mid',
                    'ews_spike_day' => 14,
                    'planned_shipments' => ['Pre-Starter' => 2900, 'Starter' => 3500],
                    'shipped_request' => ['feed_type' => 'Finisher', 'amount_kg' => 3500, 'days_ago' => 1],
                ],

                // Unit C: Kandang Plasma C (Mujawiri — Tier IP 300-349 -> Bonus 10%)
                [
                    'coop' => $coopPlasmaC,
                    'name' => 'Kandang Plasma C - Siklus 1',
                    'breed' => 'Hubbard',
                    'initial_population' => 3500,
                    'status' => 'closed',
                    'start_days_ago' => 245,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $mujawiri,
                    'price_per_kg' => 22400,
                    'feed_price_per_kg' => 8800,
                    'buyer' => 'CV Sumber Unggas',
                    'tier' => 'standard',
                ],
                [
                    'coop' => $coopPlasmaC,
                    'name' => 'Kandang Plasma C - Siklus 2',
                    'breed' => 'Hubbard',
                    'initial_population' => 4000,
                    'status' => 'closed',
                    'start_days_ago' => 165,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $mujawiri,
                    'price_per_kg' => 22800,
                    'feed_price_per_kg' => 8900,
                    'buyer' => 'PT Nusantara Broiler',
                    'tier' => 'standard',
                ],
                [
                    'coop' => $coopPlasmaC,
                    'name' => 'Kandang Plasma C - Siklus 3',
                    'breed' => 'Hubbard',
                    'initial_population' => 4000,
                    'status' => 'closed',
                    'start_days_ago' => 85,
                    'harvest_age' => 35,
                    'days_recorded' => 35,
                    'worker' => $mujawiri,
                    'price_per_kg' => 23500,
                    'feed_price_per_kg' => 9000,
                    'buyer' => 'UD Jaya Bersama',
                    'tier' => 'standard',
                ],
                [
                    'coop' => $coopPlasmaC,
                    'name' => 'Kandang Plasma C - Siklus 4 (Aktif)',
                    'breed' => 'Hubbard',
                    'initial_population' => 4000,
                    'status' => 'active',
                    'start_days_ago' => 29, // Finisher phase, 30 records inclusive through TODAY -> demonstrates "✓ Tercatat Hari Ini" & ready for 1-click harvest demo
                    'harvest_age' => null,
                    'days_recorded' => 30,
                    'worker' => $mujawiri,
                    'tier' => 'standard',
                    'planned_shipments' => ['Pre-Starter' => 2700, 'Starter' => 4100, 'Finisher' => 4500],
                    'pending_request' => ['feed_type' => 'Finisher', 'amount_kg' => 1500, 'days_ago' => 0],
                ],
            ];

            foreach ($flockDefinitions as $idx => $def) {
                $coop = $def['coop'];
                $startDate = isset($def['start_date'])
                    ? Carbon::parse($def['start_date'])->startOfDay()
                    : $today->copy()->subDays($def['start_days_ago']);
                $cycleLengthDays = (int) ($def['harvest_age'] ?? 35);
                $closedAt = $def['status'] === 'closed'
                    ? $startDate->copy()->addDays($cycleLengthDays)
                    : null;

                $flock = Flock::create([
                    'coop_id' => $coop->id,
                    'name' => $def['name'],
                    'breed' => $def['breed'],
                    'start_date' => $startDate->toDateString(),
                    'initial_population' => $def['initial_population'],
                    'status' => $def['status'],
                    'closed_at' => $closedAt ? $closedAt->copy()->setTime(16, 0) : null,
                    'worker_id' => $def['worker']?->id,
                    'stock_pre_starter_kg' => 0,
                    'stock_starter_kg' => 0,
                    'stock_finisher_kg' => 0,
                ]);

                // DOC Chick-In Cost recorded in CashFlow
                CashFlow::create([
                    'transaction_date' => $startDate->toDateString(),
                    'description' => "Pengadaan DOC ({$def['breed']}, " . number_format($def['initial_population']) . " ekor) — {$flock->name}",
                    'type' => 'out',
                    'amount' => (int) ($def['initial_population'] * $docPrice),
                ]);

                if ($owner) {
                    $logCreatedAt = $startDate->copy()->subDays(1)->setTime(9, 0);
                    ActivityLog::create([
                        'user_id' => $owner->id,
                        'action' => 'Kandang Dibuat',
                        'model_type' => 'App\Models\Flock',
                        'description' => "Owner membuat siklus kandang baru: {$flock->name} ({$coop->name})",
                        'created_at' => $logCreatedAt,
                        'updated_at' => $logCreatedAt,
                    ]);
                }

                // Agronomic curve tuned per tier (deterministic, zero rand() drift)
                [$baseGramPerBird, $slopeGramPerBird, $targetHarvestWeightKg, $mortModulo] = match ($def['tier']) {
                    'high' => [18.0, 3.92, 2.18, 4],
                    'mid' => [18.8, 4.02, 2.08, 3],
                    default => [19.8, 4.15, 1.97, 3],
                };

                $consumedByPhase = [
                    'Pre-Starter' => 0.0,
                    'Starter' => 0.0,
                    'Finisher' => 0.0,
                ];
                $accumulatedFeed = 0.0;
                $accumulatedMortality = 0;
                $accumulatedCulling = 0;

                for ($day = 1; $day <= $def['days_recorded']; $day++) {
                    $recordDate = $startDate->copy()->addDays($day - 1)->toDateString();

                    // Slight deterministic daily wave within +/- 4 kg
                    $wave = (($idx + $day) % 5) - 2;
                    $feedGramPerBird = $baseGramPerBird + ($day * $slopeGramPerBird);
                    $dailyFeed = round((($def['initial_population'] * $feedGramPerBird) / 1000) + ($wave * 1.5), 1);

                    // EWS spike (> 0.5% of initial population) on specified brooding/weather day
                    if (isset($def['ews_spike_day']) && $day === $def['ews_spike_day']) {
                        $dailyMortality = (int) ceil($def['initial_population'] * 0.0058); // ~0.58%
                    } else {
                        $dailyMortality = (($day + $idx) % $mortModulo === 0) ? 2 : 1;
                    }
                    $dailyCulling = ($day % 7 === 0) ? 1 : 0;

                    // Broiler sigmoid-like growth trajectory (42g DOC to target harvest weight by target cycle length)
                    $progress = $day / $cycleLengthDays;
                    $curvedProgress = (0.35 * $progress) + (0.65 * pow($progress, 1.25));
                    $avgWeightGram = (int) round(42 + (($targetHarvestWeightKg * 1000 - 42) * $curvedProgress));

                    $preStarter = 0.0;
                    $starter = 0.0;
                    $finisher = 0.0;

                    if ($day <= 14) {
                        $preStarter = $dailyFeed;
                        $consumedByPhase['Pre-Starter'] = round($consumedByPhase['Pre-Starter'] + $dailyFeed, 2);
                    } elseif ($day <= 24) {
                        $starter = $dailyFeed;
                        $consumedByPhase['Starter'] = round($consumedByPhase['Starter'] + $dailyFeed, 2);
                    } else {
                        $finisher = $dailyFeed;
                        $consumedByPhase['Finisher'] = round($consumedByPhase['Finisher'] + $dailyFeed, 2);
                    }

                    $flock->dailyRecords()->create([
                        'record_date' => $recordDate,
                        'mortality' => $dailyMortality,
                        'culling' => $dailyCulling,
                        'feed_pre_starter_kg' => $preStarter,
                        'feed_starter_kg' => $starter,
                        'feed_finisher_kg' => $finisher,
                        'average_weight_gram' => $avgWeightGram,
                    ]);

                    $accumulatedFeed = round($accumulatedFeed + $dailyFeed, 2);
                    $accumulatedMortality += $dailyMortality;
                    $accumulatedCulling += $dailyCulling;
                }

                // Completed Feed Logistics synchronized with actual phase usage
                $phaseStartDayOffsets = [
                    'Pre-Starter' => -1,
                    'Starter' => 13,
                    'Finisher' => 23,
                ];

                $shipments = $def['status'] === 'closed'
                    ? $consumedByPhase
                    : ($def['planned_shipments'] ?? []);

                $remainingStocks = [
                    'Pre-Starter' => 0.0,
                    'Starter' => 0.0,
                    'Finisher' => 0.0,
                ];

                foreach ($shipments as $phase => $shippedAmount) {
                    $shippedAmount = round((float) $shippedAmount, 2);
                    if ($shippedAmount <= 0) {
                        continue;
                    }

                    // Ensure active flock shipment always covers what was already consumed in daily records
                    if ($def['status'] === 'active' && $shippedAmount < $consumedByPhase[$phase]) {
                        $shippedAmount = round($consumedByPhase[$phase] + 150, 2);
                    }

                    $reqTime = $startDate->copy()->addDays($phaseStartDayOffsets[$phase])->setTime(10, 0);
                    if ($reqTime->greaterThan($today->copy()->endOfDay())) {
                        $reqTime = $today->copy()->setTime(9, 0);
                    }

                    FeedRequest::create([
                        'flock_id' => $flock->id,
                        'feed_type' => $phase,
                        'amount_kg' => $shippedAmount,
                        'status' => 'completed',
                        'created_at' => $reqTime,
                        'updated_at' => $reqTime->copy()->addHours(2),
                    ]);

                    FeedInventory::create([
                        'transaction_date' => $reqTime->toDateString(),
                        'feed_name' => "{$phase} (Distribusi Kandang)",
                        'transaction_type' => 'out',
                        'quantity_kg' => $shippedAmount,
                        'flock_id' => $flock->id,
                        'notes' => "Pengiriman Pakan {$phase} ke {$flock->name}",
                        'created_at' => $reqTime,
                        'updated_at' => $reqTime,
                    ]);

                    if ($owner) {
                        ActivityLog::create([
                            'user_id' => $owner->id,
                            'action' => 'Pakan Dikirim',
                            'model_type' => 'App\Models\FeedRequest',
                            'description' => "Owner menyetujui dan mengirim {$shippedAmount} Kg pakan {$phase} ke {$flock->name}",
                            'created_at' => $reqTime,
                            'updated_at' => $reqTime,
                        ]);
                    }

                    if ($def['status'] === 'active') {
                        $remainingStocks[$phase] = round(max(0, $shippedAmount - $consumedByPhase[$phase]), 2);
                    }
                }

                if ($def['status'] === 'active') {
                    $flock->update([
                        'stock_pre_starter_kg' => $remainingStocks['Pre-Starter'],
                        'stock_starter_kg' => $remainingStocks['Starter'],
                        'stock_finisher_kg' => $remainingStocks['Finisher'],
                    ]);
                }

                // Active Pending Feed Request (for Owner sidebar badge & "Setujui & Kirim" workflow)
                if (!empty($def['pending_request'])) {
                    $pend = $def['pending_request'];
                    $pendTime = $today->copy()->subDays($pend['days_ago'])->setTime(11, 15);
                    FeedRequest::create([
                        'flock_id' => $flock->id,
                        'feed_type' => $pend['feed_type'],
                        'amount_kg' => $pend['amount_kg'],
                        'status' => 'pending',
                        'created_at' => $pendTime,
                        'updated_at' => $pendTime,
                    ]);
                }

                // Active Shipped Feed Request (deducted from central warehouse; ready for Worker "Terima Pakan")
                if (!empty($def['shipped_request'])) {
                    $ship = $def['shipped_request'];
                    $shipTime = $today->copy()->subDays($ship['days_ago'])->setTime(14, 0);
                    FeedRequest::create([
                        'flock_id' => $flock->id,
                        'feed_type' => $ship['feed_type'],
                        'amount_kg' => $ship['amount_kg'],
                        'status' => 'shipped',
                        'created_at' => $shipTime,
                        'updated_at' => $shipTime,
                    ]);

                    FeedInventory::create([
                        'transaction_date' => $shipTime->toDateString(),
                        'feed_name' => "{$ship['feed_type']} (Kirim ke Kandang)",
                        'transaction_type' => 'out',
                        'quantity_kg' => $ship['amount_kg'],
                        'flock_id' => $flock->id,
                        'notes' => "Dikirim ke Kandang: {$flock->name}",
                        'created_at' => $shipTime,
                        'updated_at' => $shipTime,
                    ]);

                    if ($owner) {
                        ActivityLog::create([
                            'user_id' => $owner->id,
                            'action' => 'Pakan Dikirim',
                            'model_type' => 'App\Models\FeedRequest',
                            'description' => "Owner menyetujui dan mengirim {$ship['amount_kg']} Kg pakan {$ship['feed_type']} ke {$flock->name}",
                            'created_at' => $shipTime,
                            'updated_at' => $shipTime,
                        ]);
                    }
                }

                // Latest daily recording audit log for active flocks
                if ($def['status'] === 'active' && $def['worker']) {
                    $lastRecordDate = $startDate->copy()->addDays($def['days_recorded'] - 1)->setTime(17, 30);
                    ActivityLog::create([
                        'user_id' => $def['worker']->id,
                        'action' => 'Input Recording Harian',
                        'model_type' => 'App\Models\DailyRecord',
                        'description' => "{$def['worker']->name} mencatat laporan harian ke-{$def['days_recorded']} pada {$flock->name}",
                        'created_at' => $lastRecordDate,
                        'updated_at' => $lastRecordDate,
                    ]);
                }

                // Closed Flock Harvest + Payroll + CashFlow Sync (matches HarvestController & PayrollController)
                if ($def['status'] === 'closed') {
                    $chickensSold = max(0, $def['initial_population'] - $accumulatedMortality - $accumulatedCulling);
                    $totalWeightKg = round($chickensSold * $targetHarvestWeightKg, 2);

                    $grossRevenue = round($totalWeightKg * $def['price_per_kg'], 2);
                    $totalFeedCost = round($accumulatedFeed * $def['feed_price_per_kg'], 2);
                    $totalDocCost = $def['initial_population'] * $docPrice;
                    $netProfit = round($grossRevenue - $totalFeedCost - $totalDocCost, 2);

                    $harvestDate = $closedAt->toDateString();

                    $flock->harvests()->create([
                        'harvest_date' => $harvestDate,
                        'total_chickens_sold' => $chickensSold,
                        'total_weight_kg' => $totalWeightKg,
                        'buyer_name' => $def['buyer'],
                        'chicken_price_per_kg' => $def['price_per_kg'],
                        'feed_price_per_kg' => $def['feed_price_per_kg'],
                        'total_feed_cost' => $totalFeedCost,
                        'gross_revenue' => $grossRevenue,
                        'net_profit' => $netProfit,
                        'revenue' => $grossRevenue,
                    ]);

                    CashFlow::create([
                        'transaction_date' => $harvestDate,
                        'description' => "Pendapatan Panen {$flock->name} ({$def['buyer']})",
                        'type' => 'in',
                        'amount' => (int) round($grossRevenue),
                    ]);

                    if ($owner) {
                        $harvestLogAt = $closedAt->copy()->setTime(16, 15);
                        ActivityLog::create([
                            'user_id' => $owner->id,
                            'action' => 'Panen Selesai',
                            'model_type' => 'App\Models\Harvest',
                            'description' => "Panen tutup buku {$flock->name}: " . number_format($chickensSold) . ' ekor (' . number_format($totalWeightKg, 2) . " kg) ke {$def['buyer']}",
                            'created_at' => $harvestLogAt,
                            'updated_at' => $harvestLogAt,
                        ]);
                    }

                    if ($def['worker']) {
                        $worker = $def['worker'];
                        $baseSalary = (int) $worker->base_salary;

                        $metrics = FlockMetricsService::calculateForFlock($flock);
                        $bonus = FlockMetricsService::evaluateIpBonus($metrics['ip'], $baseSalary);
                        $bonusAmount = $bonus['recommended_bonus'];
                        $totalPaid = $baseSalary + $bonusAmount;
                        $paymentDate = $closedAt->copy()->addDays(2)->toDateString();

                        Payroll::create([
                            'user_id' => $worker->id,
                            'flock_id' => $flock->id,
                            'base_salary' => $baseSalary,
                            'bonus_amount' => $bonusAmount,
                            'total_paid' => $totalPaid,
                            'payment_date' => $paymentDate,
                        ]);

                        CashFlow::create([
                            'transaction_date' => $paymentDate,
                            'description' => "Pembayaran Gaji & Bonus: {$worker->name} ({$flock->name})",
                            'type' => 'out',
                            'amount' => $totalPaid,
                        ]);

                        if ($owner) {
                            $payLogAt = $closedAt->copy()->addDays(2)->setTime(10, 0);
                            ActivityLog::create([
                                'user_id' => $owner->id,
                                'action' => 'Gaji Diterbitkan',
                                'model_type' => 'App\Models\Payroll',
                                'description' => "Owner menerbitkan slip gaji {$worker->name} ({$flock->name}) sebesar Rp " . number_format($totalPaid, 0, ',', '.'),
                                'created_at' => $payLogAt,
                                'updated_at' => $payLogAt,
                            ]);
                        }
                    }
                }
            }

            // -------------------------------------------------------------
            // 4. OVK INVENTORY (Balanced In/Out Stock + Synced CashFlow)
            // -------------------------------------------------------------
            $ovkItems = [
                ['days_ago' => 270, 'name' => 'Vita Stress 100gr', 'category' => 'obat', 'type' => 'in', 'qty' => 80, 'unit' => 'sachet', 'price' => 1200000, 'desc' => 'Pengadaan vitamin anti-stres awal tahun'],
                ['days_ago' => 268, 'name' => 'Vaksin ND Hitchner B1', 'category' => 'vaksin', 'type' => 'in', 'qty' => 30, 'unit' => 'botol', 'price' => 1800000, 'desc' => 'Pengadaan vaksinasi ND awal siklus'],
                ['days_ago' => 260, 'name' => 'Formades Desinfektan', 'category' => 'kimia', 'type' => 'in', 'qty' => 25, 'unit' => 'liter', 'price' => 1625000, 'desc' => 'Pengadaan desinfektan sterilisasi kandang'],
                ['days_ago' => 210, 'name' => 'Vita Stress 100gr', 'category' => 'obat', 'type' => 'out', 'qty' => 25, 'unit' => 'sachet', 'price' => null, 'desc' => 'Pemakaian brooding Kandang Hanau & Seruyan'],
                ['days_ago' => 180, 'name' => 'Vaksin ND Hitchner B1', 'category' => 'vaksin', 'type' => 'out', 'qty' => 18, 'unit' => 'botol', 'price' => null, 'desc' => 'Pemakaian vaksinasi Siklus 2 (3 unit kandang)'],
                ['days_ago' => 140, 'name' => 'Vaksin Gumboro A2', 'category' => 'vaksin', 'type' => 'in', 'qty' => 24, 'unit' => 'botol', 'price' => 1520000, 'desc' => 'Pengadaan vaksin gumboro fase starter'],
                ['days_ago' => 95, 'name' => 'Formades Desinfektan', 'category' => 'kimia', 'type' => 'out', 'qty' => 12, 'unit' => 'liter', 'price' => null, 'desc' => 'Sterilisasi istirahat kandang menuju Siklus 3'],
                ['days_ago' => 40, 'name' => 'Antiseptik Medisep', 'category' => 'kimia', 'type' => 'in', 'qty' => 18, 'unit' => 'liter', 'price' => 810000, 'desc' => 'Pengadaan antiseptik sanitasi siklus aktif'],
                ['days_ago' => 15, 'name' => 'Vaksin Gumboro A2', 'category' => 'vaksin', 'type' => 'out', 'qty' => 9, 'unit' => 'botol', 'price' => null, 'desc' => 'Vaksinasi gumboro Kandang Seruyan Utama Siklus 4'],
                ['days_ago' => 3, 'name' => 'Vita Stress 100gr', 'category' => 'obat', 'type' => 'out', 'qty' => 12, 'unit' => 'sachet', 'price' => null, 'desc' => 'Pemberian vitamin brooding Kandang Hanau Siklus 4'],
            ];

            foreach ($ovkItems as $ovk) {
                $ovkDate = $today->copy()->subDays($ovk['days_ago'])->toDateString();
                OvkInventory::create([
                    'record_date' => $ovkDate,
                    'item_name' => $ovk['name'],
                    'category' => $ovk['category'],
                    'transaction_type' => $ovk['type'],
                    'quantity' => $ovk['qty'],
                    'unit' => $ovk['unit'],
                    'total_price' => $ovk['price'],
                    'description' => $ovk['desc'],
                ]);

                if ($ovk['type'] === 'in' && (int) ($ovk['price'] ?? 0) > 0) {
                    CashFlow::create([
                        'transaction_date' => $ovkDate,
                        'description' => "Belanja OVK: {$ovk['name']} ({$ovk['qty']} {$ovk['unit']})",
                        'type' => 'out',
                        'amount' => (int) $ovk['price'],
                    ]);
                }
            }

            // -------------------------------------------------------------
            // 5. OPERATIONAL & BY-PRODUCT CASH FLOWS (Relative to Today)
            // -------------------------------------------------------------
            $operationalCashFlows = [
                ['days_ago' => 282, 'desc' => 'Pembelian Sekam Alas Kandang (3 Truk) — Persiapan Siklus 1', 'type' => 'out', 'amount' => 3600000],
                ['days_ago' => 278, 'desc' => 'Pembelian Gas LPG Pemanas Brooding DOC (18 Tabung)', 'type' => 'out', 'amount' => 1150000],
                ['days_ago' => 244, 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam (Hanau Siklus 1)', 'type' => 'in', 'amount' => 2150000],
                ['days_ago' => 223, 'desc' => 'Pembayaran Listrik & Token Air Kandang Utama', 'type' => 'out', 'amount' => 1850000],
                ['days_ago' => 209, 'desc' => 'Hasil Penjualan Karung Pakan Bekas (Plasma C Siklus 1)', 'type' => 'in', 'amount' => 1450000],
                ['days_ago' => 186, 'desc' => 'Pembelian Sekam Alas Kandang & Kapur Sanitasi (Siklus 2)', 'type' => 'out', 'amount' => 4200000],
                ['days_ago' => 179, 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam (Hanau Siklus 2)', 'type' => 'in', 'amount' => 3800000],
                ['days_ago' => 148, 'desc' => 'Perawatan Kipas Exhaust Blower & Instalasi Nipple Minum', 'type' => 'out', 'amount' => 1650000],
                ['days_ago' => 129, 'desc' => 'Hasil Penjualan Karung Pakan Bekas & Sisa Sekam Kering', 'type' => 'in', 'amount' => 1750000],
                ['days_ago' => 71, 'desc' => 'Pembelian Sekam & Gas LPG Brooding (Hanau Siklus 3 — Chick-in Sep)', 'type' => 'out', 'amount' => 3100000],
                ['days_ago' => 35, 'desc' => 'Hasil Penjualan Pupuk Karungan (Hanau Siklus 3 — Panen Okt)', 'type' => 'in', 'amount' => 3200000],
                ['days_ago' => 58, 'desc' => 'Pembayaran Listrik, Token Air & Kebersihan Unit Kandang', 'type' => 'out', 'amount' => 2100000],
                ['days_ago' => 44, 'desc' => 'Hasil Penjualan Pupuk & Karung Bekas (Plasma C Siklus 3)', 'type' => 'in', 'amount' => 2450000],
                ['days_ago' => 20, 'desc' => 'Pembelian Sekam Baru & Gas LPG Brooding (Hanau Siklus 4 Aktif)', 'type' => 'out', 'amount' => 2950000],
                ['days_ago' => 5, 'desc' => 'Pembelian Gas LPG Pemanas Tambahan & Desinfektan Harian', 'type' => 'out', 'amount' => 1250000],
                ['days_ago' => 2, 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam & Karung Bekas', 'type' => 'in', 'amount' => 2900000],
            ];

            foreach ($operationalCashFlows as $cf) {
                CashFlow::create([
                    'transaction_date' => $today->copy()->subDays($cf['days_ago'])->toDateString(),
                    'description' => $cf['desc'],
                    'type' => $cf['type'],
                    'amount' => $cf['amount'],
                ]);
            }
        });
    }
}
