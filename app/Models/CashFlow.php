<?php

namespace App\Models;

use App\Traits\FiltersByPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CashFlow extends Model
{
    use HasFactory, FiltersByPeriod;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cash_flows';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_date',
        'description',
        'type',
        'amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'integer',
        ];
    }

    /**
     * Filter cash flow records and compute totals from mutually exclusive period filters.
     */
    public static function getFilteredSummary(Request $request, ?int $perPage = null): array
    {
        $filter = self::applyPeriodFilter(static::query(), $request);
        $query = $filter['query'];

        $orderedQuery = (clone $query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        $totalIn = (int) (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (int) (clone $query)->where('type', 'out')->sum('amount');
        $balance = $totalIn - $totalOut;

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
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $balance,
            'activeFilterType' => $filter['activeFilterType'],
            'periodLabel' => $periodLabel,
            'month' => $filter['month'],
            'startDate' => $filter['startDate'],
            'endDate' => $filter['endDate'],
        ];
    }
}
