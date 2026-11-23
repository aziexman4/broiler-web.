<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                {{ __('Modul Manajemen Gudang Pakan') }}
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Real-time Feed Inventory &amp; Auto-Deduction</span>
        </div>
    </x-slot>

    @php
        $feedIndexUrl = request()->routeIs('feed.index') ? route('feed.index') : route('feeds.index');
        $isFiltered = isset($activeFilterType) && $activeFilterType !== 'all';
    @endphp

    <div class="py-12" x-data="feedInventoryDashboard()" @kpi-updated.window="updateKPIs($event.detail)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success & Error Alert Notifications -->
            <x-flash-alerts />

            <!-- Filter Bar & Cetak Audit Mutasi Gudang A4 -->
            <x-period-filter-bar
                :action-url="$feedIndexUrl"
                :reset-url="$feedIndexUrl"
                :print-url="route('reports.inventory-audit', request()->only(['filter_mode', 'month', 'start_date', 'end_date']))"
                print-label="Cetak Audit Gudang A4"
                :active-filter-type="$activeFilterType ?? 'all'"
                :period-label="$periodLabel ?? '-'"
                :record-count="method_exists($inventories, 'total') ? $inventories->total() : $inventories->count()"
            />

            <!-- Top KPI Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sisa Stok Pakan Tersedia -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                                {{ $isFiltered ? 'Selisih Mutasi Periode' : 'Sisa Stok Tersedia' }}
                            </span>
                            <div class="mt-2 text-3xl font-black transition-colors duration-500" :class="isCritical ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                                <span x-text="currentStock">{{ number_format($currentStock, 2, ',', '.') }}</span> <span class="text-sm font-normal text-slate-500 dark:text-slate-400">kg</span>
                            </div>
                        </div>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            📦
                        </div>
                    </div>

                    <div class="mt-3 flex items-center text-xs font-bold text-rose-600 dark:text-rose-300 bg-rose-50 dark:bg-rose-900/40 px-2.5 py-1 rounded border border-rose-200 dark:border-rose-800 transition-opacity duration-300" x-show="isCritical" x-cloak>
                        ⚠️ PERINGATAN: Stok pakan kritis (&lt; 500 kg)! Segera lakukan restok.
                    </div>
                    <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 transition-opacity duration-300" x-show="!isCritical">
                        Status Stok: <strong class="text-emerald-700 dark:text-emerald-400">Aman / Cukup</strong>
                    </div>
                </div>

                <!-- Total Pakan Masuk (In) -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Pakan Masuk (Restok)</span>
                            <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">
                                <span x-text="totalIn">{{ number_format($totalIn, 2, ',', '.') }}</span> <span class="text-sm font-normal text-slate-500 dark:text-slate-400">kg</span>
                            </div>
                        </div>
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            📥
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">Akumulasi pengadaan pakan</div>
                </div>

                <!-- Total Pakan Keluar (Out) -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Pakan Terpakai (Out)</span>
                            <div class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-400">
                                <span x-text="totalOut">{{ number_format($totalOut, 2, ',', '.') }}</span> <span class="text-sm font-normal text-slate-500 dark:text-slate-400">kg</span>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-xl">
                            📤
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">Termasuk auto-deduct catatan harian</div>
                </div>
            </div>

            <!-- Middle Section: Form & History Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Input Form (1 Column) -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700 h-fit">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 pb-2 border-b border-slate-200 dark:border-slate-700 flex items-center">
                        ➕ Catat Mutasi Pakan Manual
                    </h3>

                    <form method="POST" action="{{ route('feeds.store') }}" class="space-y-4">
                        @csrf

                        <!-- Tanggal Transaksi -->
                        <div>
                            <label for="transaction_date" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input id="transaction_date" type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                                   class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-1" />
                        </div>

                        <!-- Nama Pakan -->
                        <div>
                            <label for="feed_name" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama / Merek Pakan <span class="text-rose-500">*</span>
                            </label>
                            <input id="feed_name" type="text" name="feed_name" value="{{ old('feed_name') }}" required placeholder="Contoh: BR-1 Super / Finisher"
                                   class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('feed_name')" class="mt-1" />
                        </div>

                        <!-- Jenis Transaksi (In / Out) -->
                        <div>
                            <label for="transaction_type" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Tipe Mutasi <span class="text-rose-500">*</span>
                            </label>
                            <select id="transaction_type" name="transaction_type" required
                                    class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="in" {{ old('transaction_type') === 'in' ? 'selected' : '' }}>📥 Masuk / Restok Gudang (In)</option>
                                <option value="out" {{ old('transaction_type') === 'out' ? 'selected' : '' }}>📤 Keluar / Manual Usage (Out)</option>
                            </select>
                            <x-input-error :messages="$errors->get('transaction_type')" class="mt-1" />
                        </div>

                        <!-- Jumlah (Kg) -->
                        <div>
                            <label for="quantity_kg" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Jumlah (Kg) <span class="text-rose-500">*</span>
                            </label>
                            <input id="quantity_kg" type="number" step="0.01" min="0.01" name="quantity_kg" value="{{ old('quantity_kg') }}" required placeholder="Contoh: 500"
                                   class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('quantity_kg')" class="mt-1" />
                        </div>

                        <!-- Harga per Kg -->
                        <div>
                            <label for="price_per_kg" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Harga per Kg (Rp) <span class="text-slate-400 dark:text-slate-500 text-normal">(Opsional)</span>
                            </label>
                            <input id="price_per_kg" type="number" name="price_per_kg" value="{{ old('price_per_kg') }}" placeholder="Contoh: 8800"
                                   class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('price_per_kg')" class="mt-1" />
                        </div>

                        <!-- Peruntukan Kandang (Optional) -->
                        <div>
                            <label for="flock_id" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Terkait Kandang <span class="text-slate-400 dark:text-slate-500 text-normal">(Opsional)</span>
                            </label>
                            <select id="flock_id" name="flock_id"
                                    class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Tidak Terikat Kandang Spesifik --</option>
                                @foreach($flocks as $flock)
                                    <option value="{{ $flock->id }}" {{ old('flock_id') == $flock->id ? 'selected' : '' }}>
                                        {{ $flock->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('flock_id')" class="mt-1" />
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div>
                            <label for="notes" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Catatan / Keterangan
                            </label>
                            <input id="notes" type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Pembelian supplier PT Charoen Pokphand"
                                   class="w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>

                        <label for="feed_sync_cash_flow" class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <input id="feed_sync_cash_flow" type="checkbox" name="sync_cash_flow" value="1" {{ old('sync_cash_flow', '1') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                <strong class="block text-slate-800 dark:text-slate-100">Catat otomatis ke Arus Kas</strong>
                                Otomatis mencatat kas keluar (Kg &times; Harga/Kg) untuk transaksi Pakan Masuk (In).
                            </span>
                        </label>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow transition">
                                Simpan Mutasi Pakan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Mutasi History Table (2 Columns) -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-md border border-slate-200 dark:border-slate-700">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 pb-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2 flex-wrap">
                        <span>📋 Riwayat Mutasi Gudang Pakan</span>
                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">
                            Periode: <strong class="text-slate-700 dark:text-slate-200">{{ $periodLabel ?? '-' }}</strong> ({{ method_exists($inventories, 'total') ? $inventories->total() : $inventories->count() }} transaksi)
                        </span>
                    </h3>

                    @if($inventories->isEmpty())
                        <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-sm">
                            {{ $isFiltered ? 'Belum ada transaksi mutasi pakan pada periode ini.' : 'Belum ada riwayat transaksi mutasi pakan.' }}
                        </div>
                    @else
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left border-collapse text-slate-600 dark:text-slate-300">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-xs font-semibold uppercase tracking-wider">
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Tanggal</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Nama Pakan</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Tipe</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4 text-right">Jumlah (Kg)</th>
                                        <th class="sticky top-0 bg-slate-100 dark:bg-slate-700 z-10 border-b border-slate-200 dark:border-slate-600 py-3 px-4">Kandang / Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                                    @foreach($inventories as $inv)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                            <td class="py-3 px-4 font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($inv->transaction_date)->format('d M Y') }}
                                            </td>
                                            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $inv->feed_name }}
                                            </td>
                                            <td class="py-3 px-4 whitespace-nowrap">
                                                @if($inv->transaction_type === 'in')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-emerald-900/40 text-green-800 dark:text-emerald-300 border border-green-200 dark:border-emerald-800">
                                                        📥 MASUK (IN)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        📤 KELUAR (OUT)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right font-bold whitespace-nowrap {{ $inv->transaction_type === 'in' ? 'text-green-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}"
                                                x-data="inlineEdit({ id: {{ $inv->id }}, field: 'quantity_kg', value: '{{ $inv->quantity_kg }}', displayValue: '{{ number_format($inv->quantity_kg, 2, ',', '.') }}' })">
                                                <div x-show="!editing" @click="startEdit()" class="cursor-pointer group flex items-center justify-end gap-2 relative">
                                                    <span class="opacity-0 group-hover:opacity-100 text-slate-400 text-xs absolute -left-4">✏️</span>
                                                    <span>{{ $inv->transaction_type === 'in' ? '+' : '-' }} <span x-text="displayValue">{{ number_format($inv->quantity_kg, 2, ',', '.') }}</span> kg</span>
                                                </div>
                                                <div x-show="editing" x-cloak class="flex items-center justify-end gap-1">
                                                    <input x-ref="input" x-model="value" @keydown.enter="save()" @keydown.escape="cancel()" @blur="save()" type="number" step="0.01" class="w-24 text-right text-sm border-indigo-300 dark:border-indigo-500 dark:bg-slate-900 dark:text-slate-100 rounded focus:ring-indigo-500 px-2 py-1 h-8">
                                                    <svg x-show="isSaving" class="animate-spin h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300"
                                                x-data="inlineEdit({ id: {{ $inv->id }}, field: 'notes', value: '{{ addslashes($inv->notes) }}', displayValue: '{{ addslashes($inv->notes ?? '-') }}' })">
                                                <div class="font-semibold text-indigo-700 dark:text-indigo-400" x-show="'{{ $inv->flock_id }}' !== ''">
                                                    @if($inv->flock) [{{ $inv->flock->name }}] @endif
                                                </div>
                                                <div x-show="!editing" @click="startEdit()" class="cursor-pointer group flex items-center gap-2 mt-0.5 relative">
                                                    <span x-text="displayValue">{{ $inv->notes ?? '-' }}</span>
                                                    <span class="opacity-0 group-hover:opacity-100 text-slate-400 text-[10px] absolute -right-6">✏️ Edit</span>
                                                </div>
                                                <div x-show="editing" x-cloak class="flex items-center gap-2 mt-0.5">
                                                    <input x-ref="input" x-model="value" @keydown.enter="save()" @keydown.escape="cancel()" @blur="save()" type="text" class="w-full text-xs border-indigo-300 dark:border-indigo-500 dark:bg-slate-900 dark:text-slate-100 rounded focus:ring-indigo-500 px-2 py-1 h-8">
                                                    <svg x-show="isSaving" class="animate-spin h-4 w-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($inventories, 'links'))
                            <div class="mt-4">
                                {{ $inventories->links() }}
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('feedInventoryDashboard', () => ({
                currentStock: '{{ number_format($currentStock, 2, ',', '.') }}',
                totalIn: '{{ number_format($totalIn, 2, ',', '.') }}',
                totalOut: '{{ number_format($totalOut, 2, ',', '.') }}',
                isCritical: {{ $currentStock < 500 ? 'true' : 'false' }},

                updateKPIs(kpis) {
                    this.currentStock = kpis.currentStock;
                    this.totalIn = kpis.totalIn;
                    this.totalOut = kpis.totalOut;
                    this.isCritical = kpis.isCritical;
                }
            }));

            Alpine.data('inlineEdit', (config) => ({
                id: config.id,
                field: config.field,
                value: config.value,
                originalValue: config.value,
                displayValue: config.displayValue,
                editing: false,
                isSaving: false,

                startEdit() {
                    this.editing = true;
                    this.value = this.originalValue;
                    setTimeout(() => {
                        if(this.$refs.input) this.$refs.input.focus();
                    }, 50);
                },

                cancel() {
                    this.editing = false;
                    this.value = this.originalValue;
                },

                async save() {
                    if (this.value == this.originalValue) {
                        this.editing = false;
                        return;
                    }

                    this.isSaving = true;

                    try {
                        const response = await fetch(`/feeds/${this.id}/inline-update`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                field: this.field,
                                value: this.value
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.originalValue = this.value;
                            this.displayValue = data.newValue || this.value;
                            this.editing = false;

                            // Trigger global event to update KPIs
                            this.$dispatch('kpi-updated', data.kpis);

                            // Show success highlight
                            const td = this.$el;
                            const originalBg = td.style.backgroundColor;
                            td.style.transition = 'background-color 1s';
                            td.style.backgroundColor = document.documentElement.classList.contains('dark')
                                ? 'rgba(16, 185, 129, 0.25)'
                                : '#dcfce7';
                            setTimeout(() => td.style.backgroundColor = originalBg, 1500);

                            // Show toast
                            this.$dispatch('notify', { message: 'Data berhasil diperbarui!', type: 'success' });
                        } else {
                            this.$dispatch('notify', { message: data.message || 'Gagal menyimpan data.', type: 'error' });
                            this.cancel();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.$dispatch('notify', { message: 'Terjadi kesalahan jaringan.', type: 'error' });
                        this.cancel();
                    } finally {
                        this.isSaving = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
