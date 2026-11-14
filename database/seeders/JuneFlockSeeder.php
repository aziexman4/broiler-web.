<?php

namespace Database\Seeders;

use App\Models\DailyRecord;
use App\Models\FeedInventory;
use App\Models\Flock;
use App\Models\GrowthSampling;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JuneFlockSeeder extends Seeder
{
    /**
     * Run the database seeds for June Flock simulation (28 days).
     */
    public function run(): void
    {
        // 1. Fetch a random worker (or fallback to first worker)
        $worker = User::where('role', 'worker')->inRandomOrder()->first();

        // 2. Create the active June Flock
        $flock = Flock::create([
            'name' => 'Kandang Hanau Timur - Siklus Juni',
            'breed' => 'Cobb 500',
            'start_date' => '2026-06-01',
            'initial_population' => 5000,
            'status' => 'active',
            'worker_id' => $worker ? $worker->id : null,
        ]);

        $currentPopulation = 5000;
        $startDate = Carbon::parse('2026-06-01');

        // 3. Loop for exactly 28 days (June 1st to June 28th)
        for ($day = 1; $day <= 28; $day++) {
            $recordDate = (clone $startDate)->addDays($day - 1)->toDateString();

            // Simulate realistic mortality (0 to 5) & culling (0 to 2)
            $mortality = rand(0, 5);
            $culling = (rand(1, 10) > 8) ? rand(1, 2) : 0;
            $currentPopulation -= ($mortality + $culling);

            // Realistic daily feed consumption in Kg: (population * (15 + (day * 4))) / 1000 + variance
            $feedConsumedKg = round(($currentPopulation * (15 + ($day * 4))) / 1000 + (rand(-5, 5) / 10), 2);

            // Save Daily Record
            DailyRecord::create([
                'flock_id' => $flock->id,
                'record_date' => $recordDate,
                'mortality' => $mortality,
                'culling' => $culling,
                'feed_consumed_kg' => max(10, $feedConsumedKg),
            ]);

            // Auto-deduct feed stock
            FeedInventory::create([
                'transaction_date' => $recordDate,
                'feed_name' => 'Pakan Kandang (Auto)',
                'transaction_type' => 'out',
                'quantity_kg' => max(10, $feedConsumedKg),
                'flock_id' => $flock->id,
                'notes' => 'Konsumsi Harian Kandang: ' . $flock->name,
            ]);

            // Save weekly weight growth sampling (Every 7 days)
            if ($day % 7 === 0) {
                $weekNumber = $day / 7;
                $avgWeightGram = round(40 + ($day * 45) + rand(-10, 10));

                GrowthSampling::create([
                    'flock_id' => $flock->id,
                    'sampling_date' => $recordDate,
                    'week_number' => $weekNumber,
                    'average_weight_g' => $avgWeightGram,
                ]);
            }
        }
    }
}
