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
    public static function getFilteredSummary(Request $request): array
    {
        $filter = self::applyPeriodFilter(static::query()->with('flock'), $request);
        $query = $filter['query'];

        $records = (clone $query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalIn = (float) (clone $query)->where('transaction_type', 'in')->sum('quantity_kg');
        $totalOut = (float) (clone $query)->where('transaction_type', 'out')->sum('quantity_kg');
        $currentStock = $totalIn - $totalOut;
        $totalValue = (float) ((clone $query)->where('transaction_type', 'in')
            ->selectRaw('SUM(quantity_kg * price_per_kg) as total')
            ->value('total') ?? 0);

        $periodLabel = self::buildPeriodLabel(
            $filter['activeFilterType'],
            $filter['month'],
            $filter['startDate'],
            $filter['endDate'],
            $records
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
