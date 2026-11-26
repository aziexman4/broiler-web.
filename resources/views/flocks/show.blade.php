<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                    {{ $flock->name }}
                    <span class="ms-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $flock->status === 'active' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200' }}">
                        {{ ucfirst($flock->status) }}
                    </span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    @if($flock->coop)
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">📍 {{ $flock->coop->name }}</span> |
                    @endif
                    Ras: {{ $flock->breed }} | Chick-in: {{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }} (Umur: {{ $harvestAge }} hari)
                </p>
                @if($flock->status === 'active')
                    @php $progressPercent = min(100, ($harvestAge / 35) * 100); @endphp
                    <div class="mt-2 w-full md:w-72 bg-gray-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden" title="Progres Umur Ayam (Target Standar: 35 Hari)">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-500 h-1.5 rounded-full" style="width: {{ $progressPercent }}%"></div>
                    </div>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('flocks.index') }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                    &larr; Kembali
                </a>
                @if(Auth::user()->isOwner())
                <div x-data="{ reportOpen: false }" class="relative">
                    <button @click="reportOpen = !reportOpen" type="button" class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        📄 Cetak Laporan ▾
                    </button>
                    <div x-show="reportOpen" @click.away="reportOpen = false"
                         x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 py-2" style="display:none">
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Laporan Tersedia</div>
                        <a href="{{ route('flocks.export-pdf', $flock) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <span>🏆</span> Laporan Akhir Panen (P&L)
                        </a>
                        <a href="{{ route('reports.daily-record', $flock) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <span>📋</span> Buku Recording Harian
                        </a>
                        <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                        <a href="{{ route('flocks.export-csv', $flock) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <span>📊</span> Download CSV (Excel)
                        </a>
                    </div>
                </div>
                @else
                <a href="{{ route('reports.daily-record', $flock) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    📋 Cetak Recording Harian
                </a>
                @endif

                @if($flock->status === 'active')
                <button @click="$dispatch('open-slide-over')" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    + Catat Harian
                </button>
                @else
                <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    🔒 Siklus Tutup Buku
                </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="dailyRecordSlideOver()" @open-slide-over.window="openPanel = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center space-x-3 shadow-md">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center space-x-3 shadow-md">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Metric Summary KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Populasi Hidup -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Populasi Hidup</div>
                    <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($livePopulation) }} <span class="text-xs font-normal text-slate-500">ekor</span></div>
                    <div class="mt-1 text-xs text-slate-400">Awal: {{ number_format($flock->initial_population) }}</div>
                </div>

                <!-- Total Pakan -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Total Pakan</div>
                    <div class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($totalFeedConsumed, 2) }} <span class="text-xs font-normal text-slate-500">kg</span></div>
                    <div class="mt-1 text-xs text-slate-400">Konsumsi pakan</div>
                </div>

                <!-- FCR -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300" title="FCR (Feed Conversion Ratio): Rasio konversi pakan menjadi daging. Semakin kecil nilainya (mendekati 1.0) semakin bagus.">
                    <div class="flex items-center gap-1">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">FCR (Feed Conv.)</div>
                        <svg class="w-3.5 h-3.5 text-slate-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $fcr > 0 ? $fcr : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-400">Target <= 1.50</div>
                </div>

                <!-- IP (Index Performansi) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300" title="IP (Indeks Performansi): Nilai gabungan dari FCR, Kelangsungan Hidup, dan Umur. IP > 300 dianggap sangat baik.">
                    <div class="flex items-center gap-1">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">IP (Index Performansi)</div>
                        <svg class="w-3.5 h-3.5 text-slate-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $ip > 0 ? $ip : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-400">Daya hidup: {{ $livability }}%</div>
                </div>

                <!-- Total Kematian & Afkir -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Kematian / Afkir</div>
                    <div class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalMortality) }} / <span class="text-orange-500">{{ number_format($totalCulling) }}</span></div>
                    <div class="mt-1 text-xs text-slate-400">Mortality / Culling</div>
                </div>

                <!-- Total Hasil Panen -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Total Terjual</div>
                    <div class="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($totalHarvestSold) }} <span class="text-xs font-normal text-slate-500">ekor</span></div>
                    <div class="mt-1 text-xs text-slate-400">{{ number_format($totalHarvestWeight, 2) }} kg</div>
                </div>
            </div>

                @can('manage-enterprise')
                @if($flock->status === 'active')
                <!-- Individual P&L Forecasting Widget (Owner Only) -->
                <div class="bg-gradient-to-br from-indigo-900 to-blue-900 text-white p-6 rounded-2xl shadow-md border border-indigo-800">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Proyeksi Finansial (Real-time)</span>
                            <h3 class="text-xl font-bold mt-2">Estimasi Laba/Rugi Berjalan</h3>
                            <p class="text-xs text-indigo-200 mt-1">Berdasarkan {{ number_format($livePopulation) }} ekor hidup & bobot rata-rata terakhir</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2 border-t border-indigo-800/50">
                        <div>
                            <div class="text-xs text-indigo-300 uppercase font-semibold">Total Biaya (Modal & Pakan)</div>
                            <div class="text-xl font-extrabold text-rose-400 mt-1">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-indigo-300 uppercase font-semibold">Potensi Pendapatan</div>
                            <div class="text-xl font-extrabold text-blue-400 mt-1">Rp {{ number_format($estimatedRevenue, 0, ',', '.') }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-indigo-300 uppercase font-semibold">Proyeksi Laba Bersih</div>
                            <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                                Rp {{ number_format($netProfit, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endcan

                <!-- Live BEP Target Tracker Chart -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                            {{ $flock->status === 'active' ? 'Live BEP Target Tracker (Forecasting)' : 'Historical BEP Tracker (Retrospeksi)' }}
                        </h3>
                        @if($flock->status === 'active')
                            <span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 text-xs font-bold px-3 py-1 rounded-full animate-pulse">Live Forecasting</span>
                        @else
                            <span class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold px-3 py-1 rounded-full border border-slate-200 dark:border-slate-600">🔒 Terkunci (Selesai Panen)</span>
                        @endif
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="bepChart"></canvas>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Grafik ini membandingkan <strong>Pertumbuhan Aktual (Biru)</strong> dengan <strong>Target Break-Even Point (Merah)</strong>.
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Jika garis biru berada di atas garis merah, peternakan dalam posisi <span class="text-emerald-600 dark:text-emerald-400 font-bold">POTENSI PROFIT</span> pada hari tersebut.
                        </p>
                    </div>
                </div>

                <!-- FEATURE 5: Closed Flock Financial Summary Card (Owner Only) -->
                @can('manage-enterprise')
                @if($latestHarvest)
                <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-6 rounded-2xl shadow-md border border-indigo-900">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Financial Summary (Kandang Selesai)</span>
                            <h3 class="text-xl font-bold mt-2">Laba Bersih Panen</h3>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400">Tanggal Panen</div>
                            <div class="text-sm font-semibold text-slate-200">{{ \Carbon\Carbon::parse($latestHarvest->harvest_date)->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-2 border-t border-slate-800">
                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold">Pendapatan Kotor (Gross)</div>
                            <div class="text-xl font-extrabold text-emerald-400 mt-1">Rp {{ number_format($latestHarvest->gross_revenue, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">@ Rp {{ number_format($latestHarvest->chicken_price_per_kg, 0, ',', '.') }}/kg</div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold">Biaya Total Pakan</div>
                            <div class="text-xl font-extrabold text-rose-400 mt-1">Rp {{ number_format($latestHarvest->total_feed_cost, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">@ Rp {{ number_format($latestHarvest->feed_price_per_kg, 0, ',', '.') }}/kg</div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold">Laba Bersih (Net Profit)</div>
                            <div class="text-2xl font-black {{ $latestHarvest->net_profit >= 0 ? 'text-green-400' : 'text-red-400' }} mt-1">
                                Rp {{ number_format($latestHarvest->net_profit, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">Gross Revenue - Biaya Pakan - Modal DOC</div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 uppercase font-semibold">Pembeli</div>
                            <div class="text-lg font-bold text-slate-100 mt-1">{{ $latestHarvest->buyer_name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ number_format($latestHarvest->total_chickens_sold) }} ekor ({{ number_format($latestHarvest->total_weight_kg, 2) }} kg)</div>
                        </div>
                    </div>
                </div>
                @endif
                @endcan


                <!-- Visual Monitoring Charts (Chart.js) -->
                <div x-data="{ isChartLoading: true }" x-init="setTimeout(() => isChartLoading = false, 800)" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Daily Mortality Chart -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-gray-200 mb-4 flex items-center">
                            📈 Grafik Kematian Harian (Mortality)
                        </h3>
                        
                        <!-- Skeleton Loader -->
                        <div x-show="isChartLoading" x-transition.opacity.duration.500ms class="absolute inset-0 z-10 bg-white dark:bg-slate-800 flex flex-col justify-center rounded-2xl p-6 mt-10">
                            <div class="w-full h-48 bg-slate-100 dark:bg-slate-700 rounded-xl animate-pulse"></div>
                            <div class="flex gap-4 mt-4 justify-center">
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                            </div>
                        </div>

                        <div class="h-64 transition-opacity duration-500" :class="isChartLoading ? 'opacity-0' : 'opacity-100'">
                            <canvas id="mortalityChart"></canvas>
                        </div>
                    </div>

                    <!-- Daily Feed Consumption Chart -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-gray-200 mb-4 flex items-center">
                            🥣 Grafik Konsumsi Pakan Harian (Kg)
                        </h3>

                        <!-- Skeleton Loader -->
                        <div x-show="isChartLoading" x-transition.opacity.duration.500ms class="absolute inset-0 z-10 bg-white dark:bg-slate-800 flex flex-col justify-center rounded-2xl p-6 mt-10">
                            <div class="w-full h-48 bg-slate-100 dark:bg-slate-700 rounded-xl animate-pulse"></div>
                            <div class="flex gap-4 mt-4 justify-center">
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                                <div class="w-8 h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                            </div>
                        </div>

                        <div class="h-64 transition-opacity duration-500" :class="isChartLoading ? 'opacity-0' : 'opacity-100'">
                            <canvas id="feedChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Daily Records Table with FEATURE 3: EWS High Mortality Highlight -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-xl border border-slate-200/80 dark:border-slate-700 transition-colors">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Riwayat Catatan Harian</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Baris merah menandakan Peringatan Dini Kematian Tinggi (> 0.5% populasi)</p>
                            </div>
                            @if($flock->status === 'active')
                            <button @click="openPanel = true" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-md shadow-md transition">
                                + Tambah Catatan Harian
                            </button>
                            @endif
                        </div>

                        @if($dailyRecords->isEmpty())
                        <div class="text-center py-6 text-slate-500 dark:text-slate-400 text-sm">
                            Belum ada catatan harian untuk kandang ini.
                        </div>
                        @else
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b dark:border-slate-700 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider">
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Tanggal Record</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Kematian (Mati)</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Afkir (Culling)</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Konsumsi Pakan (Kg)</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Status EWS</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm" id="dailyRecordsTableBody">
                                    @foreach($dailyRecords as $record)
                                    @php
                                    // FEATURE 3: EWS Mortality Warning Calculation (> 0.5% initial population)
                                    $mortalityRate = $flock->initial_population > 0 ? ($record->mortality / $flock->initial_population) : 0;
                                    $isHighMortality = $mortalityRate > 0.005;
                                    @endphp
                                    <tr class="{{ $isHighMortality ? 'bg-red-50 dark:bg-red-950/40 text-red-900 dark:text-red-200 border-l-4 border-red-500' : 'hover:bg-gray-50 dark:hover:bg-slate-700/40' }}">
                                        <td class="py-3 px-4 font-medium">{{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}</td>
                                        <td class="py-3 px-4 font-semibold {{ $isHighMortality ? 'text-red-700 dark:text-red-400 font-bold' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($record->mortality) }} ekor
                                        </td>
                                        <td class="py-3 px-4 text-orange-600 dark:text-orange-400 font-medium">{{ number_format($record->culling) }} ekor</td>
                                        <td class="py-3 px-4 text-indigo-700 dark:text-indigo-400 font-medium">{{ number_format($record->feed_consumed_kg, 2) }} kg</td>
                                        <td class="py-3 px-4">
                                            @if($isHighMortality)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-200 dark:bg-red-900/40 text-red-900 dark:text-red-400">
                                                ⚠️ High Mortality (>0.5%)
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400">
                                                Normal
                                            </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($flock->status === 'active')
                                            <form action="{{ route('daily_records.destroy', $record) }}" method="POST"
                                                  data-confirm-title="Hapus Catatan Harian?"
                                                  data-confirm-message="Hapus catatan ini? Stok pakan yang terpakai akan otomatis dikembalikan ke gudang kandang."
                                                  data-confirm-button="Ya, Hapus"
                                                  data-confirm-tone="danger"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200 bg-red-50 dark:bg-red-900/40 hover:bg-red-100 dark:hover:bg-red-900/60 px-2 py-1 rounded text-xs font-semibold transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 3-Step Feed Logistics (Supply Chain) -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md dark:shadow-none sm:rounded-xl border border-amber-200 dark:border-amber-900/50 transition-colors">
                    <div class="p-6 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-900/50 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-xl">
                                <span class="text-2xl">🏭</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100">Gudang Kandang & Permintaan Pakan</h3>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-200 dark:bg-amber-800 px-2 py-1 rounded">Pre-Starter: {{ number_format($flock->stock_pre_starter_kg, 2) }} Kg</span>
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-300 dark:bg-amber-700 px-2 py-1 rounded">Starter: {{ number_format($flock->stock_starter_kg, 2) }} Kg</span>
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-400 dark:bg-amber-600 px-2 py-1 rounded">Finisher: {{ number_format($flock->stock_finisher_kg, 2) }} Kg</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($flock->status === 'active' && !Auth::user()->isOwner())
                        <form action="{{ route('feed_requests.store', $flock) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto bg-white dark:bg-slate-700 p-2 rounded-xl shadow-md border border-amber-200 dark:border-slate-600">
                            @csrf
                            <select name="feed_type" required class="rounded border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm w-full sm:w-40 focus:border-amber-500 focus:ring-amber-500">
                                <option value="">Jenis Pakan...</option>
                                <option value="Pre-Starter">Pre-Starter</option>
                                <option value="Starter">Starter</option>
                                <option value="Finisher">Finisher</option>
                            </select>
                            <input type="number" name="amount_kg" placeholder="Jumlah Kg" min="1" required class="rounded border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm w-full sm:w-28 focus:border-amber-500 focus:ring-amber-500">
                            <button type="submit" class="w-full sm:w-auto px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded shadow transition flex-shrink-0">
                                + Minta Pakan
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="p-0">
                        @if($feedRequests->isEmpty())
                        <div class="text-center py-6 text-slate-500 dark:text-slate-400 text-sm">
                            Belum ada riwayat permintaan pakan.
                        </div>
                        @else
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4 border-b dark:border-slate-600">Tanggal</th>
                                        <th class="py-3 px-4 border-b dark:border-slate-600">Jenis Pakan</th>
                                        <th class="py-3 px-4 border-b dark:border-slate-600">Jumlah (Kg)</th>
                                        <th class="py-3 px-4 border-b dark:border-slate-600">Status</th>
                                        <th class="py-3 px-4 border-b dark:border-slate-600 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm dark:text-slate-200">
                                    @foreach($feedRequests as $request)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                        <td class="py-3 px-4 font-medium">{{ \Carbon\Carbon::parse($request->created_at)->format('d M Y H:i') }}</td>
                                        <td class="py-3 px-4">{{ $request->feed_type }}</td>
                                        <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($request->amount_kg, 2) }} kg</td>
                                        <td class="py-3 px-4">
                                            @if($request->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-400 border border-orange-200 dark:border-orange-800">⏳ Pending</span>
                                            @elseif($request->status === 'shipped')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-800">🚚 Dikirim</span>
                                            @elseif($request->status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">✖ Ditolak / Batal</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800">✅ Diterima</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($flock->status === 'active' && $request->status === 'pending' && Auth::user()->isOwner())
                                                <div class="inline-flex items-center gap-1.5">
                                                    <form action="{{ route('feed_requests.ship', $request) }}" method="POST" class="inline-block"
                                                          data-confirm-title="Setujui & Kirim Pakan?"
                                                          data-confirm-message="Setujui permintaan pakan ini dan kirimkan stok ke gudang kandang?"
                                                          data-confirm-button="Ya, Kirim Pakan"
                                                          data-confirm-tone="primary">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition">
                                                            Setujui &amp; Kirim
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('feed_requests.reject', $request) }}" method="POST" class="inline-block"
                                                          data-confirm-title="Tolak Permintaan Pakan?"
                                                          data-confirm-message="Permintaan pakan ini akan ditandai Ditolak tanpa memotong stok gudang pusat."
                                                          data-confirm-button="Ya, Tolak"
                                                          data-confirm-tone="danger">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/40 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-semibold text-xs rounded-lg transition">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($flock->status === 'active' && $request->status === 'pending' && !Auth::user()->isOwner())
                                                <form action="{{ route('feed_requests.reject', $request) }}" method="POST" class="inline-block"
                                                      data-confirm-title="Batalkan Permintaan Pakan?"
                                                      data-confirm-message="Batalkan permintaan pakan yang belum diproses ini?"
                                                      data-confirm-button="Ya, Batalkan"
                                                      data-confirm-tone="danger">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/40 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-semibold text-xs rounded-lg transition">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            @elseif($flock->status === 'active' && $request->status === 'shipped' && !Auth::user()->isOwner())
                                                <form action="{{ route('feed_requests.receive', $request) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded shadow transition pulse-animation">
                                                        📦 Terima Pakan
                                                    </button>
                                                </form>
                                            @elseif($request->status === 'completed')
                                                <span class="text-xs text-slate-500 font-medium">Selesai</span>
                                            @elseif($request->status === 'rejected')
                                                <span class="text-xs text-rose-500 dark:text-rose-400 font-medium">Dibatalkan</span>
                                            @elseif($flock->status !== 'active')
                                                <span class="text-xs text-slate-400 font-medium">Terkunci</span>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Harvest Data Table Section -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-xl border border-slate-200/80 dark:border-slate-700 transition-colors">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Data Panen &amp; Penutupan Kandang</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Catatan hasil panen dan pendapatan</p>
                            </div>
                            @if(Auth::user()->isOwner() && $flock->status === 'active')
                            <form action="{{ route('harvests.auto', $flock) }}" method="POST" class="inline"
                                  data-confirm-title="Panen Kandang Sekarang?"
                                  data-confirm-message="Apakah Anda yakin ingin memanen kandang ini sekarang? Data laba-rugi akan dikalkulasi secara otomatis berdasarkan proyeksi harian terakhir."
                                  data-confirm-button="Ya, Proses Panen"
                                  data-confirm-tone="emerald">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg text-xs font-bold hover:from-emerald-600 hover:to-green-700 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                    Panen Otomatis (1-Klik)
                                </button>
                            </form>
                            @endif
                        </div>

                        @if($harvests->isEmpty())
                        <div class="text-center py-6 text-slate-500 dark:text-slate-400 text-sm">
                            Belum ada data panen untuk kandang ini.
                        </div>
                        @else
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b dark:border-slate-700 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider">
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Tanggal Panen</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Ayam Terjual</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Total Berat</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Nama Pembeli</th>
                                        @can('manage-enterprise')
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Pendapatan Kotor</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4 text-right">Laba Bersih</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                                    @foreach($harvests as $harvest)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                        <td class="py-3 px-4 font-medium text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y') }}</td>
                                        <td class="py-3 px-4 text-gray-700 dark:text-slate-300 font-semibold">{{ number_format($harvest->total_chickens_sold) }} ekor</td>
                                        <td class="py-3 px-4 text-gray-700 dark:text-slate-300 font-semibold">{{ number_format($harvest->total_weight_kg, 2) }} kg</td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $harvest->buyer_name }}</td>
                                        @can('manage-enterprise')
                                        <td class="py-3 px-4 font-medium text-indigo-600 dark:text-indigo-400">Rp {{ number_format($harvest->gross_revenue, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($harvest->net_profit, 0, ',', '.') }}</td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        @if($flock->status === 'active')
        <!-- Slide-Over Panel Form -->
        <div x-show="openPanel" class="fixed inset-0 z-50 overflow-hidden" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="openPanel = false"
                 x-show="openPanel" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
            
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div class="w-screen max-w-md transform transition ease-in-out duration-300 sm:duration-500"
                     x-show="openPanel" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    
                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-slate-900 shadow-xl transition-colors">
                        <div class="bg-indigo-700 dark:bg-indigo-900 px-4 py-6 sm:px-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">Input Catatan Harian</h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button type="button" @click="openPanel = false" class="relative rounded-md text-indigo-200 hover:text-white focus:outline-none">
                                        <span class="absolute -inset-2.5"></span>
                                        <span class="sr-only">Tutup panel</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-1">
                                <p class="text-sm text-indigo-300">Catat mortalitas dan konsumsi pakan harian kandang.</p>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="relative flex-1 px-4 py-6 sm:px-6">
                            <form @submit.prevent="submitForm" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-100">Tanggal Record</label>
                                    <div class="mt-2">
                                        <input type="date" x-model="formData.record_date" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-100">Mati (Ekor)</label>
                                        <div class="mt-2">
                                            <input type="number" min="0" x-model="formData.mortality" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-100">Afkir (Ekor)</label>
                                        <div class="mt-2">
                                            <input type="number" min="0" x-model="formData.culling" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <label class="block text-sm font-bold leading-6 text-slate-900 dark:text-slate-100 border-b dark:border-slate-700 pb-1">Konsumsi Pakan per Fase (Kg)</label>
                                    
                                    <!-- Pre-Starter -->
                                    <div class="bg-amber-50 dark:bg-amber-950/30 rounded-xl p-3 border border-amber-100 dark:border-amber-800">
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="block text-xs font-bold text-amber-900 dark:text-amber-200">Pre-Starter</label>
                                            <span class="text-[10px] font-medium" :class="formData.feed_pre_starter_kg > stocks.pre_starter ? 'text-red-600 dark:text-red-400 font-bold' : 'text-amber-700 dark:text-amber-300'">
                                                Sisa: <span x-text="stocks.pre_starter.toFixed(2)"></span> Kg
                                            </span>
                                        </div>
                                        <input type="number" min="0" step="0.01" x-model.number="formData.feed_pre_starter_kg" required class="block w-full rounded-md border-0 py-2 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-amber-300 dark:ring-amber-700 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6 transition-all" :class="{'ring-red-500 focus:ring-red-500 text-red-900 bg-red-50 dark:bg-red-950/40 dark:text-red-200': formData.feed_pre_starter_kg > stocks.pre_starter}">
                                    </div>

                                    <!-- Starter -->
                                    <div class="bg-amber-100 dark:bg-amber-950/40 rounded-xl p-3 border border-amber-200 dark:border-amber-800">
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="block text-xs font-bold text-amber-900 dark:text-amber-200">Starter</label>
                                            <span class="text-[10px] font-medium" :class="formData.feed_starter_kg > stocks.starter ? 'text-red-600 dark:text-red-400 font-bold' : 'text-amber-800 dark:text-amber-300'">
                                                Sisa: <span x-text="stocks.starter.toFixed(2)"></span> Kg
                                            </span>
                                        </div>
                                        <input type="number" min="0" step="0.01" x-model.number="formData.feed_starter_kg" required class="block w-full rounded-md border-0 py-2 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-amber-400 dark:ring-amber-700 focus:ring-2 focus:ring-inset focus:ring-amber-600 sm:text-sm sm:leading-6 transition-all" :class="{'ring-red-500 focus:ring-red-500 text-red-900 bg-red-50 dark:bg-red-950/40 dark:text-red-200': formData.feed_starter_kg > stocks.starter}">
                                    </div>

                                    <!-- Finisher -->
                                    <div class="bg-amber-200 dark:bg-amber-950/50 rounded-xl p-3 border border-amber-300 dark:border-amber-800">
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="block text-xs font-bold text-amber-900 dark:text-amber-200">Finisher</label>
                                            <span class="text-[10px] font-medium" :class="formData.feed_finisher_kg > stocks.finisher ? 'text-red-600 dark:text-red-400 font-bold' : 'text-amber-900 dark:text-amber-300'">
                                                Sisa: <span x-text="stocks.finisher.toFixed(2)"></span> Kg
                                            </span>
                                        </div>
                                        <input type="number" min="0" step="0.01" x-model.number="formData.feed_finisher_kg" required class="block w-full rounded-md border-0 py-2 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-amber-500 dark:ring-amber-700 focus:ring-2 focus:ring-inset focus:ring-amber-600 sm:text-sm sm:leading-6 transition-all" :class="{'ring-red-500 focus:ring-red-500 text-red-900 bg-red-50 dark:bg-red-950/40 dark:text-red-200': formData.feed_finisher_kg > stocks.finisher}">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-100">Bobot Rata-rata (Gram/Ekor)</label>
                                    <div class="mt-2">
                                        <input type="number" id="average_weight_gram" min="0" x-model="formData.average_weight_gram" class="block w-full rounded-md border-0 py-1.5 text-slate-900 dark:text-slate-100 dark:bg-slate-800 shadow-md ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    </div>
                                    
                                    <!-- Mulai: Widget Kalkulator Sampling Acak -->
                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-800 rounded-xl shadow-md">
                                        <h4 class="text-sm font-bold text-blue-800 dark:text-blue-200 mb-3 flex items-center gap-2">
                                            <span>⚖️</span> Kalkulator Sampling Acak (3 Karung x 10 Ekor)
                                        </h4>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-2">
                                            <div>
                                                <label for="sample_1" class="block text-xs text-blue-700 dark:text-blue-300 font-semibold mb-1 uppercase tracking-wide">Karung 1 (Kg)</label>
                                                <input type="number" id="sample_1" placeholder="0.00" step="0.01" min="0" class="w-full text-sm rounded border-blue-200 dark:border-blue-700 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label for="sample_2" class="block text-xs text-blue-700 dark:text-blue-300 font-semibold mb-1 uppercase tracking-wide">Karung 2 (Kg)</label>
                                                <input type="number" id="sample_2" placeholder="0.00" step="0.01" min="0" class="w-full text-sm rounded border-blue-200 dark:border-blue-700 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                                            </div>
                                            <div>
                                                <label for="sample_3" class="block text-xs text-blue-700 dark:text-blue-300 font-semibold mb-1 uppercase tracking-wide">Karung 3 (Kg)</label>
                                                <input type="number" id="sample_3" placeholder="0.00" step="0.01" min="0" class="w-full text-sm rounded border-blue-200 dark:border-blue-700 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mt-4 pt-3 border-t border-blue-200 dark:border-blue-800 gap-3">
                                            <span id="calc_result" class="font-black text-lg text-blue-700 dark:text-blue-300">Hasil: 0 Gram/Ekor</span>
                                            <button type="button" id="btn_apply_weight" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-2 rounded shadow transition w-full sm:w-auto">
                                                Gunakan Hasil Ini
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Selesai: Widget Kalkulator Sampling Acak -->
                                </div>

                                <!-- Error messages from server -->
                                <template x-if="errorMessage">
                                    <div class="rounded-md bg-red-50 dark:bg-red-950/40 p-4 mt-4 border border-red-200 dark:border-red-800">
                                        <div class="flex">
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800 dark:text-red-300" x-text="errorMessage"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700 mt-6 pt-6">
                                    <button type="button" @click="openPanel = false" class="rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-md ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                                    <button type="submit" :disabled="isSubmitting" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                                        <span x-show="!isSubmitting">Simpan Catatan</span>
                                        <span x-show="isSubmitting">Menyimpan...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Alpine Component Logic -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dailyRecordSlideOver', () => ({
                    openPanel: false,
                    isSubmitting: false,
                    errorMessage: '',
                    stocks: {
                        pre_starter: {{ $flock->stock_pre_starter_kg ?? 0 }},
                        starter: {{ $flock->stock_starter_kg ?? 0 }},
                        finisher: {{ $flock->stock_finisher_kg ?? 0 }}
                    },
                    formData: {
                        record_date: new Date().toISOString().split('T')[0],
                        mortality: 0,
                        culling: 0,
                        feed_pre_starter_kg: 0,
                        feed_starter_kg: 0,
                        feed_finisher_kg: 0,
                        average_weight_gram: ''
                    },
                    
                    async submitForm() {
                        this.isSubmitting = true;
                        this.errorMessage = '';
                        
                        try {
                            const response = await fetch('{{ route('daily_records.store', $flock) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(this.formData)
                            });
                            
                            const data = await response.json();
                            
                            if (response.ok) {
                                // Success!
                                this.openPanel = false;
                                
                                // Show global toast
                                this.$dispatch('notify', { message: data.message || 'Berhasil disimpan!', type: 'success' });
                                
                                // Update Table and Charts dynamically
                                this.updateUI(data);
                                
                                // Reset form
                                this.formData = {
                                    record_date: new Date().toISOString().split('T')[0],
                                    mortality: 0,
                                    culling: 0,
                                    feed_pre_starter_kg: 0,
                                    feed_starter_kg: 0,
                                    feed_finisher_kg: 0,
                                    average_weight_gram: ''
                                };
                            } else {
                                this.errorMessage = data.message || 'Terjadi kesalahan saat menyimpan data.';
                            }
                        } catch (error) {
                            this.errorMessage = 'Terjadi kesalahan jaringan.';
                        } finally {
                            this.isSubmitting = false;
                        }
                    },

                    updateUI(data) {
                        // 1. Update Table (Clear and Re-render to ensure chronological order)
                        const tbody = document.getElementById('dailyRecordsTableBody');
                        if (tbody) {
                            tbody.innerHTML = '';
                            data.records.forEach(record => {
                                const trClass = record.isHighMortality
                                    ? 'bg-red-50 dark:bg-red-950/40 text-red-900 dark:text-red-200 border-l-4 border-red-500'
                                    : 'hover:bg-gray-50 dark:hover:bg-slate-700/40';
                                const mortClass = record.isHighMortality
                                    ? 'text-red-700 dark:text-red-400 font-bold'
                                    : 'text-red-600 dark:text-red-400';
                                const statusBadge = record.isHighMortality 
                                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-200 dark:bg-red-900/40 text-red-900 dark:text-red-400">⚠️ High Mortality (>0.5%)</span>'
                                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400">Normal</span>';

                                const tr = document.createElement('tr');
                                tr.className = trClass;
                                tr.innerHTML = `
                                    <td class="py-3 px-4 font-medium">${record.date_formatted}</td>
                                    <td class="py-3 px-4 font-semibold ${mortClass}">${record.mortality} ekor</td>
                                    <td class="py-3 px-4 text-orange-600 dark:text-orange-400 font-medium">${record.culling} ekor</td>
                                    <td class="py-3 px-4 text-indigo-700 dark:text-indigo-400 font-medium">${record.feed_consumed_kg} kg</td>
                                    <td class="py-3 px-4">${statusBadge}</td>
                                    <td class="py-3 px-4 text-right">
                                        ${ '{{ $flock->status }}' === 'active' ? `
                                        <form action="/daily-records/${record.id}" method="POST" data-confirm-title="Hapus Catatan Harian?" data-confirm-message="Hapus catatan ini? Stok pakan yang terpakai akan otomatis dikembalikan ke gudang kandang." data-confirm-button="Ya, Hapus" data-confirm-tone="danger" class="inline">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200 bg-red-50 dark:bg-red-900/40 hover:bg-red-100 dark:hover:bg-red-900/60 px-2 py-1 rounded text-xs font-semibold transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                        ` : ''}
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        }

                        // 2. Update Charts Dynamically
                        if (window.mortalityChartInstance) {
                            window.mortalityChartInstance.data.labels = data.chart.dates;
                            window.mortalityChartInstance.data.datasets[0].data = data.chart.mortality;
                            window.mortalityChartInstance.update();
                        }
                        
                        if (window.feedChartInstance) {
                            window.feedChartInstance.data.labels = data.chart.dates;
                            window.feedChartInstance.data.datasets[0].data = data.chart.feed;
                            window.feedChartInstance.update();
                        }

                        if (window.bepChartInstance && data.chart.bep) {
                            window.bepChartInstance.data.labels = data.chart.bep.labels;
                            window.bepChartInstance.data.datasets[0].data = data.chart.bep.actualWeights;
                            window.bepChartInstance.data.datasets[1].data = data.chart.bep.bepTargets;
                            window.bepChartInstance.update();
                        }

                        // 3. Update Feed Stocks Live UI
                        this.stocks.pre_starter = data.stock_pre_starter;
                        this.stocks.starter = data.stock_starter;
                        this.stocks.finisher = data.stock_finisher;
                        
                        // Note: The global page stock display will reload on full refresh, 
                        // but if we want to update it now without refresh:
                        const stockText = document.querySelector('.flex.flex-wrap.gap-2.mt-2');
                        if (stockText) {
                            stockText.innerHTML = `
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-200 dark:bg-amber-800 px-2 py-1 rounded">Pre-Starter: ${data.stock_pre_starter.toFixed(2)} Kg</span>
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-300 dark:bg-amber-700 px-2 py-1 rounded">Starter: ${data.stock_starter.toFixed(2)} Kg</span>
                                <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-400 dark:bg-amber-600 px-2 py-1 rounded">Finisher: ${data.stock_finisher.toFixed(2)} Kg</span>
                            `;
                        }

                        const stockDisplay = document.getElementById('current_stock_display');
                        if (stockDisplay && data.current_stock !== undefined) {
                            stockDisplay.innerText = Number(data.current_stock).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
                        }
                    }
                }));
            });
        </script>

    </div>

        <!-- Chart.js CDN & Script -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dates = @json($chartDates);
                const mortalityData = @json($chartMortality);
                const feedData = @json($chartFeed);
                

                // Kalkulator Sampling Logic
                const s1 = document.getElementById('sample_1');
                const s2 = document.getElementById('sample_2');
                const s3 = document.getElementById('sample_3');
                const resultText = document.getElementById('calc_result');
                const btnApply = document.getElementById('btn_apply_weight');
                const mainInput = document.getElementById('average_weight_gram');

                function calculateAverage() {
                    let val1 = parseFloat(s1.value) || 0;
                    let val2 = parseFloat(s2.value) || 0;
                    let val3 = parseFloat(s3.value) || 0;

                    let totalKg = val1 + val2 + val3;
                    let avgGram = (totalKg / 30) * 1000;
                    
                    resultText.innerText = 'Hasil: ' + avgGram.toFixed(0) + ' Gram/Ekor';
                    
                    return avgGram.toFixed(0);
                }

                if(s1 && s2 && s3 && btnApply && mainInput) {
                    s1.addEventListener('input', calculateAverage);
                    s2.addEventListener('input', calculateAverage);
                    s3.addEventListener('input', calculateAverage);

                    btnApply.addEventListener('click', function() {
                        const finalResult = calculateAverage();
                        
                        // Force Alpine.js x-model update by dispatching input event
                        mainInput.value = finalResult;
                        mainInput.dispatchEvent(new Event('input', { bubbles: true }));
                        
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: '⚖️ Timbangan berhasil disalin: ' + finalResult + ' Gram/Ekor',
                                type: 'success'
                            }
                        }));
                        
                        const originalBg = mainInput.style.backgroundColor;
                        mainInput.style.transition = 'background-color 0.5s ease';
                        mainInput.style.backgroundColor = window.BroilerTheme?.isDark()
                            ? 'rgba(16, 185, 129, 0.25)'
                            : '#dcfce7';
                        setTimeout(() => {
                            mainInput.style.backgroundColor = originalBg;
                        }, 1000);
                    });
                }

                const buildChart = (ctx, config) => window.BroilerTheme
                    ? window.BroilerTheme.createChart(ctx, config)
                    : new Chart(ctx, config);

                // 1. Mortality Chart
                const mortalityCanvas = document.getElementById('mortalityChart');
                if (mortalityCanvas) {
                    window.mortalityChartInstance = buildChart(mortalityCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Kematian (Ekor)',
                                data: mortalityData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.12)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: {} },
                                tooltip: { padding: 10, boxPadding: 4 }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {},
                                    ticks: {}
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {}
                                }
                            }
                        }
                    });
                }

                // 2. Feed Chart
                const feedCanvas = document.getElementById('feedChart');
                if (feedCanvas) {
                    window.feedChartInstance = buildChart(feedCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Konsumsi Pakan (Kg)',
                                data: feedData,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: {} },
                                tooltip: { padding: 10, boxPadding: 4 }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {},
                                    ticks: {}
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {}
                                }
                            }
                        }
                    });
                }

                // 3. BEP Growth Tracker Chart (AI Forecast)
                const bepDataRaw = {!! $bepChartData ?? 'null' !!};
                const bepCanvas = document.getElementById('bepChart');
                if (bepDataRaw && bepCanvas) {
                    window.bepChartInstance = buildChart(bepCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: bepDataRaw.labels,
                            datasets: [
                                {
                                    label: 'Aktual Rata-Rata Berat (Gram)',
                                    data: bepDataRaw.actualWeights,
                                    borderColor: '#3b82f6', // Blue
                                    backgroundColor: 'rgba(59, 130, 246, 0.12)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Target Minimal BEP (Gram)',
                                    data: bepDataRaw.bepTargets,
                                    borderColor: '#ef4444', // Red
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    fill: false,
                                    tension: 0.1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: { labels: {} },
                                tooltip: {
                                    padding: 12,
                                    boxPadding: 4,
                                    usePointStyle: true,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Berat per Ekor (Gram)' },
                                    grid: {},
                                    ticks: {}
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {}
                                }
                            }
                        }
                    });
                }
            });
        </script>
</x-app-layout>