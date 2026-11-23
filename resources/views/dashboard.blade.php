<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    @can('manage-enterprise')
                        Ringkasan Eksekutif &amp; Finansial (Owner Dashboard)
                    @else
                        Dashboard Operasional Pekerja Kandang
                    @endcan
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Selamat datang, <span class="font-semibold text-slate-700 dark:text-slate-200">{{ Auth::user()->name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('flocks.index') }}" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold uppercase tracking-wider shadow-md transition">
                    Daftar Kandang &rarr;
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center space-x-3 shadow-md">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @can('manage-enterprise')
                <!-- Financial & Enterprise KPIs (Owner Only) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Kas -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Saldo Kas</div>
                        <div class="mt-2 text-2xl font-extrabold {{ ($financialMetrics['cash_balance'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($financialMetrics['cash_balance'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Masuk: Rp {{ number_format($financialMetrics['total_cash_in'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Estimasi Pendapatan -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estimasi Pendapatan</div>
                        <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                            Rp {{ number_format($financialMetrics['estimated_revenue'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Proyeksi {{ number_format($financialMetrics['active_flocks_count'] ?? 0) }} siklus aktif ({{ number_format($financialMetrics['total_active_population'] ?? 0) }} ekor)
                        </div>
                    </div>

                    <!-- Laba Bersih -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Laba Bersih Panen</div>
                        <div class="mt-2 text-2xl font-extrabold {{ ($financialMetrics['net_profit'] ?? 0) >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($financialMetrics['net_profit'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Estimasi berjalan: Rp {{ number_format($financialMetrics['estimated_active_net_profit'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Ringkasan Operasional Kandang -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status Operasional</div>
                        <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                            {{ number_format($financialMetrics['active_flocks_count'] ?? 0) }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Aktif</span>
                        </div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ number_format($financialMetrics['closed_flocks_count'] ?? 0) }} tutup buku &bull; {{ number_format($financialMetrics['pending_feed_requests_count'] ?? 0) }} permintaan pakan pending
                        </div>
                    </div>
                </div>

                <!-- Owner Active Cycles Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Siklus Kandang Aktif &amp; Proyeksi Pendapatan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pantau performa populasi hidup dan estimasi pendapatan per kandang</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('cash-flows.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition">Arus Kas</a>
                            <a href="{{ route('financial.history') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition">Riwayat Finansial</a>
                            <a href="{{ route('payrolls.index') }}" class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/70 transition">Payroll</a>
                        </div>
                    </div>

                    @if($activeFlockSummaries->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            Belum ada siklus kandang berstatus aktif saat ini.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/60 text-xs uppercase text-slate-500 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Kandang &amp; Unit</th>
                                        <th class="px-6 py-3 font-semibold">Pekerja</th>
                                        <th class="px-6 py-3 font-semibold">Umur</th>
                                        <th class="px-6 py-3 font-semibold">Populasi Hidup</th>
                                        <th class="px-6 py-3 font-semibold text-right">Estimasi Pendapatan</th>
                                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach($activeFlockSummaries as $summary)
                                        @php $flock = $summary['flock']; @endphp
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $flock->name }}</div>
                                                @if($flock->coop)
                                                    <div class="text-xs text-indigo-600 dark:text-indigo-400">📍 {{ $flock->coop->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $flock->worker->name ?? 'Belum Ditugaskan' }}</td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $summary['harvest_age'] }} hari</td>
                                            <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-200">{{ number_format($summary['live_population']) }} ekor</td>
                                            <td class="px-6 py-4 text-right font-bold text-indigo-600 dark:text-indigo-400">
                                                Rp {{ number_format($summary['estimated_revenue'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-800 dark:bg-slate-700 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition">
                                                    Detail &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endcan

            @cannot('manage-enterprise')
                <!-- Operational & Self-Service Summary (Worker View — Zero Enterprise Financials) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Active Flocks Operational KPIs + Quick Actions -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kandang Aktif Saya</div>
                                <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($workerStats['active_flocks_count'] ?? 0) }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Siklus</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Sedang berjalan</div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Populasi Hidup Dikelola</div>
                                <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ number_format($workerStats['total_active_population'] ?? 0) }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Ekor</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Akumulasi kandang aktif</div>
                            </div>

                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Riwayat Siklus Selesai</div>
                                <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($workerStats['closed_flocks_count'] ?? 0) }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Panen</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($workerStats['total_payslips_count'] ?? 0) }} slip gaji terbit</div>
                            </div>
                        </div>

                        <!-- Active Flocks List with Quick Buttons -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Siklus Kandang Aktif Anda</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Akses cepat menuju detail kandang dan input recording harian</p>
                                </div>
                            </div>

                            @if($activeFlockSummaries->isEmpty())
                                <div class="p-10 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    Belum ada siklus kandang berstatus <span class="font-semibold">Active</span> yang ditugaskan kepada Anda saat ini.
                                </div>
                            @else
                                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach($activeFlockSummaries as $summary)
                                        @php $flock = $summary['flock']; @endphp
                                        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors">
                                            <div class="space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-bold text-slate-900 dark:text-white">{{ $flock->name }}</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                        Active &bull; Hari ke-{{ $summary['harvest_age'] }}
                                                    </span>
                                                    @if($summary['recorded_today'])
                                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                                            ✓ Tercatat Hari Ini
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap gap-x-3 gap-y-1">
                                                    @if($flock->coop)
                                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">📍 {{ $flock->coop->name }}</span>
                                                    @endif
                                                    <span>Populasi Hidup: <strong class="text-slate-700 dark:text-slate-200">{{ number_format($summary['live_population']) }} ekor</strong></span>
                                                    <span>Daya Hidup: <strong class="text-slate-700 dark:text-slate-200">{{ $summary['livability'] }}%</strong></span>
                                                    <span>Total Pakan: <strong class="text-slate-700 dark:text-slate-200">{{ number_format($summary['total_feed_kg'], 2) }} kg</strong></span>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                                <a href="{{ route('daily_records.create', $flock) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                                    + Input Recording Harian
                                                </a>
                                                <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-600 transition">
                                                    Detail Kandang &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Worker's Latest Salary / Bonus Summary Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Gaji &amp; Bonus Terakhir</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Ringkasan slip gaji terbaru milik Anda</p>
                                </div>
                                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                            </div>

                            @if($latestPayroll)
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">Total Diterima</div>
                                        <div class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">
                                            Rp {{ number_format($latestPayroll->total_paid, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Dibayarkan: {{ \Carbon\Carbon::parse($latestPayroll->payment_date)->format('d M Y') }}
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700 space-y-2 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Siklus Kandang</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $latestPayroll->flock->name ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Gaji Pokok</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($latestPayroll->base_salary, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Bonus Performa (IP)</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">+ Rp {{ number_format($latestPayroll->bonus_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 pt-2">
                                        <a href="{{ route('payrolls.payslip', $latestPayroll) }}" target="_blank" class="w-full inline-flex justify-center items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                            🖨️ Cetak Slip Gaji Terakhir
                                        </a>
                                        <a href="{{ route('worker.payslips.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition">
                                            Lihat Semua Riwayat Slip Gaji &rarr;
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="py-6 text-center space-y-3">
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Belum ada slip gaji yang diterbitkan</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Riwayat gaji pokok dan bonus IP Anda akan muncul di sini setelah siklus tutup buku.</p>
                                    <a href="{{ route('worker.payslips.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-semibold border border-indigo-200 dark:border-indigo-800 transition">
                                        Buka Halaman Slip Gaji Saya
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endcannot

        </div>
    </div>
</x-app-layout>
