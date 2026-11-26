<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="font-bold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analitik & Benchmarking
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Indeks Penampilan (IP) & Pemetaan Performa Siklus Peternakan</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <button onclick="window.print()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-md text-sm font-semibold text-gray-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Export PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div x-data="analyticsCoopFilter()" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Coop Unit Filter Bar -->
            <div class="print:hidden bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Filter Unit Kandang</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200" x-text="filteredRecords.length">{{ count($analyticsData) }}</span> siklus tutup buku</div>
                </div>
                <div class="flex flex-wrap gap-2">
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
                </div>
            </div>

            <!-- Global KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- IP Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-transparent dark:from-indigo-900/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="p-6 relative z-10">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata IP Peternakan</p>
                                <h3 class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">
                                    <span x-text="formatDecimal(avgIp, 2)">{{ number_format($farmAverages['ip'], 2) }}</span>
                                    <span x-show="avgIp >= 300" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 ml-2 align-middle">Excellent</span>
                                    <span x-show="avgIp < 300" x-cloak class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 ml-2 align-middle">Good</span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FCR Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent dark:from-emerald-900/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="p-6 relative z-10">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata FCR</p>
                                <h3 class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white" x-text="formatDecimal(avgFcr, 3)">{{ number_format($farmAverages['fcr'], 3) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Livability Card -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative group transition hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent dark:from-blue-900/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="p-6 relative z-10">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata Tingkat Kehidupan</p>
                                <h3 class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white"><span x-text="formatDecimal(avgLivability, 2)">{{ number_format($farmAverages['livability'], 2) }}</span>%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Heatmap Chart Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">IP Benchmarking Heatmap</h3>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span> > 300 (Excellent)</span>
                        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400 mr-1.5"></span> 250 - 300 (Good)</span>
                        <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-rose-500 mr-1.5"></span> < 250 (Needs Focus)</span>
                    </div>
                </div>
                <div class="relative h-96 w-full">
                    <canvas id="ipHeatmapChart"></canvas>
                </div>
            </div>

            <!-- Detailed Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Detail Performa per Siklus Kandang</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kandang & Siklus</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ras / Pekerja</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bobot Rata-rata</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">FCR</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Livability</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP (Indeks Penampilan)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                            @forelse($analyticsData as $data)
                            <tr x-show="selectedCoopId === 'all' || String(selectedCoopId) === '{{ $data['coop_id'] }}'" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $data['flock_name'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $data['coop_name'] }} &bull; Umur Panen: {{ $data['harvest_age'] }} Hari</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-200">{{ $data['breed'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $data['worker_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 dark:text-slate-200">
                                    {{ $data['avg_weight_kg'] }} kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold {{ $data['fcr'] < 1.6 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-slate-200' }}">
                                    {{ $data['fcr'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 dark:text-slate-200">
                                    {{ $data['livability'] }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $ipColor = 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                                        if($data['ip'] >= 300) {
                                            $ipColor = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
                                        } elseif($data['ip'] >= 250) {
                                            $ipColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
                                        } elseif($data['ip'] > 0) {
                                            $ipColor = 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400';
                                        }
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full {{ $ipColor }}">
                                        {{ $data['ip'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data kandang yang sudah panen (Closed).
                                </td>
                            </tr>
                            @endforelse
                            @if(count($analyticsData) > 0)
                            <tr x-show="filteredRecords.length === 0" x-cloak>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
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
                records: initialRecords,
                get filteredRecords() {
                    if (this.selectedCoopId === 'all') {
                        return this.records;
                    }
                    return this.records.filter(r => String(r.coop_id) === String(this.selectedCoopId));
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
                formatDecimal(val, decimals) {
                    return Number(val || 0).toLocaleString('en-US', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                },
                selectCoop(coopId) {
                    this.selectedCoopId = coopId;
                    if (typeof window.updateAnalyticsIpChart === 'function') {
                        window.updateAnalyticsIpChart(this.filteredRecords);
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

            const ctx = document.getElementById('ipHeatmapChart').getContext('2d');
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
                        barThickness: 40,
                    }]
                },
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
                                        `IP Score: ${record.ip}`,
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
                            grid: {},
                            ticks: {}
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            };

            const ipChart = window.BroilerTheme
                ? window.BroilerTheme.createChart(ctx, ipConfig)
                : new Chart(ctx, ipConfig);

            window.updateAnalyticsIpChart = function(filtered) {
                currentRecords = [...filtered];
                if (!ipChart) return;
                ipChart.data.labels = currentRecords.map(d => d.flock_name);
                ipChart.data.datasets[0].data = currentRecords.map(d => d.ip);
                ipChart.data.datasets[0].backgroundColor = currentRecords.map(d => ipColor(d.ip, 0.8));
                ipChart.data.datasets[0].borderColor = currentRecords.map(d => ipColor(d.ip, 1));
                ipChart.update('none');
            };
        });
    </script>
</x-app-layout>
