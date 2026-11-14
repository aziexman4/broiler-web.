<?php

namespace Database\Seeders;

use App\Models\CashFlow;
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

        // Initial Feed Stock In
        \App\Models\FeedInventory::create([
            'transaction_date' => Carbon::now()->subMonths(11)->toDateString(),
            'feed_name' => 'BR-1 Super Starter',
            'transaction_type' => 'in',
            'quantity_kg' => 30000,
            'price_per_kg' => 8800,
            'notes' => 'Pengadaan Pakan Starter Awal Tahun (PT Japfa Comfeed)',
        ]);

        \App\Models\FeedInventory::create([
            'transaction_date' => Carbon::now()->subMonths(6)->toDateString(),
            'feed_name' => 'BR-2 Finisher Premium',
            'transaction_type' => 'in',
            'quantity_kg' => 25000,
            'price_per_kg' => 8900,
            'notes' => 'Pengadaan Pakan Finisher Mid-Year (PT Charoen Pokphand)',
        ]);

        // -------------------------------------------------------------
        // 1. FLOCKS & HARVESTS DEFINITIONS (8 Closed + 2 Active)
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
            $flock = Flock::create([
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
            
            if ($def['status'] === 'closed') {
                $totalEstimatedPreStarter = 14 * 40; // Kasar rata-rata
                $totalEstimatedStarter = 10 * 80;
                $totalEstimatedFinisher = 11 * 120;
                $phases['Pre-Starter'] = $totalEstimatedPreStarter;
                $phases['Starter'] = $totalEstimatedStarter;
                $phases['Finisher'] = $totalEstimatedFinisher;
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
            
            $accumulatedFeed = 0;
            $accumulatedMortality = 0;
            $accumulatedCulling = 0;

            for ($day = 1; $day <= $daysToRecord; $day++) {
                $recordDate = Carbon::parse($def['start_date'])->addDays($day - 1)->toDateString();
                
                // Realistic feed curve: starts around 25-35kg and scales up to ~220kg/day
                $dailyFeed = round(25 + ($day * 5.2) + rand(-3, 3), 1);
                $dailyMortality = rand(1, 4);
                $dailyCulling = (rand(1, 10) > 7) ? rand(1, 2) : 0;

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
                ]);

                $accumulatedFeed += $dailyFeed;
                $accumulatedMortality += $dailyMortality;
                $accumulatedCulling += $dailyCulling;
            }

            // Create Harvest and Payroll if the flock is closed
            if ($def['status'] === 'closed') {
                $chickensSold = max(0, $def['initial_population'] - $accumulatedMortality - $accumulatedCulling);
                
                // Average weight around 1.95 - 2.15 kg
                $avgWeight = 1.95 + (rand(5, 20) / 100);
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
                    $baseSalary = $worker->base_salary;
                    
                    // Since all closed flocks perform well, bonus of Rp 500.000 is granted (IP > 300)
                    $bonusAmount = 500000;
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
        // 3. CASH FLOW SEEDER
        // -------------------------------------------------------------
        $cashFlows = [
            ['date' => Carbon::now()->subMonths(9)->toDateString(), 'desc' => 'Pembelian Sekam Alas Kandang (3 Truk)', 'type' => 'out', 'amount' => 3600000],
            ['date' => Carbon::now()->subMonths(8)->toDateString(), 'desc' => 'Pembayaran Listrik & Token Kandang Utama', 'type' => 'out', 'amount' => 1850000],
            ['date' => Carbon::now()->subMonths(7)->toDateString(), 'desc' => 'Hasil Penjualan Pupuk Karungan (Kotoran Ayam)', 'type' => 'in', 'amount' => 4500000],
            ['date' => Carbon::now()->subMonths(5)->toDateString(), 'desc' => 'Perbaikan Kipas Ventilation Blower', 'type' => 'out', 'amount' => 1200000],
            ['date' => Carbon::now()->subMonths(3)->toDateString(), 'desc' => 'Hasil Penjualan Karung Pakan Bekas', 'type' => 'in', 'amount' => 2100000],
            ['date' => Carbon::now()->subMonths(1)->toDateString(), 'desc' => 'Pembelian Sekam Baru untuk Siklus Aktif', 'type' => 'out', 'amount' => 4200000],
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
