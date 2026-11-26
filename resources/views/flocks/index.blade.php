<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                    {{ __('Daftar Kandang (Flocks)') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola siklus budidaya aktif, arsip tutup buku, dan penugasan pekerja</p>
            </div>
            @if(Auth::user()->isOwner())
            <a href="{{ route('flocks.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm transition">
                + Tambah Kandang Baru
            </a>
            @endif
        </div>
    </x-slot>

    @php
        $initialSearch = (string) request('search', '');
        $initialCoop = (string) request('coop_id', '');
        $initialStatus = in_array(request('status'), ['active', 'closed'], true) ? request('status') : '';
        $initialWorker = (string) request('worker_id', '');
        $initialSort = in_array(request('sort'), ['active_first', 'start_desc', 'start_asc', 'pop_desc'], true)
            ? request('sort')
            : 'active_first';

        $activeFlockCount = $flocks->where('status', 'active')->count();
        $closedFlockCount = $flocks->where('status', 'closed')->count();

        $flocksMeta = $flocks->map(function ($flock) {
            return [
                'id' => $flock->id,
                'name' => strtolower($flock->name),
                'coop_id' => $flock->coop_id ? (string) $flock->coop_id : '',
                'coop_name' => strtolower($flock->coop->name ?? ''),
                'breed' => strtolower($flock->breed),
                'worker_id' => $flock->worker_id ? (string) $flock->worker_id : '',
                'worker' => strtolower($flock->worker->name ?? ''),
                'status' => strtolower($flock->status),
                'start_ts' => \Carbon\Carbon::parse($flock->start_date)->timestamp,
                'initial_population' => (int) $flock->initial_population,
            ];
        })->values();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            @if(Auth::user()->isOwner())
            <!-- Enterprise Pricing Settings Widget -->
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all hover:shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl hidden sm:block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Pengaturan Harga Acuan (Live Market, DOC &amp; Pakan)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Acuan otomatis Target BEP harian, proyeksi P&amp;L berjalan, dan Panen Otomatis 1-Klik.</p>
                    </div>
                </div>

                <form action="{{ route('settings.updatePrice') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 items-end gap-3 w-full lg:w-auto">
                    @csrf
                    <div>
                        <label for="global_market_price" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Harga Ayam (/Kg)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                            <input id="global_market_price" type="number" name="global_market_price" value="{{ $globalMarketPrice }}" min="0" required class="pl-9 w-full sm:w-36 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500 font-bold shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label for="doc_price" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Harga DOC (/Ekor)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                            <input id="doc_price" type="number" name="doc_price" value="{{ $docPrice }}" min="0" class="pl-9 w-full sm:w-36 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500 font-bold shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label for="feed_price" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Harga Pakan (/Kg)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                            <input id="feed_price" type="number" name="feed_price" value="{{ $feedPrice }}" min="0" class="pl-9 w-full sm:w-36 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500 font-bold shadow-sm">
                        </div>
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        Simpan Harga
                    </button>
                </form>
            </div>
            @endif

            <div x-data="flockListFilter()" class="bg-white dark:bg-slate-900 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-800 transition-all hover:shadow-lg">
                <div class="p-5 sm:p-6 text-slate-900 dark:text-slate-100">
                    @if($flocks->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Belum ada kandang terdaftar</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                @if(Auth::user()->isOwner())
                                    Mulai dengan membuat data kandang baru untuk peternakan Anda.
                                @else
                                    Belum ada kandang yang ditugaskan kepada Anda.
                                @endif
                            </p>
                            @if(Auth::user()->isOwner())
                            <div class="mt-6">
                                <a href="{{ route('flocks.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700">
                                    + Tambah Kandang
                                </a>
                            </div>
                            @endif
                        </div>
                    @else
                        <!-- Reformulated 2-Tier Filter Bar -->
                        <div class="mb-5 space-y-3.5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <!-- Tier 1: Status Tabs, Search Input & Sort -->
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                <!-- Status Segmented Tabs -->
                                <div class="inline-flex flex-wrap items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 self-start">
                                    <button type="button"
                                        @click="setStatus('')"
                                        :class="selectedStatus === ''
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold'
                                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition">
                                        Semua Status ({{ $flocks->count() }})
                                    </button>
                                    <button type="button"
                                        @click="setStatus('active')"
                                        :class="selectedStatus === 'active'
                                            ? 'bg-emerald-600 text-white shadow-xs font-bold'
                                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition inline-flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full" :class="selectedStatus === 'active' ? 'bg-white' : 'bg-emerald-500'"></span>
                                        Aktif ({{ $activeFlockCount }})
                                    </button>
                                    <button type="button"
                                        @click="setStatus('closed')"
                                        :class="selectedStatus === 'closed'
                                            ? 'bg-slate-700 text-white shadow-xs font-bold dark:bg-slate-600'
                                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition">
                                        Tutup Buku ({{ $closedFlockCount }})
                                    </button>
                                </div>

                                <!-- Search + Sort -->
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 lg:max-w-xl lg:justify-end">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input x-model="search"
                                            @input="onFilterChange()"
                                            type="text"
                                            class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-9 pr-8 py-2 dark:bg-slate-800 dark:border-slate-700 dark:text-white transition-colors"
                                            placeholder="Cari nama siklus, ras, atau pekerja...">
                                        <button type="button"
                                            x-show="search !== ''"
                                            x-cloak
                                            @click="search = ''; onFilterChange()"
                                            aria-label="Hapus pencarian"
                                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                            &times;
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label for="flock-sort" class="text-xs font-semibold text-slate-500 dark:text-slate-400 shrink-0">Urut:</label>
                                        <select id="flock-sort"
                                            x-model="sortBy"
                                            @change="onFilterChange()"
                                            class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 py-2 pl-3 pr-8 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
                                            <option value="active_first">Aktif Dulu (Default)</option>
                                            <option value="start_desc">Tanggal Mulai Terbaru</option>
                                            <option value="start_asc">Tanggal Mulai Terlama</option>
                                            <option value="pop_desc">Populasi Terbesar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Tier 2: Unit Kandang Pills, Worker Filter (Owner) & Live Counter -->
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button"
                                        @click="setCoop('')"
                                        :class="selectedCoop === ''
                                            ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                        class="px-3 py-1 rounded-xl border text-xs font-semibold transition">
                                        Semua Unit Kandang
                                    </button>
                                    @foreach($coops as $coop)
                                        @php $unitCount = $flocks->where('coop_id', $coop->id)->count(); @endphp
                                        <button type="button"
                                            @click="setCoop('{{ $coop->id }}')"
                                            :class="selectedCoop === '{{ $coop->id }}'
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                            class="px-3 py-1 rounded-xl border text-xs font-semibold transition">
                                            {{ $coop->name }} ({{ $unitCount }})
                                        </button>
                                    @endforeach

                                    @if(Auth::user()->isOwner() && $workers->isNotEmpty())
                                        <select x-model="selectedWorker"
                                            @change="onFilterChange()"
                                            aria-label="Filter Pekerja Kandang"
                                            class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 py-1.5 pl-3 pr-8 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
                                            <option value="">Semua Pekerja</option>
                                            @foreach($workers as $workerOption)
                                                <option value="{{ $workerOption->id }}">{{ $workerOption->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between lg:justify-end gap-3 text-xs">
                                    <span class="text-slate-500 dark:text-slate-400">
                                        Menampilkan <strong class="text-slate-800 dark:text-slate-200" x-text="visibleCount()">{{ $flocks->count() }}</strong> dari {{ $flocks->count() }} siklus
                                        (<strong class="text-emerald-600 dark:text-emerald-400" x-text="visiblePopulation().toLocaleString('id-ID')">{{ number_format($flocks->sum('initial_population'), 0, ',', '.') }}</strong> ekor)
                                    </span>
                                    <button type="button"
                                        x-show="hasActiveFilter()"
                                        x-cloak
                                        @click="resetFilters()"
                                        class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold border border-slate-200 dark:border-slate-700 transition">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Nama Kandang &amp; Unit</th>
                                        <th class="py-3 px-4">Ras / Breed</th>
                                        <th class="py-3 px-4">Anak Kandang (Worker)</th>
                                        <th class="py-3 px-4">Tanggal Mulai</th>
                                        <th class="py-3 px-4">Populasi Awal</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody x-ref="flockTableBody" class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                                    @foreach($flocks as $flock)
                                        <tr data-flock-id="{{ $flock->id }}"
                                            x-show="matchesId({{ $flock->id }})"
                                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-150">
                                            <td class="py-3 px-4">
                                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $flock->name }}</div>
                                                @if($flock->coop)
                                                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">
                                                        📍 {{ $flock->coop->name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $flock->breed }}</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-slate-300">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-100 dark:border-teal-800">
                                                    👤 {{ $flock->worker->name ?? 'Belum Ditugaskan' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }}</td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ number_format($flock->initial_population) }} ekor</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $flock->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-gray-300' }}">
                                                    {{ ucfirst($flock->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-3 py-1 bg-slate-800 dark:bg-slate-700 text-white rounded-lg text-xs font-medium hover:bg-slate-700 dark:hover:bg-slate-600">
                                                        Lihat Detail &rarr;
                                                    </a>
                                                    @can('manage-enterprise')
                                                        <form action="{{ route('flocks.destroy', $flock) }}" method="POST" class="inline"
                                                              data-confirm-title="Hapus Siklus Kandang?"
                                                              data-confirm-message="Apakah Anda yakin ingin menghapus siklus kandang ini?"
                                                              data-confirm-button="Ya, Hapus Siklus"
                                                              data-confirm-tone="danger">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-rose-50 dark:bg-rose-900/40 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 text-xs font-semibold rounded-lg border border-rose-200 dark:border-rose-800 transition-colors">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr x-ref="emptyFlockRow" x-show="visibleCount() === 0" x-cloak style="display: none;">
                                        <td colspan="7" class="py-8 px-4 text-center text-slate-500 dark:text-slate-400 text-sm">
                                            Tidak ada siklus kandang yang cocok dengan kombinasi pencarian &amp; filter yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('flockListFilter', () => ({
                search: @json($initialSearch),
                selectedCoop: @json($initialCoop),
                selectedStatus: @json($initialStatus),
                selectedWorker: @json($initialWorker),
                sortBy: @json($initialSort),
                items: @json($flocksMeta),

                init() {
                    this.syncOrder();
                },

                matchesItem(item) {
                    const q = this.search.trim().toLowerCase();
                    const matchSearch = q === ''
                        || item.name.includes(q)
                        || item.coop_name.includes(q)
                        || item.breed.includes(q)
                        || item.worker.includes(q)
                        || item.status.includes(q);
                    const matchCoop = this.selectedCoop === '' || item.coop_id === this.selectedCoop;
                    const matchStatus = this.selectedStatus === '' || item.status === this.selectedStatus;
                    const matchWorker = this.selectedWorker === '' || item.worker_id === this.selectedWorker;
                    return matchSearch && matchCoop && matchStatus && matchWorker;
                },

                matchesId(id) {
                    const item = this.items.find(i => i.id === id);
                    return item ? this.matchesItem(item) : true;
                },

                filteredItems() {
                    return this.items.filter(item => this.matchesItem(item));
                },

                visibleCount() {
                    return this.filteredItems().length;
                },

                visiblePopulation() {
                    return this.filteredItems().reduce((sum, item) => sum + Number(item.initial_population || 0), 0);
                },

                hasActiveFilter() {
                    return this.search.trim() !== ''
                        || this.selectedCoop !== ''
                        || this.selectedStatus !== ''
                        || this.selectedWorker !== ''
                        || this.sortBy !== 'active_first';
                },

                setStatus(status) {
                    this.selectedStatus = status;
                    this.onFilterChange();
                },

                setCoop(coopId) {
                    this.selectedCoop = this.selectedCoop === coopId && coopId !== '' ? '' : coopId;
                    this.onFilterChange();
                },

                resetFilters() {
                    this.search = '';
                    this.selectedCoop = '';
                    this.selectedStatus = '';
                    this.selectedWorker = '';
                    this.sortBy = 'active_first';
                    this.onFilterChange();
                },

                onFilterChange() {
                    this.syncOrder();
                    this.syncUrl();
                },

                sortedItems() {
                    const list = [...this.items];
                    switch (this.sortBy) {
                        case 'start_desc':
                            return list.sort((a, b) => b.start_ts - a.start_ts || b.id - a.id);
                        case 'start_asc':
                            return list.sort((a, b) => a.start_ts - b.start_ts || a.id - b.id);
                        case 'pop_desc':
                            return list.sort((a, b) => b.initial_population - a.initial_population || b.start_ts - a.start_ts);
                        case 'active_first':
                        default:
                            return list.sort((a, b) => {
                                const rankA = a.status === 'active' ? 0 : 1;
                                const rankB = b.status === 'active' ? 0 : 1;
                                return rankA - rankB || b.start_ts - a.start_ts || b.id - a.id;
                            });
                    }
                },

                syncOrder() {
                    const tbody = this.$refs.flockTableBody;
                    if (!tbody) return;
                    const rowMap = new Map();
                    tbody.querySelectorAll('tr[data-flock-id]').forEach((tr) => {
                        rowMap.set(String(tr.dataset.flockId), tr);
                    });
                    this.sortedItems().forEach((item) => {
                        const tr = rowMap.get(String(item.id));
                        if (tr) tbody.appendChild(tr);
                    });
                    if (this.$refs.emptyFlockRow) {
                        tbody.appendChild(this.$refs.emptyFlockRow);
                    }
                },

                syncUrl() {
                    const params = new URLSearchParams();
                    if (this.search.trim() !== '') params.set('search', this.search.trim());
                    if (this.selectedCoop !== '') params.set('coop_id', this.selectedCoop);
                    if (this.selectedStatus !== '') params.set('status', this.selectedStatus);
                    if (this.selectedWorker !== '') params.set('worker_id', this.selectedWorker);
                    if (this.sortBy !== 'active_first') params.set('sort', this.sortBy);
                    const qs = params.toString();
                    const nextUrl = qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
                    window.history.replaceState({}, '', nextUrl);
                }
            }));
        });
    </script>
</x-app-layout>
