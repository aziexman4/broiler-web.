<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                {{ __('Dashboard Kandang (Flocks)') }}
            </h2>
            @if(Auth::user()->isOwner())
            <a href="{{ route('flocks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                + Tambah Kandang Baru
            </a>
            @endif
        </div>
    </x-slot>

    @php
        $initialSearch = (string) request('search', '');
        $initialCoop = (string) request('coop_id', '');
        $initialStatus = in_array(request('status'), ['active', 'closed'], true) ? request('status') : '';
        $flocksMeta = $flocks->map(function ($flock) {
            return [
                'id' => $flock->id,
                'name' => strtolower($flock->name),
                'coop_id' => $flock->coop_id ? (string) $flock->coop_id : '',
                'coop_name' => strtolower($flock->coop->name ?? ''),
                'breed' => strtolower($flock->breed),
                'worker' => strtolower($flock->worker->name ?? ''),
                'status' => strtolower($flock->status),
            ];
        })->values();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
            <!-- Global Market Price Widget -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between mb-6 gap-4 transition-all hover:shadow-lg">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl hidden sm:block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Harga Pasar Global (Live)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Digunakan untuk acuan otomatis Target BEP pertumbuhan ayam.</p>
                    </div>
                </div>

                <form action="{{ route('settings.updatePrice') }}" method="POST" class="flex items-center gap-2 w-full md:w-auto">
                    @csrf
                    <div class="relative flex-1 md:w-40">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500 font-medium">Rp</span>
                        <input type="number" name="global_market_price" value="{{ $globalMarketPrice }}" required class="pl-10 w-full rounded-2xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500 font-bold shadow-md">
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-md hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        Simpan Harga
                    </button>
                </form>
            </div>
            @endif

            <div x-data="flockListFilter()" class="bg-white dark:bg-slate-900 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-800 transition-all hover:shadow-lg">
                <div class="p-6 text-slate-900 dark:text-slate-100">
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
                                <a href="{{ route('flocks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">
                                    + Tambah Kandang
                                </a>
                            </div>
                            @endif
                        </div>
                    @else
                        <!-- Search & Dropdown Filter Bar (Unit Lokasi & Status) -->
                        <div class="mb-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                                <!-- Search Box -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input x-model="search" type="text" class="bg-slate-50 border border-gray-300 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-9 p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-white transition-colors duration-200" placeholder="Cari kandang, ras, atau pekerja...">
                                </div>

                                <!-- Dropdown Unit Lokasi Kandang -->
                                <div>
                                    <select x-model="selectedCoop" aria-label="Filter Unit Lokasi Kandang" class="bg-slate-50 border border-gray-300 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-white transition-colors duration-200">
                                        <option value="">📍 Semua Unit Kandang</option>
                                        @foreach($coops as $coop)
                                            <option value="{{ $coop->id }}">
                                                {{ $coop->name }}{{ $coop->is_headquarters ? ' (Pusat)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Dropdown Status -->
                                <div>
                                    <select x-model="selectedStatus" aria-label="Filter Status Siklus" class="bg-slate-50 border border-gray-300 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-white transition-colors duration-200">
                                        <option value="">🔄 Semua Status</option>
                                        <option value="active">🟢 Active (Berjalan)</option>
                                        <option value="closed">🔒 Closed (Tutup Buku)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between lg:justify-end gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">
                                    Menampilkan <strong class="text-slate-800 dark:text-slate-200" x-text="visibleCount()">{{ $flocks->count() }}</strong> dari {{ $flocks->count() }} siklus
                                </span>
                                <button type="button"
                                    x-show="hasActiveFilter()"
                                    x-cloak
                                    @click="resetFilters()"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold border border-slate-200 dark:border-slate-700 transition">
                                    Reset Filter
                                </button>
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
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                                    @foreach($flocks as $flock)
                                        <tr x-show="matchesId({{ $flock->id }})" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-150">
                                            <td class="py-3 px-4">
                                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $flock->name }}</div>
                                                @if($flock->coop)
                                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mt-0.5">
                                                        📍 {{ $flock->coop->name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $flock->breed }}</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-slate-300">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                                    👤 {{ $flock->worker->name ?? 'Belum Ditugaskan' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }}</td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ number_format($flock->initial_population) }} ekor</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $flock->status === 'active' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-gray-300' }}">
                                                    {{ ucfirst($flock->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-slate-700 text-white rounded text-xs font-medium hover:bg-gray-700 dark:hover:bg-slate-600">
                                                        Lihat Detail &rarr;
                                                    </a>
                                                    @can('manage-enterprise')
                                                        <form action="{{ route('flocks.destroy', $flock) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siklus kandang ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-rose-50 dark:bg-rose-900/40 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 text-xs font-semibold rounded border border-rose-200 dark:border-rose-800 transition-colors">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr x-show="visibleCount() === 0" x-cloak style="display: none;">
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
                items: @json($flocksMeta),

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
                    return matchSearch && matchCoop && matchStatus;
                },

                matchesId(id) {
                    const item = this.items.find(i => i.id === id);
                    return item ? this.matchesItem(item) : true;
                },

                visibleCount() {
                    return this.items.filter(item => this.matchesItem(item)).length;
                },

                hasActiveFilter() {
                    return this.search.trim() !== '' || this.selectedCoop !== '' || this.selectedStatus !== '';
                },

                resetFilters() {
                    this.search = '';
                    this.selectedCoop = '';
                    this.selectedStatus = '';
                }
            }));
        });
    </script>
</x-app-layout>
