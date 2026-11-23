<?php

namespace App\Models;

use App\Traits\FiltersByPeriod;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class FeedInventory extends Model
{
    use HasFactory, LogsActivity, FiltersByPeriod;

    protected $table = 'feed_inventories';

    protected $fillable = [
        'transaction_date',
        'feed_name',
        'transaction_type',
        'quantity_kg',
        'price_per_kg',
        'notes',
        'flock_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity_kg' => 'decimal:2',
            'price_per_kg' => 'integer',
        ];
    }

    /**
     * Get the flock associated with the feed inventory record.
     */
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    /**
     * Filter feed inventory mutations and compute stock/purchase totals for the period.
     */
    public static function getFilteredSummary(Request $request, ?int $perPage = null): array
    {
        $filter = self::applyPeriodFilter(static::query()->with('flock'), $request);
        $query = $filter['query'];

        $orderedQuery = (clone $query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        $totalIn = (float) (clone $query)->where('transaction_type', 'in')->sum('quantity_kg');
        $totalOut = (float) (clone $query)->where('transaction_type', 'out')->sum('quantity_kg');
        $currentStock = $totalIn - $totalOut;
        $totalValue = (float) ((clone $query)->where('transaction_type', 'in')
            ->selectRaw('SUM(quantity_kg * price_per_kg) as total')
            ->value('total') ?? 0);

        if ($perPage !== null) {
            $maxDate = (clone $query)->max('transaction_date');
            $minDate = (clone $query)->min('transaction_date');
            $boundRecords = ($maxDate && $minDate)
                ? new \Illuminate\Database\Eloquent\Collection([
                    new static(['transaction_date' => $maxDate]),
                    new static(['transaction_date' => $minDate]),
                ])
                : new \Illuminate\Database\Eloquent\Collection();

            $records = $orderedQuery->paginate($perPage)->withQueryString();
            $periodSource = $boundRecords;
        } else {
            $records = $orderedQuery->get();
            $periodSource = $records;
        }

        $periodLabel = self::buildPeriodLabel(
            $filter['activeFilterType'],
            $filter['month'],
            $filter['startDate'],
            $filter['endDate'],
            $periodSource
        );

        return [
            'records' => $records,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'currentStock' => $currentStock,
            'totalValue' => $totalValue,
            'activeFilterType' => $filter['activeFilterType'],
            'periodLabel' => $periodLabel,
            'month' => $filter['month'],
            'startDate' => $filter['startDate'],
            'endDate' => $filter['endDate'],
        ];
    }
}
