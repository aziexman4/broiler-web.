<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.18.12l-.3.12a2 2 0 00-1.02 2.58l.64 1.54a2 2 0 002.58 1.02l.3-.12a2 2 0 001.18-.12l2.38.48a6 6 0 003.86-.52l.32-.16a6 6 0 013.86-.52l2.38.48a2 2 0 001.18-.12l.3-.12a2 2 0 001.02-2.58l-.64-1.54z" />
                    </svg>
                    Manajemen Stok OVK (Obat, Vaksin & Kimia)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Pencatatan inventaris pembelian dan pemakaian obat-obatan peternakan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Notification -->

            <!-- Top Section: KPI Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 relative overflow-hidden md:col-span-1">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full opacity-60 pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Biaya Belanja OVK</p>
                            <h3 class="text-2xl font-extrabold text-blue-600 mt-2">
                                Rp {{ number_format($totalSpending, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-100 text-blue-600 rounded-2xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-slate-400">
                        <span>Akumulasi dana pengadaan obat & vaksin</span>
                    </div>
                </div>
            </div>

            <!-- Middle Section: 2-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Form Catat Transaksi OVK -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 lg:col-span-1">
                    <div class="pb-4 mb-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Catat Transaksi OVK</h3>
                        <p class="text-xs text-slate-500">Input transaksi pembelian barang masuk atau pemakaian keluar</p>
                    </div>

                    <form action="{{ route('ovk.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Tanggal -->
                        <div>
                            <label for="record_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="record_date" name="record_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label for="item_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nama Barang OVK <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="item_name" name="item_name" placeholder="misal: Vita Stress, ND Hitchner" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label for="category" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select id="category" name="category" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="obat">Obat-Obatan</option>
                                <option value="vaksin">Vaksin</option>
                                <option value="kimia">Kimia / Desinfektan</option>
                            </select>
                        </div>

                        <!-- Tipe Transaksi -->
                        <div>
                            <label for="transaction_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Jenis Transaksi <span class="text-rose-500">*</span>
                            </label>
                            <select id="transaction_type" name="transaction_type" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="in">Masuk (Stock In / Belanja)</option>
                                <option value="out">Keluar (Stock Out / Pakai)</option>
                            </select>
                        </div>

                        <!-- Quantity & Satuan -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="quantity" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Jumlah <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" id="quantity" name="quantity" placeholder="10" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            <div>
                                <label for="unit" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Satuan <span class="text-rose-500">*</span>
                                </label>
                                <select id="unit" name="unit" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="botol">Botol</option>
                                    <option value="sachet">Sachet</option>
                                    <option value="liter">Liter</option>
                                    <option value="gram">Gram</option>
                                    <option value="pcs">Pcs</option>
                                </select>
                            </div>
                        </div>

                        <!-- Total Price -->
                        <div>
                            <label for="total_price" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Total Harga Belanja (Rp) <span class="text-slate-400 font-normal">(Hanya jika Barang Masuk)</span>
                            </label>
                            <input type="number" id="total_price" name="total_price" placeholder="150000" class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Keterangan (Opsional)
                            </label>
                            <textarea id="description" name="description" rows="2" placeholder="Catatan dosis / supplier..." class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md transition-colors">
                                Simpan Transaksi OVK
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Recent Transaction History Table -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Riwayat Mutasi OVK</h3>
                            <p class="text-xs text-slate-500">Daftar barang masuk dan keluar inventaris OVK</p>
                        </div>
                    </div>

                    <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Tanggal</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Nama Barang</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Kategori</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Tipe</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5">Jumlah</th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3.5 text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70">
                                @forelse ($records as $record)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">
                                            {{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-slate-800">
                                            {{ $record->item_name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($record->category === 'obat')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                    Obat
                                                </span>
                                            @elseif ($record->category === 'vaksin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                    Vaksin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Kimia
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($record->transaction_type === 'in')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    + Masuk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-700">
                                                    - Keluar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-medium">
                                            {{ number_format($record->quantity, 2, ',', '.') }} {{ $record->unit }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold">
                                            @if ($record->total_price)
                                                Rp {{ number_format($record->total_price, 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-400 font-normal">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.18.12l-.3.12a2 2 0 00-1.02 2.58l.64 1.54a2 2 0 002.58 1.02l.3-.12a2 2 0 001.18-.12l2.38.48a6 6 0 003.86-.52l.32-.16a6 6 0 013.86-.52l2.38.48a2 2 0 001.18-.12l.3-.12a2 2 0 001.02-2.58l-.64-1.54z" />
                                            </svg>
                                            <p class="font-medium text-slate-500">Belum Ada Transaksi OVK</p>
                                            <p class="text-xs mt-1">Gunakan formulir di samping untuk mencatat obat, vaksin, atau kimia.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
