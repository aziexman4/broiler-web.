@props([
    'actionUrl',
    'resetUrl',
    'printUrl',
    'printLabel' => 'Cetak Laporan A4',
    'activeFilterType' => 'all',
    'periodLabel' => '-',
    'recordCount' => 0,
])

@php
    $initialMode = in_array(request('filter_mode'), ['month', 'range'], true)
        ? request('filter_mode')
        : ((request('start_date') || request('end_date')) ? 'range' : 'month');
    $isFiltered = $activeFilterType !== 'all';
@endphp

<div x-data="{ mode: '{{ $initialMode }}' }" class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-5">
    <form method="GET" action="{{ $actionUrl }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <input type="hidden" name="filter_mode" :value="mode">

        <div class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
            <!-- Mode Tabs -->
            <div>
                <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                    Mode Filter Periode
                </span>
                <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-900 p-1 border border-slate-200 dark:border-slate-700">
                    <button type="button"
                        @click="mode = 'month'"
                        :class="mode === 'month'
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-2 text-xs font-semibold rounded-lg transition-all">
                        📅 Per Bulan
                    </button>
                    <button type="button"
                        @click="mode = 'range'"
                        :class="mode === 'range'
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-2 text-xs font-semibold rounded-lg transition-all">
                        🗓️ Rentang Tanggal
                    </button>
                </div>
            </div>

            <!-- Tab 1: Per Bulan -->
            <div x-show="mode === 'month'" class="flex-1 max-w-xs">
                <label for="filter_month" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Pilih Bulan &amp; Tahun
                </label>
                <input type="month"
                    id="filter_month"
                    name="month"
                    value="{{ request('month') }}"
                    :disabled="mode !== 'month'"
                    class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Tab 2: Rentang Tanggal -->
            <div x-show="mode === 'range'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 flex-1 max-w-lg" style="display: none;">
                <div>
                    <label for="filter_start_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Dari Tanggal
                    </label>
                    <input type="date"
                        id="filter_start_date"
                        name="start_date"
                        value="{{ request('start_date') }}"
                        :disabled="mode !== 'range'"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="filter_end_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Sampai Tanggal
                    </label>
                    <input type="date"
                        id="filter_end_date"
                        name="end_date"
                        value="{{ request('end_date') }}"
                        :disabled="mode !== 'range'"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Terapkan Filter
            </button>

            <a href="{{ $resetUrl }}" class="inline-flex items-center px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-600 transition-colors">
                Reset
            </a>

            <a href="{{ $printUrl }}"
               target="_blank"
               class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                {{ $printLabel }}
            </a>
        </div>
    </form>

    @if ($isFiltered)
        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                🔎 Menampilkan Periode: {{ $periodLabel }}
            </span>
            <span class="text-slate-500 dark:text-slate-400">
                Ditemukan <strong class="text-slate-700 dark:text-slate-200">{{ $recordCount }}</strong> transaksi yang sesuai
            </span>
        </div>
    @endif
</div>
