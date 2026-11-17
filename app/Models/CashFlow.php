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
    public static function getFilteredSummary(Request $request): array
    {
        $filter = self::applyPeriodFilter(static::query(), $request);
        $query = $filter['query'];

        $records = (clone $query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalIn = (int) (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (int) (clone $query)->where('type', 'out')->sum('amount');
        $balance = $totalIn - $totalOut;

        $periodLabel = self::buildPeriodLabel(
            $filter['activeFilterType'],
            $filter['month'],
            $filter['startDate'],
            $filter['endDate'],
            $records
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
