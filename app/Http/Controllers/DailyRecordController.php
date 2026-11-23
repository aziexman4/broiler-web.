<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Models\FeedInventory;
use App\Models\Flock;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DailyRecordController extends Controller
{
    private const FEED_PHASE_LABELS = [
        'pre_starter' => ['stock_col' => 'stock_pre_starter_kg', 'input_col' => 'feed_pre_starter_kg', 'label' => 'Pre-Starter'],
        'starter' => ['stock_col' => 'stock_starter_kg', 'input_col' => 'feed_starter_kg', 'label' => 'Starter'],
        'finisher' => ['stock_col' => 'stock_finisher_kg', 'input_col' => 'feed_finisher_kg', 'label' => 'Finisher'],
    ];

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

    /**
     * Validate duplicate date, mortality upper bound, and biological feed capacity.
     */
    private function validateRecordIntegrity(Flock $flock, array $validated, ?DailyRecord $existingRecord = null): void
    {
        $duplicateDateExists = $flock->dailyRecords()
            ->whereDate('record_date', $validated['record_date'])
            ->when($existingRecord, fn ($q) => $q->where('id', '!=', $existingRecord->id))
            ->exists();

        if ($duplicateDateExists) {
            throw ValidationException::withMessages([
                'record_date' => 'Catatan harian untuk tanggal ' . Carbon::parse($validated['record_date'])->format('d M Y') . ' pada kandang ini sudah ada.',
            ]);
        }

        $otherDepletion = (int) $flock->dailyRecords()
            ->when($existingRecord, fn ($q) => $q->where('id', '!=', $existingRecord->id))
            ->sum(DB::raw('mortality + culling'));

        $remainingAlive = max(0, $flock->initial_population - $otherDepletion);
        $requestedDepletion = (int) $validated['mortality'] + (int) $validated['culling'];

        if ($requestedDepletion > $remainingAlive) {
            throw ValidationException::withMessages([
                'mortality' => "Jumlah mati + afkir ({$requestedDepletion} ekor) melebihi sisa populasi hidup ({$remainingAlive} ekor).",
            ]);
        }

        // Biological Limit & Fraud Prevention (EWS Pakan)
        $totalInputFeedKg = (float) $validated['feed_pre_starter_kg']
            + (float) $validated['feed_starter_kg']
            + (float) $validated['feed_finisher_kg'];

        $ageInDays = max(1, Carbon::parse($flock->start_date)->diffInDays(Carbon::parse($validated['record_date'])) + 1);
        $maxGramPerBird = min(300, ($ageInDays * 7) + 30);

        $priorDepletion = (int) $flock->dailyRecords()
            ->when($existingRecord, fn ($q) => $q->where('id', '!=', $existingRecord->id))
            ->whereDate('record_date', $existingRecord ? '<=' : '<', $validated['record_date'])
            ->sum(DB::raw('mortality + culling'));
        $livePopulationForFeed = max(1, $flock->initial_population - $priorDepletion);
        $maxFeedKgAllowed = ($livePopulationForFeed * $maxGramPerBird) / 1000;

        if ($totalInputFeedKg > $maxFeedKgAllowed) {
            $msg = sprintf(
                '❌ Peringatan Anomali! Berdasarkan umur (%d hari) dan populasi hidup (%d ekor), kapasitas lambung maksimum ayam hanya %.2f Kg/hari. Input pakan Anda (%.2f Kg) menembus batas biologis (Indikasi Kecurangan).',
                $ageInDays,
                $livePopulationForFeed,
                $maxFeedKgAllowed,
                $totalInputFeedKg
            );

            throw ValidationException::withMessages(['feed_pre_starter_kg' => $msg]);
        }
    }

    /**
     * Create automatic FeedInventory 'out' entries for non-zero daily feed usage.
     */
    private function createAutoFeedInventories(Flock $flock, string $recordDate, array $feedAmounts): void
    {
        foreach (self::FEED_PHASE_LABELS as $meta) {
            $amount = (float) ($feedAmounts[$meta['input_col']] ?? 0);
            if ($amount > 0) {
                FeedInventory::create([
                    'transaction_date' => $recordDate,
                    'feed_name' => "Pakan {$meta['label']} Kandang (Auto)",
                    'transaction_type' => 'out',
                    'quantity_kg' => $amount,
                    'flock_id' => $flock->id,
                    'notes' => 'Konsumsi Harian Kandang: ' . $flock->name,
                ]);
            }
        }
    }

    /**
     * Remove matching automatic FeedInventory 'out' entries when a DailyRecord is updated or deleted.
     */
    private function removeAutoFeedInventories(Flock $flock, DailyRecord $record): void
    {
        $recordDate = Carbon::parse($record->record_date)->toDateString();

        foreach (self::FEED_PHASE_LABELS as $meta) {
            $amount = (float) $record->{$meta['input_col']};
            if ($amount > 0) {
                FeedInventory::where('flock_id', $flock->id)
                    ->whereDate('transaction_date', $recordDate)
                    ->where('transaction_type', 'out')
                    ->where('feed_name', "Pakan {$meta['label']} Kandang (Auto)")
                    ->where('quantity_kg', $amount)
                    ->limit(1)
                    ->delete();
            }
        }
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

        $startDate = Carbon::parse($flock->start_date)->format('Y-m-d');

        try {
            DB::transaction(function () use ($request, &$flock, $startDate) {
                $flock = Flock::lockForUpdate()->findOrFail($flock->id);
                $this->ensureFlockIsActive($flock);

                $validated = $request->validate([
                    'record_date' => ['required', 'date', "after_or_equal:{$startDate}"],
                    'mortality' => ['required', 'integer', 'min:0'],
                    'culling' => ['required', 'integer', 'min:0'],
                    'feed_pre_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_pre_starter_kg],
                    'feed_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_starter_kg],
                    'feed_finisher_kg' => ['required', 'numeric', 'min:0', 'max:' . $flock->stock_finisher_kg],
                    'average_weight_gram' => ['nullable', 'integer', 'min:0'],
                ], [
                    'record_date.after_or_equal' => "Tanggal catatan tidak boleh sebelum tanggal chick-in ({$startDate}).",
                    'feed_pre_starter_kg.max' => 'Pakan Pre-Starter tidak cukup! Sisa stok: ' . $flock->stock_pre_starter_kg . ' kg.',
                    'feed_starter_kg.max' => 'Pakan Starter tidak cukup! Sisa stok: ' . $flock->stock_starter_kg . ' kg.',
                    'feed_finisher_kg.max' => 'Pakan Finisher tidak cukup! Sisa stok: ' . $flock->stock_finisher_kg . ' kg.',
                ]);

                $this->validateRecordIntegrity($flock, $validated);

                $flock->dailyRecords()->create($validated);

                foreach (self::FEED_PHASE_LABELS as $meta) {
                    $amount = (float) $validated[$meta['input_col']];
                    if ($amount > 0) {
                        $flock->decrement($meta['stock_col'], $amount);
                    }
                }

                $this->createAutoFeedInventories($flock, $validated['record_date'], $validated);
            });
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                $firstError = collect($e->errors())->flatten()->first() ?? $e->getMessage();
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $flock->refresh();

        if ($request->expectsJson()) {
            $allRecords = $flock->dailyRecords()->orderBy('record_date', 'asc')->get();

            $chartDates = $allRecords->pluck('record_date')->map(fn ($date) => Carbon::parse($date)->format('d M'))->toArray();
            $chartMortality = $allRecords->pluck('mortality')->toArray();
            $chartFeed = $allRecords->map(fn ($r) => $r->feed_consumed_kg)->toArray();

            $marketPrice = (float) (SystemSetting::where('key', 'global_market_price')->value('value') ?? 23000);
            $docPrice = (float) (SystemSetting::where('key', 'doc_price')->value('value') ?? 8000);
            $feedPrice = (float) (SystemSetting::where('key', 'feed_price')->value('value') ?? 9000);

            $bepLabels = [];
            $actualWeights = [];
            $bepTargets = [];
            $cumulativeFeed = 0;
            $dayCounter = 1;
            $currentPopulation = $flock->initial_population;

            foreach ($allRecords as $rec) {
                $bepLabels[] = 'Hari ' . $dayCounter;
                $actualWeights[] = $rec->average_weight_gram ?? (40 + ($dayCounter * 45));

                $cumulativeFeed += $rec->feed_consumed_kg;
                $currentPopulation -= ($rec->mortality + $rec->culling);
                if ($currentPopulation <= 0) {
                    $currentPopulation = 1;
                }

                $bepTotalCost = ($flock->initial_population * $docPrice) + ($cumulativeFeed * $feedPrice);
                $bepTargetKg = $bepTotalCost / ($marketPrice > 0 ? $marketPrice : 1);
                $bepTargetGramPerBird = ($bepTargetKg / $currentPopulation) * 1000;

                $bepTargets[] = round($bepTargetGramPerBird, 2);
                $dayCounter++;
            }

            $tableRecords = $allRecords->reverse()->map(function ($r) use ($flock) {
                return [
                    'id' => $r->id,
                    'date_formatted' => Carbon::parse($r->record_date)->format('d M Y'),
                    'mortality' => number_format($r->mortality),
                    'culling' => number_format($r->culling),
                    'feed_consumed_kg' => number_format($r->feed_consumed_kg, 2),
                    'isHighMortality' => ($flock->initial_population > 0 && ($r->mortality / $flock->initial_population) > 0.005),
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
                        'bepTargets' => $bepTargets,
                    ],
                ],
                'records' => $tableRecords,
                'current_stock' => $flock->current_feed_stock_kg,
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

        $startDate = Carbon::parse($flock->start_date)->format('Y-m-d');

        DB::transaction(function () use ($request, $flock, $dailyRecord, $startDate) {
            $lockedFlock = Flock::lockForUpdate()->findOrFail($flock->id);
            $this->ensureFlockIsActive($lockedFlock);

            $maxPreStarter = $lockedFlock->stock_pre_starter_kg + $dailyRecord->feed_pre_starter_kg;
            $maxStarter = $lockedFlock->stock_starter_kg + $dailyRecord->feed_starter_kg;
            $maxFinisher = $lockedFlock->stock_finisher_kg + $dailyRecord->feed_finisher_kg;

            $validated = $request->validate([
                'record_date' => ['required', 'date', "after_or_equal:{$startDate}"],
                'mortality' => ['required', 'integer', 'min:0'],
                'culling' => ['required', 'integer', 'min:0'],
                'feed_pre_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxPreStarter],
                'feed_starter_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxStarter],
                'feed_finisher_kg' => ['required', 'numeric', 'min:0', 'max:' . $maxFinisher],
                'average_weight_gram' => ['nullable', 'integer', 'min:0'],
            ], [
                'record_date.after_or_equal' => "Tanggal catatan tidak boleh sebelum tanggal chick-in ({$startDate}).",
            ]);

            $this->validateRecordIntegrity($lockedFlock, $validated, $dailyRecord);

            $oldFeeds = [
                'feed_pre_starter_kg' => (float) $dailyRecord->feed_pre_starter_kg,
                'feed_starter_kg' => (float) $dailyRecord->feed_starter_kg,
                'feed_finisher_kg' => (float) $dailyRecord->feed_finisher_kg,
            ];

            $this->removeAutoFeedInventories($lockedFlock, $dailyRecord);

            $dailyRecord->update($validated);

            foreach (self::FEED_PHASE_LABELS as $meta) {
                $diff = (float) $validated[$meta['input_col']] - $oldFeeds[$meta['input_col']];
                if ($diff != 0.0) {
                    $lockedFlock->decrement($meta['stock_col'], $diff);
                }
            }

            $this->createAutoFeedInventories($lockedFlock, $validated['record_date'], $validated);
        });

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

        DB::transaction(function () use ($flock, $dailyRecord) {
            $lockedFlock = Flock::lockForUpdate()->findOrFail($flock->id);
            $this->ensureFlockIsActive($lockedFlock);

            $this->removeAutoFeedInventories($lockedFlock, $dailyRecord);

            foreach (self::FEED_PHASE_LABELS as $meta) {
                $amount = (float) $dailyRecord->{$meta['input_col']};
                if ($amount > 0) {
                    $lockedFlock->increment($meta['stock_col'], $amount);
                }
            }

            $dailyRecord->delete();
        });

        return redirect()->route('flocks.show', $flock)->with('success', 'Catatan harian berhasil dihapus dan stok pakan telah dikembalikan.');
    }
}
