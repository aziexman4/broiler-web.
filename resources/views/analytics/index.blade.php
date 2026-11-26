<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h2 class="font-bold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analitik &amp; Benchmarking
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Indeks Penampilan (IP), Efisiensi FCR, &amp; Profitabilitas Antar Unit Kandang</p>
            </div>
            <div class="flex items-center gap-2">
                <a id="analytics-print-link"
                   href="{{ route('reports.analytics') }}"
                   data-base-url="{{ route('reports.analytics') }}"
                   class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-md text-sm font-semibold text-gray-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Pratinjau &amp; Cetak PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div x-data="analyticsCoopFilter()" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Coop Unit Filter & Sort Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Filter Unit Kandang</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200" x-text="filteredRecords.length">{{ count($analyticsData) }}</span> siklus tutup buku
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                        @click="selectCoop('all')"
                        :class="selectedCoopId === 'all'
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                            : 'bg-slate-100 dark:bg-slate-700/70 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        class="px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition">
                        Semua Kandang ({{ count($analyticsData) }})
                    </button>
                    @foreach($coops ?? [] as $coop)
                        @php
                            $coopCycleCount = collect($analyticsData)->where('coop_id', $coop->id)->count();
                        @endphp
                        <button type="button"
                            @click="selectCoop('{{ $coop->id }}')"
                            :class="String(selectedCoopId) === '{{ $coop->id }}'
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                : 'bg-slate-100 dark:bg-slate-700/70 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition">
                            {{ $coop->name }} ({{ $coopCycleCount }})
                        </button>
                    @endforeach
                    <div class="flex items-center gap-2 pl-0 sm:pl-2 sm:border-l border-slate-200 dark:border-slate-700">
                        <label for="analytics-sort" class="text-xs font-semibold text-slate-500 dark:text-slate-400">Urut:</label>
                        <select id="analytics-sort" x-model="sortBy" @change="applyFilterAndSort()"
                            class="rounded-xl border-slate-200 dark:border-slate-600 text-xs font-semibold py-1.5 pl-3 pr-8 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="closed_asc">Kronologis (Terlama)</option>
                            <option value="closed_desc">Panen Terbaru</option>
                            <option value="ip_desc">IP Tertinggi</option>
                            <option value="ip_asc">IP Terendah</option>
                            <option value="fcr_asc">FCR Terbaik</option>
                            <option value="profit_desc">Net Profit Tertinggi</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Global KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- IP Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="p-5 relative z-10 flex items-center">
                        <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div class="ml-4 min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata IP</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                @php $avgIpServer = $farmAverages['ip'] ?? 0; @endphp
                                <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white" x-text="formatDecimal(avgIp, 2)">
                                    {{ number_format($avgIpServer, 2) }}
                                </h3>
                                <span x-show="avgIp >= 400" {{ $avgIpServer < 400 ? 'x-cloak' : '' }} class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Sangat Baik</span>
                                <span x-show="avgIp >= 350 && avgIp < 400" {{ ($avgIpServer < 350 || $avgIpServer >= 400) ? 'x-cloak' : '' }} class="px-2 py-0.5 rounded text-[11px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Baik</span>
                                <span x-show="avgIp >= 300 && avgIp < 350" {{ ($avgIpServer < 300 || $avgIpServer >= 350) ? 'x-cloak' : '' }} class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">Cukup / Standar</span>
                                <span x-show="avgIp < 300" {{ $avgIpServer >= 300 ? 'x-cloak' : '' }} class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Kurang</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FCR Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="p-5 relative z-10 flex items-center">
                        <div class="flex-shrink-0 p-3 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata FCR</p>
                            <h3 class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white" x-text="formatDecimal(avgFcr, 3)">{{ number_format($farmAverages['fcr'], 3) }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Livability Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="p-5 relative z-10 flex items-center">
                        <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kehidupan</p>
                            <h3 class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white"><span x-text="formatDecimal(avgLivability, 2)">{{ number_format($farmAverages['livability'], 2) }}</span>%</h3>
                        </div>
                    </div>
                </div>

                <!-- Total Net Profit Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="p-5 relative z-10 flex items-center">
                        <div class="flex-shrink-0 p-3 bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4 min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Net Profit</p>
                            <h3 class="mt-1 text-xl font-extrabold truncate"
                                :class="totalNetProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                x-text="formatRupiah(totalNetProfit)">
                                Rp {{ number_format($farmAverages['total_net_profit'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coop Benchmarking Leaderboard -->
            @if(!empty($coopBenchmarks))
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Perbandingan Performa Antar Unit Kandang</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Urutan berdasarkan rata-rata IP terbaik. Klik kartu unit untuk memfilter grafik &amp; rincian.</p>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Target Bonus: IP &ge; 300</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($coopBenchmarks as $idx => $unit)
                            <button type="button"
                                @click="toggleCoop('{{ $unit['coop_id'] }}')"
                                :class="String(selectedCoopId) === '{{ $unit['coop_id'] }}'
                                    ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30'
                                    : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 hover:border-indigo-300 dark:hover:border-indigo-700'"
                                class="text-left rounded-2xl border p-4 transition-all">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $idx === 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                                #{{ $idx + 1 }}
                                            </span>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $unit['coop_name'] }}</span>
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            PJ: {{ $unit['manager_name'] }} &bull; {{ $unit['cycles_count'] }} siklus
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[11px] uppercase font-semibold text-slate-400">Rata IP</div>
                                        <div class="text-lg font-extrabold {{ $unit['avg_ip'] >= 300 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ number_format($unit['avg_ip'], 2) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-200/80 dark:border-slate-700 text-xs">
                                    <div>
                                        <div class="text-slate-400">FCR</div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($unit['avg_fcr'], 3) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-slate-400">Livability</div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($unit['avg_livability'], 2) }}%</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-slate-400">Net Profit</div>
                                        <div class="font-bold {{ $unit['total_net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            Rp {{ number_format($unit['total_net_profit'] / 1000000, 1, ',', '.') }}Jt
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Dual Visual Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Heatmap Chart Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">IP Benchmarking Heatmap</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Garis putus-putus menandai target bonus minimum IP &ge; 300</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> &ge; 300 (Target Bonus)</span>
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 mr-1.5"></span> 250 - 299 (Evaluasi)</span>
                            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-1.5"></span> &lt; 250 (Perlu Fokus)</span>
                        </div>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="ipHeatmapChart"></canvas>
                    </div>
                </div>

                <!-- FCR & Weight Trend Chart -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tren Efisiensi FCR &amp; Bobot Panen</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Semakin rendah FCR dan stabil bobot panen, efisiensi pakan semakin baik</p>
                        </div>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="fcrWeightChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Detail Performa per Siklus Kandang</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Klik nama siklus untuk meninjau catatan harian, grafik BEP, dan rincian panen</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kandang &amp; Siklus</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ras / Pekerja</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bobot Rata-rata</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">FCR</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Livability</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Profit</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP &amp; Tier Bonus</th>
                            </tr>
                        </thead>
                        <tbody x-ref="tableBody" class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            @forelse($analyticsData as $data)
                            <tr data-flock-id="{{ $data['flock_id'] }}"
                                x-show="selectedCoopId === 'all' || String(selectedCoopId) === '{{ $data['coop_id'] }}'"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('flocks.show', $data['flock_id']) }}" class="font-bold text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $data['flock_name'] }} &rarr;
                                    </a>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $data['coop_name'] }} &bull; Panen: {{ $data['closed_at'] }} ({{ $data['harvest_age'] }} Hr)
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-200">{{ $data['breed'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $data['worker_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 dark:text-slate-200">
                                    {{ number_format($data['avg_weight_kg'], 2) }} kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold {{ $data['fcr'] < 1.6 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-slate-200' }}">
                                    {{ number_format($data['fcr'], 3) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 dark:text-slate-200">
                                    {{ number_format($data['livability'], 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $data['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    Rp {{ number_format($data['net_profit'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $ipColor = 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                                        if ($data['ip'] >= 300) {
                                            $ipColor = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
                                        } elseif ($data['ip'] >= 250) {
                                            $ipColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
                                        } elseif ($data['ip'] > 0) {
                                            $ipColor = 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400';
                                        }
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full {{ $ipColor }}">
                                        {{ number_format($data['ip'], 2) }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $data['bonus_tier_label'] }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data kandang yang sudah panen (Closed).
                                </td>
                            </tr>
                            @endforelse
                            @if(count($analyticsData) > 0)
                            <tr x-ref="emptyFilteredRow" x-show="filteredRecords.length === 0" x-cloak>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada siklus panen selesai pada unit kandang ini.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Injection -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function analyticsCoopFilter() {
            const initialRecords = @json($analyticsData);
            return {
                selectedCoopId: 'all',
                sortBy: 'closed_asc',
                records: initialRecords,
                get filteredRecords() {
                    if (this.selectedCoopId === 'all') {
                        return this.records;
                    }
                    return this.records.filter(r => String(r.coop_id) === String(this.selectedCoopId));
                },
                get sortedFilteredRecords() {
                    const list = [...this.filteredRecords];
                    switch (this.sortBy) {
                        case 'closed_desc':
                            return list.sort((a, b) => Number(b.closed_timestamp) - Number(a.closed_timestamp));
                        case 'ip_desc':
                            return list.sort((a, b) => Number(b.ip) - Number(a.ip));
                        case 'ip_asc':
                            return list.sort((a, b) => Number(a.ip) - Number(b.ip));
                        case 'fcr_asc':
                            return list.sort((a, b) => Number(a.fcr) - Number(b.fcr));
                        case 'profit_desc':
                            return list.sort((a, b) => Number(b.net_profit) - Number(a.net_profit));
                        case 'closed_asc':
                        default:
                            return list.sort((a, b) => Number(a.closed_timestamp) - Number(b.closed_timestamp));
                    }
                },
                get validRecords() {
                    return this.filteredRecords.filter(r => Number(r.ip) > 0);
                },
                get avgIp() {
                    const list = this.validRecords;
                    if (!list.length) return 0;
                    return list.reduce((sum, r) => sum + Number(r.ip), 0) / list.length;
                },
                get avgFcr() {
                    const list = this.validRecords;
                    if (!list.length) return 0;
                    return list.reduce((sum, r) => sum + Number(r.fcr), 0) / list.length;
                },
                get avgLivability() {
                    const list = this.validRecords;
                    if (!list.length) return 0;
                    return list.reduce((sum, r) => sum + Number(r.livability), 0) / list.length;
                },
                get totalNetProfit() {
                    return this.filteredRecords.reduce((sum, r) => sum + Number(r.net_profit || 0), 0);
                },
                formatDecimal(val, decimals) {
                    return Number(val || 0).toLocaleString('en-US', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                },
                formatRupiah(val) {
                    const num = Math.round(Number(val || 0));
                    const prefix = num < 0 ? '-Rp ' : 'Rp ';
                    return prefix + Math.abs(num).toLocaleString('id-ID');
                },
                toggleCoop(coopId) {
                    const nextId = String(this.selectedCoopId) === String(coopId) ? 'all' : String(coopId);
                    this.selectCoop(nextId);
                },
                selectCoop(coopId) {
                    this.selectedCoopId = coopId;
                    const printLink = document.getElementById('analytics-print-link');
                    if (printLink) {
                        const base = printLink.dataset.baseUrl;
                        printLink.href = this.selectedCoopId === 'all'
                            ? base
                            : `${base}?coop_id=${encodeURIComponent(this.selectedCoopId)}`;
                    }
                    this.applyFilterAndSort();
                },
                applyFilterAndSort() {
                    const sorted = this.sortedFilteredRecords;
                    if (typeof window.updateAnalyticsCharts === 'function') {
                        window.updateAnalyticsCharts(sorted);
                    }
                    this.syncTableOrder(sorted);
                },
                syncTableOrder(sorted) {
                    const tbody = this.$refs.tableBody;
                    if (!tbody) return;
                    const rowMap = new Map();
                    tbody.querySelectorAll('tr[data-flock-id]').forEach((tr) => {
                        rowMap.set(String(tr.dataset.flockId), tr);
                    });
                    sorted.forEach((rec) => {
                        const tr = rowMap.get(String(rec.flock_id));
                        if (tr) {
                            tbody.appendChild(tr);
                        }
                    });
                    if (this.$refs.emptyFilteredRow) {
                        tbody.appendChild(this.$refs.emptyFilteredRow);
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const rawData = @json($analyticsData);
            let currentRecords = [...rawData];

            function ipColor(ip, alpha) {
                if (ip >= 300) return alpha < 1 ? `rgba(16, 185, 129, ${alpha})` : 'rgb(16, 185, 129)';
                if (ip >= 250) return alpha < 1 ? `rgba(250, 204, 21, ${alpha})` : 'rgb(250, 204, 21)';
                return alpha < 1 ? `rgba(244, 63, 94, ${alpha})` : 'rgb(244, 63, 94)';
            }

            const ipTargetLinePlugin = {
                id: 'ipTargetLine',
                afterDatasetsDraw(chart) {
                    const yScale = chart.scales?.y;
                    const area = chart.chartArea;
                    if (!yScale || !area) return;

                    const yPos = yScale.getPixelForValue(300);
                    if (yPos < area.top || yPos > area.bottom) return;

                    const ctx = chart.ctx;
                    const isDark = window.BroilerTheme?.isDark?.() ?? false;
                    ctx.save();
                    ctx.beginPath();
                    ctx.setLineDash([6, 4]);
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = isDark ? 'rgba(52, 211, 153, 0.9)' : 'rgba(5, 150, 105, 0.85)';
                    ctx.moveTo(area.left, yPos);
                    ctx.lineTo(area.right, yPos);
                    ctx.stroke();

                    ctx.setLineDash([]);
                    ctx.fillStyle = isDark ? '#6ee7b7' : '#047857';
                    ctx.font = 'bold 11px Figtree, sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText('Target Bonus IP 300', area.right - 6, yPos - 6);
                    ctx.restore();
                }
            };

            const ipCtx = document.getElementById('ipHeatmapChart').getContext('2d');
            const ipConfig = {
                type: 'bar',
                data: {
                    labels: currentRecords.map(d => d.flock_name),
                    datasets: [{
                        label: 'Indeks Penampilan (IP)',
                        data: currentRecords.map(d => d.ip),
                        backgroundColor: currentRecords.map(d => ipColor(d.ip, 0.8)),
                        borderColor: currentRecords.map(d => ipColor(d.ip, 1)),
                        borderWidth: 1,
                        borderRadius: 6,
                        barThickness: 32,
                    }]
                },
                plugins: [ipTargetLinePlugin],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            padding: 12,
                            boxPadding: 4,
                            callbacks: {
                                label: function(context) {
                                    const record = currentRecords[context.dataIndex];
                                    if (!record) return [];
                                    return [
                                        `Unit: ${record.coop_name}`,
                                        `IP: ${record.ip} (${record.bonus_tier_label})`,
                                        `FCR: ${record.fcr}`,
                                        `Livability: ${record.livability}%`,
                                        `Worker: ${record.worker_name}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 420,
                            grid: {},
                            ticks: {}
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 40,
                                minRotation: 40
                            }
                        }
                    }
                }
            };

            const ipChart = window.BroilerTheme
                ? window.BroilerTheme.createChart(ipCtx, ipConfig)
                : new Chart(ipCtx, ipConfig);

            const fcrCtx = document.getElementById('fcrWeightChart').getContext('2d');
            const fcrConfig = {
                data: {
                    labels: currentRecords.map(d => d.flock_name),
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Bobot Rata-rata (Kg)',
                            data: currentRecords.map(d => d.avg_weight_kg),
                            backgroundColor: 'rgba(99, 102, 241, 0.72)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 1,
                            borderRadius: 6,
                            barThickness: 28,
                            yAxisID: 'y',
                        },
                        {
                            type: 'line',
                            label: 'FCR',
                            data: currentRecords.map(d => d.fcr),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            padding: 12,
                            boxPadding: 4,
                            callbacks: {
                                afterBody: function(contexts) {
                                    const first = contexts[0];
                                    const record = first ? currentRecords[first.dataIndex] : null;
                                    if (!record) return [];
                                    return [`Unit: ${record.coop_name}`, `Panen: ${record.closed_at}`];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 2.6,
                            title: { display: true, text: 'Bobot (Kg)' },
                            grid: {},
                            ticks: {}
                        },
                        y1: {
                            position: 'right',
                            suggestedMin: 1.2,
                            suggestedMax: 2.0,
                            title: { display: true, text: 'FCR' },
                            grid: { drawOnChartArea: false },
                            ticks: {}
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 40,
                                minRotation: 40
                            }
                        }
                    }
                }
            };

            const fcrChart = window.BroilerTheme
                ? window.BroilerTheme.createChart(fcrCtx, fcrConfig)
                : new Chart(fcrCtx, fcrConfig);

            window.updateAnalyticsCharts = function(filtered) {
                currentRecords = [...filtered];
                const labels = currentRecords.map(d => d.flock_name);

                if (ipChart) {
                    ipChart.data.labels = labels;
                    ipChart.data.datasets[0].data = currentRecords.map(d => d.ip);
                    ipChart.data.datasets[0].backgroundColor = currentRecords.map(d => ipColor(d.ip, 0.8));
                    ipChart.data.datasets[0].borderColor = currentRecords.map(d => ipColor(d.ip, 1));
                    ipChart.update('none');
                }

                if (fcrChart) {
                    fcrChart.data.labels = labels;
                    fcrChart.data.datasets[0].data = currentRecords.map(d => d.avg_weight_kg);
                    fcrChart.data.datasets[1].data = currentRecords.map(d => d.fcr);
                    fcrChart.update('none');
                }
            };
        });
    </script>
</x-app-layout>
