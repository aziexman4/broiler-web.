<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CashFlow extends Model
{
    use HasFactory;

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
        $requestedMode = $request->input('filter_mode');
        $month = self::parseValidMonth(trim((string) $request->input('month', '')));
        $startDate = self::parseValidDate(trim((string) $request->input('start_date', '')));
        $endDate = self::parseValidDate(trim((string) $request->input('end_date', '')));

        $hasRangeInput = $startDate !== null || $endDate !== null;
        $useRange = ($requestedMode === 'range' || ($requestedMode !== 'month' && $hasRangeInput)) && $hasRangeInput;
        $useMonth = !$useRange && ($requestedMode === 'month' || $month !== null) && $month !== null;

        $query = static::query();

        if ($useMonth) {
            [$year, $monthNumber] = array_map('intval', explode('-', $month));
            $query->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $monthNumber);
            $activeFilterType = 'month';
        } elseif ($useRange) {
            if ($startDate !== null) {
                $query->whereDate('transaction_date', '>=', $startDate);
            }
            if ($endDate !== null) {
                $query->whereDate('transaction_date', '<=', $endDate);
            }
            $activeFilterType = 'range';
        } else {
            $activeFilterType = 'all';
        }

        $records = (clone $query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalIn = (int) (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (int) (clone $query)->where('type', 'out')->sum('amount');
        $balance = $totalIn - $totalOut;

        return [
            'records' => $records,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $balance,
            'activeFilterType' => $activeFilterType,
            'periodLabel' => self::buildPeriodLabel($activeFilterType, $month, $startDate, $endDate, $records),
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Build a human-readable Indonesian period label for UI badges and A4 reports.
     */
    private static function buildPeriodLabel(
        string $activeFilterType,
        ?string $month,
        ?string $startDate,
        ?string $endDate,
        Collection $records
    ): string {
        if ($activeFilterType === 'month' && $month !== null) {
            return Carbon::createFromFormat('!Y-m', $month)
                ->locale('id')
                ->translatedFormat('F Y');
        }

        if ($activeFilterType === 'range') {
            $startLabel = $startDate
                ? Carbon::createFromFormat('!Y-m-d', $startDate)->locale('id')->translatedFormat('d M Y')
                : null;
            $endLabel = $endDate
                ? Carbon::createFromFormat('!Y-m-d', $endDate)->locale('id')->translatedFormat('d M Y')
                : null;

            if ($startLabel && $endLabel) {
                return "{$startLabel} — {$endLabel}";
            }

            if ($startLabel) {
                return "Mulai {$startLabel}";
            }

            if ($endLabel) {
                return "S.d. {$endLabel}";
            }
        }

        if ($records->isEmpty()) {
            return '-';
        }

        $earliest = Carbon::parse($records->last()->transaction_date)->locale('id')->translatedFormat('d M Y');
        $latest = Carbon::parse($records->first()->transaction_date)->locale('id')->translatedFormat('d M Y');

        return "{$earliest} — {$latest}";
    }

    /**
     * Validate a YYYY-MM month string.
     */
    private static function parseValidMonth(string $value): ?string
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1 ? $value : null;
    }

    /**
     * Validate a Y-m-d calendar date string.
     */
    private static function parseValidDate(string $value): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }
}
