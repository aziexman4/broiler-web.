<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

trait FiltersByPeriod
{
    /**
     * Apply mutually exclusive month or date-range filters to a query and return metadata.
     */
    protected static function applyPeriodFilter(
        Builder $query,
        Request $request,
        string $dateColumn = 'transaction_date'
    ): array {
        $requestedMode = $request->input('filter_mode');
        $month = self::parseValidMonth(trim((string) $request->input('month', '')));
        $startDate = self::parseValidDate(trim((string) $request->input('start_date', '')));
        $endDate = self::parseValidDate(trim((string) $request->input('end_date', '')));

        $hasRangeInput = $startDate !== null || $endDate !== null;
        $useRange = ($requestedMode === 'range' || ($requestedMode !== 'month' && $hasRangeInput)) && $hasRangeInput;
        $useMonth = !$useRange && ($requestedMode === 'month' || $month !== null) && $month !== null;

        if ($useMonth) {
            [$year, $monthNumber] = array_map('intval', explode('-', $month));
            $query->whereYear($dateColumn, $year)
                ->whereMonth($dateColumn, $monthNumber);
            $activeFilterType = 'month';
        } elseif ($useRange) {
            if ($startDate !== null) {
                $query->whereDate($dateColumn, '>=', $startDate);
            }
            if ($endDate !== null) {
                $query->whereDate($dateColumn, '<=', $endDate);
            }
            $activeFilterType = 'range';
        } else {
            $activeFilterType = 'all';
        }

        return [
            'query' => $query,
            'activeFilterType' => $activeFilterType,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Build a human-readable Indonesian period label for UI badges and A4 print headers.
     */
    protected static function buildPeriodLabel(
        string $activeFilterType,
        ?string $month,
        ?string $startDate,
        ?string $endDate,
        Collection $records,
        string $dateColumn = 'transaction_date'
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

        $earliest = Carbon::parse($records->last()->{$dateColumn})->locale('id')->translatedFormat('d M Y');
        $latest = Carbon::parse($records->first()->{$dateColumn})->locale('id')->translatedFormat('d M Y');

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
