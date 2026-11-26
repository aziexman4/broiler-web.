<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.18.12l-.3.12a2 2 0 00-1.02 2.58l.64 1.54a2 2 0 002.58 1.02l.3-.12a2 2 0 001.18-.12l2.38.48a6 6 0 003.86-.52l.32-.16a6 6 0 013.86-.52l2.38.48a2 2 0 001.18-.12l.3-.12a2 2 0 001.02-2.58l-.64-1.54z" />
                    </svg>
                    Manajemen Stok OVK (Obat, Vaksin &amp; Kimia)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencatatan inventaris pembelian, sisa stok tersedia, dan distribusi pemakaian per kandang</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success & Error Alert Notifications -->
            <x-flash-alerts />

            <!-- Top Section: KPI & Available Stock Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Biaya Belanja OVK</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                                Rp {{ number_format($totalSpending, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400 dark:text-slate-500">
                        <span>Akumulasi dana pengadaan obat, vaksin &amp; desinfektan</span>
                    </div>
                </div>

                <!-- Sisa Stok OVK Tersedia per Item -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Sisa Stok OVK Tersedia di Gudang</h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ ($stockBalances ?? collect())->count() }} jenis item</span>
                    </div>
                    @if(($stockBalances ?? collect())->isEmpty())
                        <p class="text-xs text-slate-400 dark:text-slate-500 py-4">Belum ada stok OVK tercatat.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-36 overflow-y-auto pr-1">
                            @foreach($stockBalances as $stk)
                                <div class="px-3.5 py-2.5 rounded-xl border {{ $stk->balance > 0 ? 'bg-slate-50 dark:bg-slate-900/60 border-slate-200 dark:border-slate-700' : 'bg-rose-50/70 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/70' }} flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" title="{{ $stk->item_name }}">{{ $stk->item_name }}</div>
                                        <div class="text-[10px] uppercase text-slate-400">{{ $stk->category }}</div>
                                    </div>
                                    <span class="shrink-0 text-xs font-extrabold tabular-nums {{ $stk->balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ number_format((float) $stk->balance, 1, ',', '.') }} {{ $stk->unit }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Middle Section: 2-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Form Catat Transaksi OVK -->
                <div x-data="{ txType: '{{ old('transaction_type', 'in') }}' }" class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 lg:col-span-1 h-fit">
                    <div class="pb-4 mb-5 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Catat Transaksi OVK</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Input pembelian masuk gudang atau pemakaian keluar ke kandang</p>
                    </div>

                    <form action="{{ route('ovk.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Tanggal -->
                        <div>
                            <label for="record_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Tanggal Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="record_date" name="record_date" value="{{ old('record_date', date('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label for="item_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama Barang OVK <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="item_name" name="item_name" value="{{ old('item_name') }}" placeholder="misal: Vita Stress, ND Hitchner" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>

                        <!-- Kategori & Tipe Transaksi -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="category" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Kategori <span class="text-rose-500">*</span>
                                </label>
                                <select id="category" name="category" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="obat" {{ old('category') === 'obat' ? 'selected' : '' }}>Obat-Obatan</option>
                                    <option value="vaksin" {{ old('category') === 'vaksin' ? 'selected' : '' }}>Vaksin</option>
                                    <option value="kimia" {{ old('category') === 'kimia' ? 'selected' : '' }}>Kimia / Desinfektan</option>
                                </select>
                            </div>

                            <div>
                                <label for="transaction_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Jenis <span class="text-rose-500">*</span>
                                </label>
                                <select id="transaction_type" x-model="txType" name="transaction_type" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="in">Masuk (Belanja)</option>
                                    <option value="out">Keluar (Pakai)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tujuan Kandang (Jika Keluar / Pakai) -->
                        <div x-show="txType === 'out'" x-cloak>
                            <label for="flock_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Tujuan Kandang Pemakai
                            </label>
                            <select id="flock_id" name="flock_id" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">-- Umum / Seluruh Unit --</option>
                                @foreach($activeFlocks ?? [] as $activeFlock)
                                    <option value="{{ $activeFlock->id }}" {{ old('flock_id') == $activeFlock->id ? 'selected' : '' }}>
                                        {{ $activeFlock->name }}{{ $activeFlock->coop ? ' (' . $activeFlock->coop->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity & Satuan -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="quantity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Jumlah <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01" id="quantity" name="quantity" value="{{ old('quantity') }}" placeholder="10" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>

                            <div>
                                <label for="unit" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Satuan <span class="text-rose-500">*</span>
                                </label>
                                <select id="unit" name="unit" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="botol" {{ old('unit') === 'botol' ? 'selected' : '' }}>Botol</option>
                                    <option value="sachet" {{ old('unit') === 'sachet' ? 'selected' : '' }}>Sachet</option>
                                    <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="gram" {{ old('unit') === 'gram' ? 'selected' : '' }}>Gram</option>
                                    <option value="pcs" {{ old('unit') === 'pcs' ? 'selected' : '' }}>Pcs</option>
                                </select>
                            </div>
                        </div>

                        <!-- Total Price (Hanya jika Masuk) -->
                        <div x-show="txType === 'in'">
                            <label for="total_price" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Total Harga Belanja (Rp)
                            </label>
                            <input type="number" min="0" id="total_price" name="total_price" value="{{ old('total_price') }}" placeholder="150000" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Keterangan (Opsional)
                            </label>
                            <textarea id="description" name="description" rows="2" placeholder="Catatan dosis / supplier..." class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('description') }}</textarea>
                        </div>

                        <label x-show="txType === 'in'" for="ovk_sync_cash_flow" class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <input id="ovk_sync_cash_flow" type="checkbox" name="sync_cash_flow" value="1" {{ old('sync_cash_flow', '1') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                <strong class="block text-slate-800 dark:text-slate-100">Catat otomatis ke Arus Kas</strong>
                                Otomatis mengurangi saldo kas saat mencatat pembelian (Masuk) dengan total harga &gt; Rp 0.
                            </span>
                        </label>

                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md transition-colors">
                                Simpan Transaksi OVK
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Recent Transaction History Table -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Riwayat Mutasi OVK</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Daftar barang masuk gudang dan pemakaian per kandang</p>
                        </div>
                    </div>

                    <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative border-t border-slate-200 dark:border-slate-700">
                        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Tanggal</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Nama Barang &amp; Tujuan</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Kategori</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Tipe</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5">Jumlah</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 px-6 py-3.5 text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700">
                                @forelse ($records as $record)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                            <div>{{ $record->item_name }}</div>
                                            @if ($record->flock)
                                                <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-0.5">
                                                    📍 {{ $record->flock->name }}
                                                </div>
                                            @endif
                                            @if ($record->description)
                                                <div class="text-xs font-normal text-slate-400 dark:text-slate-400 mt-0.5">{{ $record->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($record->category === 'obat')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    Obat
                                                </span>
                                            @elseif ($record->category === 'vaksin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    Vaksin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    Kimia
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($record->transaction_type === 'in')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    + Masuk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                    - Keluar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                            {{ number_format($record->quantity, 2, ',', '.') }} {{ $record->unit }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                            @if ($record->total_price)
                                                Rp {{ number_format($record->total_price, 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500 font-normal">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                            <p class="font-medium text-slate-500 dark:text-slate-400">Belum Ada Transaksi OVK</p>
                                            <p class="text-xs mt-1">Gunakan formulir di samping untuk mencatat obat, vaksin, atau kimia.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($records, 'links'))
                        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                            {{ $records->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
