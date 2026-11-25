<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-white leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Riwayat Finansial & Panen Global
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ringkasan performa lifetime dan akumulasi laba seluruh siklus kandang</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- UI Section 1: KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Akumulasi Laba Bersih -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md dark:shadow-none border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden transition-colors">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/30 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Akumulasi Laba Bersih</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                                Rp {{ number_format($total_net_profit, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>Total Keuntungan bersih semua panen</span>
                    </div>
                </div>

                <!-- Card 2: Total Pendapatan Kotor -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md dark:shadow-none border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden transition-colors">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 dark:bg-blue-900/30 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pendapatan Kotor</p>
                            <h3 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">
                                Rp {{ number_format($total_revenue, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>Bruto hasil penjualan panen</span>
                    </div>
                </div>

                <!-- Card 3: Total Tonase -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md dark:shadow-none border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden transition-colors">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-50 dark:bg-purple-900/30 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Tonase (Kg)</p>
                            <h3 class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-2">
                                {{ number_format($total_weight, 2, ',', '.') }} <span class="text-base font-medium">Kg</span>
                            </h3>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5 5 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5 5 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>Akumulasi bobot ayam terpanen</span>
                    </div>
                </div>
            </div>

            <!-- UI Section 2: Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md dark:shadow-none border border-slate-200/80 dark:border-slate-700 p-6 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Tren Laba Bersih per Siklus</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Grafik perkembangan keuntungan bersih di setiap periode panen</p>
                    </div>
                </div>
                <div class="relative w-full h-80">
                    <canvas id="netProfitChart"></canvas>
                </div>
            </div>

            <!-- UI Section 3: Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md dark:shadow-none border border-slate-200/80 dark:border-slate-700 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Riwayat Seluruh Panen</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daftar rincian transaksi panen yang tersimpan</p>
                    </div>
                </div>

                <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5">Tanggal Panen</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5">Nama Kandang</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5">Harga Ayam / Kg</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5">Harga Pakan / Kg</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5 text-right">Beban Gaji (Payroll)</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-700 px-6 py-3.5 text-right">Laba Bersih Final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @forelse ($harvests as $harvest)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                            {{ $harvest->flock->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 dark:text-slate-300">
                                        Rp {{ number_format($harvest->chicken_price_per_kg, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 dark:text-slate-300">
                                        Rp {{ number_format($harvest->feed_price_per_kg, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($harvest->labor_cost ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold {{ ($harvest->true_net_profit ?? $harvest->net_profit) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        Rp {{ number_format($harvest->true_net_profit ?? $harvest->net_profit, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-medium text-slate-500 dark:text-slate-400">Belum Ada Data Panen</p>
                                        <p class="text-xs mt-1">Data riwayat panen dan finansial akan muncul di sini setelah panen dicatat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js CDN & Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('netProfitChart').getContext('2d');
            const labels = @json($chartLabels);
            const data = @json($chartData);

            const profitConfig = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Laba Bersih (Rp)',
                        data: data,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(99, 102, 241, 0.95)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {}
                        },
                        tooltip: {
                            padding: 12,
                            boxPadding: 4,
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw || 0;
                                    return ' Laba Bersih: Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                }
                            },
                            grid: {}
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {}
                        }
                    }
                }
            };

            window.BroilerTheme
                ? window.BroilerTheme.createChart(ctx, profitConfig)
                : new Chart(ctx, profitConfig);
        });
    </script>
</x-app-layout>
