<?php

namespace App\Models;

use App\Traits\FiltersByPeriod;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CashFlow extends Model
{
    use HasFactory, FiltersByPeriod, LogsActivity;

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

        $periodStartDate = null;
        if ($filter['activeFilterType'] === 'month' && $filter['month'] !== null) {
            $periodStartDate = \Carbon\Carbon::createFromFormat('!Y-m', $filter['month'])
                ->startOfMonth()
                ->toDateString();
        } elseif ($filter['activeFilterType'] === 'range' && $filter['startDate'] !== null) {
            $periodStartDate = $filter['startDate'];
        }

        $openingBalance = 0;
        if ($periodStartDate !== null) {
            $prevIn = (int) static::query()
                ->whereDate('transaction_date', '<', $periodStartDate)
                ->where('type', 'in')
                ->sum('amount');
            $prevOut = (int) static::query()
                ->whereDate('transaction_date', '<', $periodStartDate)
                ->where('type', 'out')
                ->sum('amount');
            $openingBalance = $prevIn - $prevOut;
        }
        $closingBalance = $openingBalance + $balance;

        $running = 0;
        $runningBalanceMap = [];
        foreach (static::query()->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get(['id', 'type', 'amount']) as $row) {
            $running += $row->type === 'in' ? (int) $row->amount : -(int) $row->amount;
            $runningBalanceMap[$row->id] = $running;
        }
        $currentRealBalance = $running;

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
            $records->getCollection()->transform(function ($item) use ($runningBalanceMap) {
                $item->running_balance = $runningBalanceMap[$item->id] ?? 0;
                return $item;
            });
            $periodSource = $boundRecords;
        } else {
            $records = $orderedQuery->get()->each(function ($item) use ($runningBalanceMap) {
                $item->running_balance = $runningBalanceMap[$item->id] ?? 0;
            });
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
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'current_real_balance' => $currentRealBalance,
            'activeFilterType' => $filter['activeFilterType'],
            'periodLabel' => $periodLabel,
            'month' => $filter['month'],
            'startDate' => $filter['startDate'],
            'endDate' => $filter['endDate'],
        ];
    }
}
