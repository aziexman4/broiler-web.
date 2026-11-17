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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div x-data="{ search: '' }" class="bg-white dark:bg-slate-900 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-800 transition-all hover:shadow-lg">
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
                        <!-- Search Box (Interactive Data Table) -->
                        <div class="mb-5">
                            <div class="relative w-full md:w-1/3">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input x-model="search" type="text" class="bg-slate-50 border border-gray-300 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 dark:bg-slate-800 dark:border-slate-700 dark:text-white transition-colors duration-200" placeholder="Ketik nama kandang atau status...">
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                        <th class="py-3 px-4">Nama Kandang & Unit</th>
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
                                        <tr x-show="search === '' || '{{ strtolower($flock->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($flock->coop->name ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($flock->status) }}'.includes(search.toLowerCase())" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-150">
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
                                                <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-slate-700 text-white rounded text-xs font-medium hover:bg-gray-700 dark:hover:bg-slate-600">
                                                    Lihat Detail &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
