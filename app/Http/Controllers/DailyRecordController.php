<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Models\FeedInventory;
use App\Models\Flock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DailyRecordController extends Controller
{
    /**
     * Show the form for creating a new daily record for a flock.
     */
    /**
     * Reject any mutation attempt when a flock cycle has been closed.
     */
    private function ensureFlockIsActive(Flock $flock): void
    {
        abort_if(
            $flock->status === 'closed',
            403,
            'Siklus kandang sudah ditutup (Closed). Data recording harian tidak dapat ditambahkan atau diubah lagi.'
        );
    }

    public function create(Flock $flock)
    {
        Gate::authorize('update', $flock);
        $this->ensureFlockIsActive($flock);

        return view('daily_records.create', compact('flock'));
    }

    /**
     * Store a newly created daily record in storage and automatically deduct feed inventory.
     */
    public function store(Request $request, Flock $flock)
    {
        Gate::authorize('update', $flock);
        $this->ensureFlockIsActive($flock);

        $validated = $request->validate([
            'record_date' => 'required|date',
            'mortality' => 'required|integer|min:0',
            'culling' => 'required|integer|min:0',
            'feed_pre_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_pre_starter_kg],
            'feed_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_starter_kg],
            'feed_finisher_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_finisher_kg],
            'average_weight_gram' => 'nullable|integer|min:0',
        ], [
            'feed_pre_starter_kg.max' => 'Pakan Pre-Starter tidak cukup! Sisa stok: ' . $flock->stock_pre_starter_kg . ' kg.',
            'feed_starter_kg.max' => 'Pakan Starter tidak cukup! Sisa stok: ' . $flock->stock_starter_kg . ' kg.',
            'feed_finisher_kg.max' => 'Pakan Finisher tidak cukup! Sisa stok: ' . $flock->stock_finisher_kg . ' kg.',
        ]);

        // FEATURE: Biological Limit & Fraud Prevention (EWS Pakan)
        $totalInputFeedKg = $validated['feed_pre_starter_kg'] + $validated['feed_starter_kg'] + $validated['feed_finisher_kg'];
        
        $ageInDays = max(1, \Carbon\Carbon::parse($flock->start_date)->diffInDays(\Carbon\Carbon::parse($validated['record_date'])) + 1);
        $maxGramPerBird = min(300, ($ageInDays * 7) + 30); // Batas toleransi sangat longgar untuk mencegah false positive
        
        $totalMortalitySoFar = $flock->dailyRecords()->whereDate('record_date', '<', $validated['record_date'])->sum('mortality');
        $totalCullingSoFar = $flock->dailyRecords()->whereDate('record_date', '<', $validated['record_date'])->sum('culling');
        $livePopulation = max(1, $flock->initial_population - $totalMortalitySoFar - $totalCullingSoFar);
        
        $maxFeedKgAllowed = ($livePopulation * $maxGramPerBird) / 1000;

        if ($totalInputFeedKg > $maxFeedKgAllowed) {
            $msg = sprintf("❌ Peringatan Anomali! Berdasarkan umur (%d hari) dan populasi hidup (%d ekor), kapasitas lambung maksimum ayam hanya %.2f Kg/hari. Input pakan Anda (%.2f Kg) menembus batas biologis (Indikasi Kecurangan).", $ageInDays, $livePopulation, $maxFeedKgAllowed, $totalInputFeedKg);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['feed_pre_starter_kg' => $msg])->withInput();
        }

        $record = $flock->dailyRecords()->create($validated);

        // AUTO-DEDUCT LOGIC: Automatically deduct feed stock when daily record is saved
        if ($validated['feed_pre_starter_kg'] > 0) {
            $flock->decrement('stock_pre_starter_kg', $validated['feed_pre_starter_kg']);
            FeedInventory::create(['transaction_date' => $validated['record_date'], 'feed_name' => 'Pakan Pre-Starter Kandang (Auto)', 'transaction_type' => 'out', 'quantity_kg' => $validated['feed_pre_starter_kg'], 'flock_id' => $flock->id, 'notes' => 'Konsumsi Harian Kandang: ' . $flock->name]);
        }
        if ($validated['feed_starter_kg'] > 0) {
            $flock->decrement('stock_starter_kg', $validated['feed_starter_kg']);
            FeedInventory::create(['transaction_date' => $validated['record_date'], 'feed_name' => 'Pakan Starter Kandang (Auto)', 'transaction_type' => 'out', 'quantity_kg' => $validated['feed_starter_kg'], 'flock_id' => $flock->id, 'notes' => 'Konsumsi Harian Kandang: ' . $flock->name]);
        }
        if ($validated['feed_finisher_kg'] > 0) {
            $flock->decrement('stock_finisher_kg', $validated['feed_finisher_kg']);
            FeedInventory::create(['transaction_date' => $validated['record_date'], 'feed_name' => 'Pakan Finisher Kandang (Auto)', 'transaction_type' => 'out', 'quantity_kg' => $validated['feed_finisher_kg'], 'flock_id' => $flock->id, 'notes' => 'Konsumsi Harian Kandang: ' . $flock->name]);
        }

        if ($request->expectsJson()) {
            // Ambil ulang semua data setelah ditambah, diurutkan Ascending (Lama -> Baru) untuk chart
            $allRecords = $flock->dailyRecords()->orderBy('record_date', 'asc')->get();
            
            $chartDates = $allRecords->pluck('record_date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M'))->toArray();
            $chartMortality = $allRecords->pluck('mortality')->toArray();
            // Gunakan accessor total konsumsi pakan untuk chart
            $chartFeed = $allRecords->map(fn($r) => $r->feed_consumed_kg)->toArray();

            // Calculate BEP Chart Data for AJAX
            $marketPrice = \App\Models\SystemSetting::where('key', 'global_market_price')->first()->value ?? 23000;
            $docPrice = \App\Models\SystemSetting::where('key', 'doc_price')->first()->value ?? 8000;
            $feedPrice = \App\Models\SystemSetting::where('key', 'feed_price')->first()->value ?? 9000;
            
            $bepLabels = [];
            $actualWeights = [];
            $bepTargets = [];
            $cumulativeFeed = 0;
            $dayCounter = 1;
            $currentPopulation = $flock->initial_population;

            foreach ($allRecords as $rec) {
                $bepLabels[] = "Hari " . $dayCounter;
                $actualWeights[] = $rec->average_weight_gram ?? (40 + ($dayCounter * 45)); 
                
                $cumulativeFeed += $rec->feed_consumed_kg;
                $currentPopulation -= ($rec->mortality + $rec->culling);
                if ($currentPopulation <= 0) $currentPopulation = 1;
                
                $bepTotalCost = ($flock->initial_population * $docPrice) + ($cumulativeFeed * $feedPrice);
                $bepTargetKg = $bepTotalCost / $marketPrice;
                $bepTargetGramPerBird = ($bepTargetKg / $currentPopulation) * 1000;
                
                $bepTargets[] = round($bepTargetGramPerBird, 2);
                $dayCounter++;
            }

            // Reverse untuk tabel (Baru -> Lama)
            $tableRecords = $allRecords->reverse()->map(function($r) use ($flock) {
                return [
                    'id' => $r->id,
                    'date_formatted' => \Carbon\Carbon::parse($r->record_date)->format('d M Y'),
                    'mortality' => number_format($r->mortality),
                    'culling' => number_format($r->culling),
                    'feed_consumed_kg' => number_format($r->feed_consumed_kg, 2),
                    'isHighMortality' => ($flock->initial_population > 0 && ($r->mortality / $flock->initial_population) > 0.005)
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Catatan harian berhasil disimpan & stok pakan berkurang otomatis!',
                'chart' => [
                    'dates' => $chartDates,
                    'mortality' => $chartMortality,
                    'feed' => $chartFeed,
                    'bep' => [
                        'labels' => $bepLabels,
                        'actualWeights' => $actualWeights,
                        'bepTargets' => $bepTargets
                    ]
                ],
                'records' => $tableRecords,
                'stock_pre_starter' => $flock->stock_pre_starter_kg,
                'stock_starter' => $flock->stock_starter_kg,
                'stock_finisher' => $flock->stock_finisher_kg,
            ]);
        }

        return redirect()->route('flocks.show', $flock)->with('success', 'Catatan harian berhasil disimpan & stok pakan berkurang otomatis!');
    }

    /**
     * Update the specified daily record in storage and adjust feed inventory.
     */
    public function update(Request $request, DailyRecord $dailyRecord)
    {
        $flock = $dailyRecord->flock;
        Gate::authorize('update', $flock);
        $this->ensureFlockIsActive($flock);

        // Maksimal pakan yang diizinkan adalah stok saat ini + pakan yang sebelumnya dipakai di record ini
        $maxPreStarter = $flock->stock_pre_starter_kg + $dailyRecord->feed_pre_starter_kg;
        $maxStarter = $flock->stock_starter_kg + $dailyRecord->feed_starter_kg;
        $maxFinisher = $flock->stock_finisher_kg + $dailyRecord->feed_finisher_kg;

        $validated = $request->validate([
            'record_date' => 'required|date',
            'mortality' => 'required|integer|min:0',
            'culling' => 'required|integer|min:0',
            'feed_pre_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxPreStarter],
            'feed_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxStarter],
            'feed_finisher_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxFinisher],
            'average_weight_gram' => 'nullable|integer|min:0',
        ]);

        // FEATURE: Biological Limit & Fraud Prevention (EWS Pakan)
        $totalInputFeedKg = $validated['feed_pre_starter_kg'] + $validated['feed_starter_kg'] + $validated['feed_finisher_kg'];
        
        $ageInDays = max(1, \Carbon\Carbon::parse($flock->start_date)->diffInDays(\Carbon\Carbon::parse($validated['record_date'])) + 1);
        $maxGramPerBird = min(300, ($ageInDays * 7) + 30);
        
        // Hitung total mati SEBELUM record ini
        $totalMortalitySoFar = $flock->dailyRecords()->where('id', '!=', $dailyRecord->id)->whereDate('record_date', '<=', $validated['record_date'])->sum('mortality');
        $totalCullingSoFar = $flock->dailyRecords()->where('id', '!=', $dailyRecord->id)->whereDate('record_date', '<=', $validated['record_date'])->sum('culling');
        $livePopulation = max(1, $flock->initial_population - $totalMortalitySoFar - $totalCullingSoFar);
        
        $maxFeedKgAllowed = ($livePopulation * $maxGramPerBird) / 1000;

        if ($totalInputFeedKg > $maxFeedKgAllowed) {
            $msg = sprintf("❌ Peringatan Anomali! Berdasarkan umur (%d hari) dan populasi hidup (%d ekor), kapasitas lambung maksimum ayam hanya %.2f Kg/hari. Input pakan Anda (%.2f Kg) menembus batas biologis (Indikasi Kecurangan).", $ageInDays, $livePopulation, $maxFeedKgAllowed, $totalInputFeedKg);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['feed_pre_starter_kg' => $msg])->withInput();
        }

        $oldPreStarter = $dailyRecord->feed_pre_starter_kg;
        $oldStarter = $dailyRecord->feed_starter_kg;
        $oldFinisher = $dailyRecord->feed_finisher_kg;
        
        $dailyRecord->update($validated);

        // Hitung selisih pakan (Reverting Stock Logic)
        $diffPreStarter = $validated['feed_pre_starter_kg'] - $oldPreStarter;
        $diffStarter = $validated['feed_starter_kg'] - $oldStarter;
        $diffFinisher = $validated['feed_finisher_kg'] - $oldFinisher;
        
        if ($diffPreStarter != 0) $flock->decrement('stock_pre_starter_kg', $diffPreStarter);
        if ($diffStarter != 0) $flock->decrement('stock_starter_kg', $diffStarter);
        if ($diffFinisher != 0) $flock->decrement('stock_finisher_kg', $diffFinisher);

        return redirect()->route('flocks.show', $flock)->with('success', 'Catatan harian berhasil diperbarui dan stok disesuaikan.');
    }

    /**
     * Remove the specified daily record from storage and revert feed inventory.
     */
    public function destroy(DailyRecord $dailyRecord)
    {
        $flock = $dailyRecord->flock;
        Gate::authorize('update', $flock);
        $this->ensureFlockIsActive($flock);

        $feedPreStarter = $dailyRecord->feed_pre_starter_kg;
        $feedStarter = $dailyRecord->feed_starter_kg;
        $feedFinisher = $dailyRecord->feed_finisher_kg;
        
        $dailyRecord->delete();

        // Kembalikan stok pakan yang sebelumnya terpakai
        if ($feedPreStarter > 0) $flock->increment('stock_pre_starter_kg', $feedPreStarter);
        if ($feedStarter > 0) $flock->increment('stock_starter_kg', $feedStarter);
        if ($feedFinisher > 0) $flock->increment('stock_finisher_kg', $feedFinisher);

        return redirect()->route('flocks.show', $flock)->with('success', 'Catatan harian berhasil dihapus dan stok pakan telah dikembalikan.');
    }
}
