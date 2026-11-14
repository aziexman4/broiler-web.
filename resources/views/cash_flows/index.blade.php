<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Manajemen Arus Kas Operasional (Cash Flow)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencatatan pengeluaran operasional umum dan pemasukan non-panen peternakan</p>
            </div>
        </div>
    </x-slot>

    @php
        $initialMode = in_array(request('filter_mode'), ['month', 'range'], true)
            ? request('filter_mode')
            : ((request('start_date') || request('end_date')) ? 'range' : 'month');
        $isFiltered = isset($activeFilterType) && $activeFilterType !== 'all';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success & Error Alert Notifications -->
            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 shadow-md">
                    <div class="text-sm font-bold mb-1">Terdapat kesalahan input:</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter Bar & Cetak Laporan A4 (Alpine.js Mutually Exclusive Modes) -->
            <div x-data="{ mode: '{{ $initialMode }}' }" class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-5">
                <form method="GET" action="{{ route('cash-flows.index') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
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

                        <a href="{{ route('cash-flows.index') }}" class="inline-flex items-center px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-600 transition-colors">
                            Reset
                        </a>

                        <a href="{{ route('reports.cash-flow', request()->only(['filter_mode', 'month', 'start_date', 'end_date'])) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Laporan A4
                        </a>
                    </div>
                </form>

                @if ($isFiltered)
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            🔎 Menampilkan Periode: {{ $periodLabel }}
                        </span>
                        <span class="text-slate-500 dark:text-slate-400">
                            Ditemukan <strong class="text-slate-700 dark:text-slate-200">{{ $records->count() }}</strong> transaksi yang sesuai
                        </span>
                    </div>
                @endif
            </div>

            <!-- Top Section: 3 KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Total Pemasukan -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pemasukan Kas</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                                Rp {{ number_format($total_in, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>{{ $isFiltered ? 'Akumulasi kas masuk periode terpilih' : 'Akumulasi dana kas masuk' }}</span>
                    </div>
                </div>

                <!-- Card 2: Total Pengeluaran -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 dark:bg-rose-900/20 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pengeluaran Kas</p>
                            <h3 class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                                Rp {{ number_format($total_out, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>{{ $isFiltered ? 'Akumulasi kas keluar periode terpilih' : 'Akumulasi dana kas keluar' }}</span>
                    </div>
                </div>

                <!-- Card 3: Saldo Kas -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isFiltered ? 'Saldo Kas Periode' : 'Saldo Kas Saat Ini' }}
                            </p>
                            <h3 class="text-2xl font-extrabold {{ $balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }} mt-2">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>Selisih akumulasi pemasukan &amp; pengeluaran</span>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Form Input Kas -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6">
                <div class="pb-4 mb-6 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Tambah Catatan Kas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Catat transaksi keuangan operasional seperti sekam, listrik, atau penjualan pupuk</p>
                </div>

                <form action="{{ route('cash-flows.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Tanggal -->
                        <div>
                            <label for="transaction_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Tanggal Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Keterangan -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Keterangan Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="misal: Pembelian Sekam Alas, Listrik Kandang, Jual Pupuk" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Jenis Transaksi -->
                        <div>
                            <label for="type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Jenis Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <select id="type" name="type" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Pemasukan (+ Kas In)</option>
                                <option value="out" {{ old('type', 'out') === 'out' ? 'selected' : '' }}>Pengeluaran (- Kas Out)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nominal Rupiah -->
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Nominal Rupiah (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" placeholder="500000" min="1" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="flex items-end justify-end">
                            <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md transition-colors">
                                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Simpan Transaksi Kas
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bottom Section: Table Riwayat Transaksi Kas -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Riwayat Transaksi Kas</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daftar mutasi pengeluaran dan pemasukan operasional</p>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Periode: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $periodLabel ?? '-' }}</span>
                    </div>
                </div>

                <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative border-t border-slate-200 dark:border-slate-700">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Tanggal</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Keterangan Transaksi</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Jenis</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5 text-right">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700">
                            @forelse ($records as $record)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($record->transaction_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                        {{ $record->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($record->type === 'in')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                + Pemasukan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                - Pengeluaran
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold whitespace-nowrap {{ $record->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $record->type === 'in' ? '+' : '-' }} Rp {{ number_format($record->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="font-medium text-slate-500 dark:text-slate-400">
                                            {{ $isFiltered ? 'Tidak Ada Transaksi pada Periode Ini' : 'Belum Ada Transaksi Kas' }}
                                        </p>
                                        <p class="text-xs mt-1">
                                            {{ $isFiltered ? 'Coba pilih bulan atau rentang tanggal lain, atau klik Reset untuk melihat seluruh transaksi.' : 'Gunakan formulir di atas untuk mencatat pemasukan atau pengeluaran kas.' }}
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
