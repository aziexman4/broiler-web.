<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Manajemen Arus Kas Operasional (Cash Flow)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Pencatatan pengeluaran operasional umum dan pemasukan non-panen peternakan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Notification -->

            <!-- Top Section: 3 KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Total Pemasukan -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pemasukan Kas</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600 mt-2">
                                Rp {{ number_format($total_in, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span>Akumulasi dana kas masuk</span>
                    </div>
                </div>

                <!-- Card 2: Total Pengeluaran -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengeluaran Kas</p>
                            <h3 class="text-2xl font-extrabold text-rose-600 mt-2">
                                Rp {{ number_format($total_out, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-rose-100 text-rose-600 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span>Akumulasi dana kas keluar</span>
                    </div>
                </div>

                <!-- Card 3: Saldo Kas Saat Ini -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo Kas Saat Ini</p>
                            <h3 class="text-2xl font-extrabold {{ $balance >= 0 ? 'text-blue-600' : 'text-rose-600' }} mt-2">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-100 text-blue-600 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span>Selisih akumulasi pemasukan & pengeluaran</span>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Form Input Kas -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6">
                <div class="pb-4 mb-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Catatan Kas</h3>
                    <p class="text-xs text-slate-500">Catat transaksi keuangan operasional seperti sekam, listrik, atau penjualan pupuk</p>
                </div>

                <form action="{{ route('cash-flows.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Tanggal -->
                        <div>
                            <label for="transaction_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Tanggal Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Keterangan -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Keterangan Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="description" name="description" placeholder="misal: Pembelian Sekam Alas, Listrik Kandang, Jual Pupuk" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Jenis Transaksi -->
                        <div>
                            <label for="type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Jenis Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <select id="type" name="type" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="in">Pemasukan (+ Kas In)</option>
                                <option value="out">Pengeluaran (- Kas Out)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nominal Rupiah -->
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Nominal Rupiah (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" placeholder="500000" min="1" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Riwayat Transaksi Kas</h3>
                        <p class="text-xs text-slate-500">Daftar mutasi pengeluaran dan pemasukan operasional</p>
                    </div>
                </div>

                <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Tanggal</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Keterangan Transaksi</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Jenis</th>
                                <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5 text-right">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            @forelse ($records as $record)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900">
                                        {{ \Carbon\Carbon::parse($record->transaction_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $record->description }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($record->type === 'in')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                + Pemasukan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                - Pengeluaran
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold {{ $record->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $record->type === 'in' ? '+' : '-' }} Rp {{ number_format($record->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <p class="font-medium text-slate-500">Belum Ada Transaksi Kas</p>
                                        <p class="text-xs mt-1">Gunakan formulir di atas untuk mencatat pemasukan atau pengeluaran kas.</p>
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
