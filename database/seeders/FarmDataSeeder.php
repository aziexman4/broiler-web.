<?php

namespace Database\Seeders;

use App\Models\CashFlow;
use App\Models\Coop;
use App\Models\Flock;
use App\Models\OvkInventory;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FarmDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $harries = User::where('email', 'harries@smartcoop.com')->first();
        $danang = User::where('email', 'danang@smartcoop.com')->first();
        $mujawiri = User::where('email', 'mujawiri@smartcoop.com')->first();

        // 0. MASTER UNIT & LOKASI KANDANG (COOPS)
        $coopHanau = Coop::firstOrCreate(
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

        $coopSeruyan = Coop::firstOrCreate(
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

        $coopPlasmaC = Coop::firstOrCreate(
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

        // Initial Feed Stock In
        \App\Models\FeedInventory::create([
            'transaction_date' => Carbon::now()->subMonths(11)->toDateString(),
            'feed_name' => 'BR-1 Super Starter',
            'transaction_type' => 'in',
            'quantity_kg' => 110000,
            'price_per_kg' => 8800,
            'notes' => 'Pengadaan Pakan Starter Awal Tahun (PT Japfa Comfeed)',
        ]);

        \App\Models\FeedInventory::create([
            'transaction_date' => Carbon::now()->subMonths(6)->toDateString(),
            'feed_name' => 'BR-2 Finisher Premium',
            'transaction_type' => 'in',
            'quantity_kg' => 95000,
            'price_per_kg' => 8900,
            'notes' => 'Pengadaan Pakan Finisher Mid-Year (PT Charoen Pokphand)',
        ]);

        // -------------------------------------------------------------
        // 1. FLOCKS & HARVESTS DEFINITIONS (9 Closed + 3 Active)
        // -------------------------------------------------------------
        $flockDefinitions = [
            // Location A: Kandang Hanau (Worker: Harries)
            [
                'name' => 'Kandang Hanau - Siklus 1',
                'breed' => 'Cobb 500',
                'initial_population' => 4500,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(10)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(10)->addDays(35)->toDateString(),
                'worker' => $harries,
                'price_per_kg' => 22000,
                'feed_price_per_kg' => 8800,
                'buyer' => 'CV Unggas Jaya',
            ],
            [
                'name' => 'Kandang Hanau - Siklus 2',
                'breed' => 'Cobb 500',
                'initial_population' => 5000,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(7)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(7)->addDays(35)->toDateString(),
                'worker' => $harries,
                'price_per_kg' => 23000,
                'feed_price_per_kg' => 8900,
                'buyer' => 'PT Ayam Makmur',
            ],
            [
                'name' => 'Kandang Hanau - Siklus 3',
                'breed' => 'Cobb 500',
                'initial_population' => 5000,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(3)->addDays(35)->toDateString(),
                'worker' => $harries,
                'price_per_kg' => 22500,
                'feed_price_per_kg' => 8850,
                'buyer' => 'UD Berkah Broiler',
            ],
            [
                'name' => 'Kandang Hanau - Siklus 4 (Aktif)',
                'breed' => 'Cobb 500',
                'initial_population' => 5000,
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(4)->toDateString(),
                'closed_at' => null,
                'worker' => $harries,
                'stock_pre_starter' => 3500,
                'stock_starter' => 0,
                'stock_finisher' => 0,
            ],

            // Location B: Kandang Seruyan Utama (Worker: Danang)
            [
                'name' => 'Kandang Seruyan Utama - Siklus 1',
                'breed' => 'Ross 308',
                'initial_population' => 4000,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(8)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(8)->addDays(35)->toDateString(),
                'worker' => $danang,
                'price_per_kg' => 21800,
                'feed_price_per_kg' => 8700,
                'buyer' => 'PT Mitra Broiler',
            ],
            [
                'name' => 'Kandang Seruyan Utama - Siklus 2',
                'breed' => 'Ross 308',
                'initial_population' => 4500,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(4)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(4)->addDays(35)->toDateString(),
                'worker' => $danang,
                'price_per_kg' => 23200,
                'feed_price_per_kg' => 8950,
                'buyer' => 'CV Sentosa Poultry',
            ],
            [
                'name' => 'Kandang Seruyan Utama - Siklus 3',
                'breed' => 'Ross 308',
                'initial_population' => 4500,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(2)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(2)->addDays(35)->toDateString(),
                'worker' => $danang,
                'price_per_kg' => 22800,
                'feed_price_per_kg' => 8900,
                'buyer' => 'CV Sumber Makmur',
            ],
            [
                'name' => 'Kandang Seruyan Utama - Siklus 4 (Aktif)',
                'breed' => 'Ross 308',
                'initial_population' => 4500,
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(18)->toDateString(),
                'closed_at' => null,
                'worker' => $danang,
                'stock_pre_starter' => 0,
                'stock_starter' => 4000,
                'stock_finisher' => 0,
            ],

            // Location C: Kandang Plasma C (Worker: Mujawiri)
            [
                'name' => 'Kandang Plasma C - Siklus 1',
                'breed' => 'Hubbard',
                'initial_population' => 3500,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(9)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(9)->addDays(35)->toDateString(),
                'worker' => $mujawiri,
                'price_per_kg' => 22200,
                'feed_price_per_kg' => 8800,
                'buyer' => 'CV Sumber Unggas',
            ],
            [
                'name' => 'Kandang Plasma C - Siklus 2',
                'breed' => 'Hubbard',
                'initial_population' => 4000,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(5)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(5)->addDays(35)->toDateString(),
                'worker' => $mujawiri,
                'price_per_kg' => 22800,
                'feed_price_per_kg' => 8900,
                'buyer' => 'PT Nusantara Broiler',
            ],
            [
                'name' => 'Kandang Plasma C - Siklus 3',
                'breed' => 'Hubbard',
                'initial_population' => 4000,
                'status' => 'closed',
                'start_date' => Carbon::now()->subMonths(2)->toDateString(),
                'closed_at' => Carbon::now()->subMonths(2)->addDays(35)->toDateString(),
                'worker' => $mujawiri,
                'price_per_kg' => 23500,
                'feed_price_per_kg' => 9000,
                'buyer' => 'UD Jaya Bersama',
            ],
            [
                'name' => 'Kandang Plasma C - Siklus 4 (Aktif)',
                'breed' => 'Hubbard',
                'initial_population' => 4000,
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(32)->toDateString(),
                'closed_at' => null,
                'worker' => $mujawiri,
                'stock_pre_starter' => 0,
                'stock_starter' => 0,
                'stock_finisher' => 6000,
            ],
        ];

        foreach ($flockDefinitions as $def) {
            if (str_starts_with($def['name'], 'Kandang Hanau')) {
                $coopId = $coopHanau->id;
            } elseif (str_starts_with($def['name'], 'Kandang Seruyan Utama')) {
                $coopId = $coopSeruyan->id;
            } elseif (str_starts_with($def['name'], 'Kandang Plasma C')) {
                $coopId = $coopPlasmaC->id;
            } else {
                $coopId = $coopHanau->id;
            }

            $flock = Flock::create([
                'coop_id' => $coopId,
                'name' => $def['name'],
                'breed' => $def['breed'],
                'start_date' => $def['start_date'],
                'initial_population' => $def['initial_population'],
                'status' => $def['status'],
                'closed_at' => $def['closed_at'],
                'worker_id' => isset($def['worker']) ? $def['worker']->id : null,
                'stock_pre_starter_kg' => $def['stock_pre_starter'] ?? 0,
                'stock_starter_kg' => $def['stock_starter'] ?? 0,
                'stock_finisher_kg' => $def['stock_finisher'] ?? 0,
            ]);

            $owner = User::where('role', 'owner')->first();

            \App\Models\ActivityLog::create([
                'user_id' => $owner->id,
                'action' => 'Kandang Dibuat',
                'model_type' => 'App\Models\Flock',
                'description' => "Owner membuat siklus kandang baru: {$flock->name}",
                'created_at' => Carbon::parse($flock->start_date)->subDays(2),
                'updated_at' => Carbon::parse($flock->start_date)->subDays(2),
            ]);

            // Simulasi Logistik & Supply Chain
            $phases = [
                'Pre-Starter' => $def['stock_pre_starter'] ?? 0,
                'Starter' => $def['stock_starter'] ?? 0,
                'Finisher' => $def['stock_finisher'] ?? 0,
            ];
            
            // Untuk flock yang statusnya 'closed', kita asumsikan mereka juga menerima pakan total yang dikonsumsi
            // Mari hitung estimasi total pakan yang dihabiskan untuk flock yang closed, agar disuntikkan juga ke FeedInventory
            $daysToRecord = ($def['status'] === 'closed') ? 35 : Carbon::parse($def['start_date'])->diffInDays(Carbon::now());
            $popFactor = $def['initial_population'] / 1000;

            if ($def['status'] === 'closed') {
                $phases['Pre-Starter'] = round(650 * $popFactor, 0);
                $phases['Starter'] = round(980 * $popFactor, 0);
                $phases['Finisher'] = round(1550 * $popFactor, 0);
            }

            foreach ($phases as $phase => $amount) {
                if ($amount > 0) {
                    $reqDate = Carbon::parse($flock->start_date)->subDays(1);
                    
                    \App\Models\FeedRequest::create([
                        'flock_id' => $flock->id,
                        'feed_type' => $phase,
                        'amount_kg' => $amount,
                        'status' => 'completed',
                        'created_at' => $reqDate,
                        'updated_at' => $reqDate,
                    ]);

                    \App\Models\FeedInventory::create([
                        'transaction_date' => $reqDate->toDateString(),
                        'feed_name' => "$phase (Distribusi Kandang)",
                        'transaction_type' => 'out',
                        'quantity_kg' => $amount,
                        'flock_id' => $flock->id,
                        'notes' => "Pengiriman Pakan $phase ke {$flock->name}",
                        'created_at' => $reqDate,
                        'updated_at' => $reqDate,
                    ]);
                    
                    \App\Models\ActivityLog::create([
                        'user_id' => $owner->id,
                        'action' => 'Pakan Dikirim',
                        'model_type' => 'App\Models\FeedRequest',
                        'description' => "Owner menyetujui dan mengirim $amount Kg pakan $phase ke {$flock->name}",
                        'created_at' => $reqDate,
                        'updated_at' => $reqDate,
                    ]);
                }
            }

            // Determine number of days of daily records to generate
            $daysToRecord = ($def['status'] === 'closed') ? 35 : Carbon::parse($def['start_date'])->diffInDays(Carbon::now());

            // Agronomic profile per coop unit so closed cycles demonstrate realistic FCR (1.47 - 1.73)
            // and all 3 IP bonus tiers (Hanau: IP >= 400 [20%], Seruyan: IP 350-399 [15%], Plasma C: IP 300-349 [10%])
            if ($coopId === $coopHanau->id) {
                $baseGramPerBird = 18.0;
                $slopeGramPerBird = 3.92;
                $targetHarvestWeightKg = 2.16 + (rand(0, 3) / 100);
            } elseif ($coopId === $coopSeruyan->id) {
                $baseGramPerBird = 18.8;
                $slopeGramPerBird = 4.02;
                $targetHarvestWeightKg = 2.06 + (rand(0, 3) / 100);
            } else {
                $baseGramPerBird = 19.8;
                $slopeGramPerBird = 4.15;
                $targetHarvestWeightKg = 1.96 + (rand(0, 2) / 100);
            }

            $accumulatedFeed = 0;
            $accumulatedMortality = 0;
            $accumulatedCulling = 0;

            for ($day = 1; $day <= $daysToRecord; $day++) {
                $recordDate = Carbon::parse($def['start_date'])->addDays($day - 1)->toDateString();

                // Realistic feed curve scaled to flock population (~3.1 - 3.3 kg/bird over 35 days)
                $feedGramPerBird = $baseGramPerBird + ($day * $slopeGramPerBird);
                $dailyFeed = round((($def['initial_population'] * $feedGramPerBird) / 1000) + rand(-8, 8), 1);
                $dailyMortality = rand(1, 3);
                $dailyCulling = (rand(1, 10) > 8) ? 1 : 0;
                $avgWeightGram = (int) round(42 + (($targetHarvestWeightKg * 1000 - 42) * ($day / 35)));

                $preStarter = 0;
                $starter = 0;
                $finisher = 0;

                if ($day <= 14) {
                    $preStarter = $dailyFeed;
                } elseif ($day <= 24) {
                    $starter = $dailyFeed;
                } else {
                    $finisher = $dailyFeed;
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

                $accumulatedFeed += $dailyFeed;
                $accumulatedMortality += $dailyMortality;
                $accumulatedCulling += $dailyCulling;
            }

            // Create Harvest and Payroll if the flock is closed
            if ($def['status'] === 'closed') {
                $chickensSold = max(0, $def['initial_population'] - $accumulatedMortality - $accumulatedCulling);

                // Realistic harvest weight per unit tier (1.96 - 2.19 kg)
                $avgWeight = $targetHarvestWeightKg;
                $totalWeightKg = round($chickensSold * $avgWeight, 2);

                $grossRevenue = $totalWeightKg * $def['price_per_kg'];
                $totalFeedCost = $accumulatedFeed * $def['feed_price_per_kg'];
                $netProfit = $grossRevenue - $totalFeedCost;

                $harvest = $flock->harvests()->create([
                    'harvest_date' => $def['closed_at'],
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

                // Create Payroll Record for the assigned worker
                if (isset($def['worker'])) {
                    $worker = $def['worker'];
                    $baseSalary = (int) $worker->base_salary;

                    // Calculate actual IP & tiered bonus percentage (Phase 2 formula)
                    $fcr = $totalWeightKg > 0 ? round($accumulatedFeed / $totalWeightKg, 2) : 0;
                    $avgWeightKg = $chickensSold > 0 ? ($totalWeightKg / $chickensSold) : 0;
                    $livability = $def['initial_population'] > 0 ? (($chickensSold / $def['initial_population']) * 100) : 0;
                    $harvestAge = max(1, intval(Carbon::parse($def['start_date'])->diffInDays(Carbon::parse($def['closed_at']))));
                    $ip = ($fcr > 0 && $harvestAge > 0)
                        ? round(($livability * $avgWeightKg) / ($fcr * $harvestAge) * 100, 2)
                        : 0;

                    if ($ip >= 400) {
                        $bonusPercentage = 20;
                    } elseif ($ip >= 350) {
                        $bonusPercentage = 15;
                    } elseif ($ip >= 300) {
                        $bonusPercentage = 10;
                    } else {
                        $bonusPercentage = 0;
                    }

                    $bonusAmount = (int) round($baseSalary * ($bonusPercentage / 100));
                    $totalPaid = $baseSalary + $bonusAmount;

                    Payroll::create([
                        'user_id' => $worker->id,
                        'flock_id' => $flock->id,
                        'base_salary' => $baseSalary,
                        'bonus_amount' => $bonusAmount,
                        'total_paid' => $totalPaid,
                        'payment_date' => Carbon::parse($def['closed_at'])->addDays(2)->toDateString(),
                    ]);
                }
            }
        }

        // -------------------------------------------------------------
        // 2. OVK INVENTORY SEEDER
        // -------------------------------------------------------------
        $ovkItems = [
            ['date' => Carbon::now()->subMonths(9)->toDateString(), 'name' => 'Vita Stress 100gr', 'category' => 'obat', 'type' => 'in', 'qty' => 50, 'unit' => 'sachet', 'price' => 750000, 'desc' => 'Stok vitamin awal siklus'],
            ['date' => Carbon::now()->subMonths(8)->toDateString(), 'name' => 'Vaksin ND Hitchner B1', 'category' => 'vaksin', 'type' => 'in', 'qty' => 20, 'unit' => 'botol', 'price' => 1200000, 'desc' => 'Vaksinasi umur 4 hari'],
            ['date' => Carbon::now()->subMonths(7)->toDateString(), 'name' => 'Formades Desinfektan', 'category' => 'kimia', 'type' => 'in', 'qty' => 10, 'unit' => 'liter', 'price' => 650000, 'desc' => 'Sterilisasi ruang kandang'],
            ['date' => Carbon::now()->subMonths(6)->toDateString(), 'name' => 'Vita Stress 100gr', 'category' => 'obat', 'type' => 'out', 'qty' => 15, 'unit' => 'sachet', 'price' => null, 'desc' => 'Pemakaian Kandang Hanau'],
            ['date' => Carbon::now()->subMonths(4)->toDateString(), 'name' => 'Vaksin Gumboro A2', 'category' => 'vaksin', 'type' => 'in', 'qty' => 15, 'unit' => 'botol', 'price' => 950000, 'desc' => 'Pengadaan vaksin gumboro'],
            ['date' => Carbon::now()->subMonths(2)->toDateString(), 'name' => 'Antiseptik Medisep', 'category' => 'kimia', 'type' => 'in', 'qty' => 12, 'unit' => 'liter', 'price' => 540000, 'desc' => 'Desinfeksi rutin tempat minum'],
        ];

        foreach ($ovkItems as $ovk) {
            OvkInventory::create([
                'record_date' => $ovk['date'],
                'item_name' => $ovk['name'],
                'category' => $ovk['category'],
                'transaction_type' => $ovk['type'],
                'quantity' => $ovk['qty'],
                'unit' => $ovk['unit'],
                'total_price' => $ovk['price'],
                'description' => $ovk['desc'],
            ]);
        }

        // -------------------------------------------------------------
        // 3. CASH FLOW SEEDER (Februari 2026 – November 2026)
        // -------------------------------------------------------------
        $cashFlows = [
            // Februari 2026
            ['date' => '2026-02-05', 'desc' => 'Pembelian Sekam Alas Kandang (3 Truk) — Persiapan Plasma C', 'type' => 'out', 'amount' => 3600000],
            ['date' => '2026-02-18', 'desc' => 'Pembelian Gas LPG Pemanas Brooding DOC (15 Tabung)', 'type' => 'out', 'amount' => 950000],
            ['date' => '2026-02-24', 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam (Hanau Siklus 1)', 'type' => 'in', 'amount' => 1850000],

            // Maret 2026
            ['date' => '2026-03-10', 'desc' => 'Pembayaran Listrik & Token Kandang Utama', 'type' => 'out', 'amount' => 1850000],
            ['date' => '2026-03-25', 'desc' => 'Hasil Penjualan Karung Pakan Bekas (Kandang Plasma C)', 'type' => 'in', 'amount' => 1450000],

            // April 2026 (Bulan aktif panen & chick-in: in & out seimbang)
            ['date' => '2026-04-06', 'desc' => 'Pembelian Sekam Alas Kandang (Kandang Hanau Siklus 2)', 'type' => 'out', 'amount' => 3600000],
            ['date' => '2026-04-12', 'desc' => 'Pembelian Gas LPG Brooding DOC & Token Air Kandang', 'type' => 'out', 'amount' => 1100000],
            ['date' => '2026-04-14', 'desc' => 'Hasil Penjualan Pupuk Karungan (Kotoran Ayam)', 'type' => 'in', 'amount' => 4500000],
            ['date' => '2026-04-24', 'desc' => 'Hasil Penjualan Karung Pakan Bekas & Sisa Sekam Kering', 'type' => 'in', 'amount' => 1350000],

            // Mei 2026
            ['date' => '2026-05-08', 'desc' => 'Pembelian Sekam Tambahan & Kapur Sanitasi Lantai Kandang', 'type' => 'out', 'amount' => 1800000],
            ['date' => '2026-05-15', 'desc' => 'Pembayaran Token Listrik & Biaya Air Operasional Kandang', 'type' => 'out', 'amount' => 1450000],

            // Juni 2026 (Bulan aktif pemeliharaan & maintenance)
            ['date' => '2026-06-05', 'desc' => 'Pembelian Sekam Alas Kandang (Kandang Plasma C Siklus 2)', 'type' => 'out', 'amount' => 2400000],
            ['date' => '2026-06-14', 'desc' => 'Perbaikan Kipas Ventilation Blower & Instalasi Kabel', 'type' => 'out', 'amount' => 1200000],
            ['date' => '2026-06-20', 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam (Kandang Hanau)', 'type' => 'in', 'amount' => 2950000],
            ['date' => '2026-06-27', 'desc' => 'Hasil Penjualan Sisa Sekam & Limbah Non-Ayam', 'type' => 'in', 'amount' => 980000],

            // Juli 2026
            ['date' => '2026-07-10', 'desc' => 'Pembelian Gas LPG Pemanas Brooding DOC (Seruyan Utama)', 'type' => 'out', 'amount' => 1050000],
            ['date' => '2026-07-23', 'desc' => 'Hasil Penjualan Karung Pakan Bekas (Kandang Plasma C)', 'type' => 'in', 'amount' => 1600000],

            // Agustus 2026 (Bulan aktif panen & persiapan siklus 3)
            ['date' => '2026-08-03', 'desc' => 'Pembelian Sekam Alas Kandang (Kandang Hanau Siklus 3)', 'type' => 'out', 'amount' => 2600000],
            ['date' => '2026-08-07', 'desc' => 'Pembayaran Token Listrik & Air Kandang (Semua Unit)', 'type' => 'out', 'amount' => 1950000],
            ['date' => '2026-08-14', 'desc' => 'Hasil Penjualan Karung Pakan Bekas', 'type' => 'in', 'amount' => 2100000],
            ['date' => '2026-08-21', 'desc' => 'Perawatan Instalasi Tempat Minum Nipple & Kipas Blower', 'type' => 'out', 'amount' => 1350000],
            ['date' => '2026-08-26', 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam (Seruyan Utama)', 'type' => 'in', 'amount' => 3200000],

            // September 2026
            ['date' => '2026-09-08', 'desc' => 'Pembelian Gas LPG Brooding DOC (Seruyan & Plasma C)', 'type' => 'out', 'amount' => 1250000],
            ['date' => '2026-09-22', 'desc' => 'Hasil Penjualan Pupuk Kotoran Ayam (Hanau Siklus 3)', 'type' => 'in', 'amount' => 1750000],

            // Oktober 2026 (Bulan aktif transisi siklus tutup buku -> siklus aktif)
            ['date' => '2026-10-05', 'desc' => 'Pembelian Sekam Baru untuk Siklus Aktif', 'type' => 'out', 'amount' => 4200000],
            ['date' => '2026-10-12', 'desc' => 'Pembayaran Token Listrik, Air & Gas LPG Brooding DOC', 'type' => 'out', 'amount' => 1750000],
            ['date' => '2026-10-22', 'desc' => 'Hasil Penjualan Pupuk Karungan (Kotoran Ayam Siklus 3)', 'type' => 'in', 'amount' => 3400000],
            ['date' => '2026-10-27', 'desc' => 'Hasil Penjualan Karung Pakan Bekas & Sisa Sekam', 'type' => 'in', 'amount' => 1250000],

            // November 2026 (Bulan berjalan - 3 unit siklus aktif)
            ['date' => '2026-11-03', 'desc' => 'Pembayaran Token Listrik & Air Kandang Siklus Aktif', 'type' => 'out', 'amount' => 1900000],
            ['date' => '2026-11-07', 'desc' => 'Pembelian Gas LPG Pemanas Brooding DOC (Hanau Siklus 4)', 'type' => 'out', 'amount' => 1150000],
            ['date' => '2026-11-10', 'desc' => 'Hasil Penjualan Pupuk Karungan Kotoran Ayam', 'type' => 'in', 'amount' => 2650000],
            ['date' => '2026-11-12', 'desc' => 'Perawatan Rutin Kipas Exhaust Blower & Instalasi Kandang', 'type' => 'out', 'amount' => 850000],
            ['date' => '2026-11-14', 'desc' => 'Hasil Penjualan Karung Pakan Bekas & Limbah Non-Ayam', 'type' => 'in', 'amount' => 1150000],
        ];

        foreach ($cashFlows as $cf) {
            CashFlow::create([
                'transaction_date' => $cf['date'],
                'description' => $cf['desc'],
                'type' => $cf['type'],
                'amount' => $cf['amount'],
            ]);
        }
    }
}
