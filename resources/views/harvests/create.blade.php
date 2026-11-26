<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                Input Data Panen: {{ $flock->name }}
            </h2>
            <a href="{{ route('flocks.show', $flock) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                &larr; Kembali ke Detail Kandang
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 text-slate-900 dark:text-slate-100"
                     x-data="{ closeFlock: {{ old('close_flock', '1') ? 'true' : 'false' }}, maxBirds: {{ (int) $livePopulation }}, chickensSold: {{ (int) old('chickens_sold', $livePopulation) }} }">

                    <!-- Info Alert -->
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-900 dark:text-emerald-200 space-y-1">
                        <div>
                            <strong>Populasi Tersedia Dipanen: {{ number_format($livePopulation) }} ekor</strong>
                            @if(($alreadyHarvested ?? 0) > 0)
                                <span class="text-xs">({{ number_format($alreadyHarvested) }} ekor sudah dipanen pada penjarangan sebelumnya)</span>
                            @endif
                        </div>
                        <p class="text-xs text-emerald-800/90 dark:text-emerald-300/90">
                            Anda dapat melakukan <strong>Panen Akhir (Tutup Buku)</strong> seluruh sisa ayam, atau melepas centang tutup buku untuk mencatat <strong>Panen Penjarangan (Thinning)</strong> sebagian populasi.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('harvests.store', $flock) }}" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Tanggal Panen -->
                            <div>
                                <x-input-label for="harvest_date" :value="__('Tanggal Panen')" />
                                <x-text-input id="harvest_date" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="date" name="harvest_date" :value="old('harvest_date', date('Y-m-d'))" required autofocus />
                                <x-input-error :messages="$errors->get('harvest_date')" class="mt-2" />
                            </div>

                            <!-- Jumlah Ayam Terjual -->
                            <div>
                                <x-input-label for="chickens_sold" :value="__('Jumlah Ayam Dipanen (Ekor)')" />
                                <input id="chickens_sold" x-model.number="chickensSold" type="number" name="chickens_sold" value="{{ old('chickens_sold', $livePopulation) }}" min="1" max="{{ max(1, $livePopulation) }}" required
                                    class="block mt-1 w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                                <x-input-error :messages="$errors->get('chickens_sold')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Total Berat (Kg) -->
                        <div>
                            <x-input-label for="total_weight_kg" :value="__('Total Berat Timbangan Panen (Kg)')" />
                            <x-text-input id="total_weight_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="total_weight_kg" :value="old('total_weight_kg')" required min="0.01" placeholder="Contoh: 8500.50" />
                            <x-input-error :messages="$errors->get('total_weight_kg')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Harga Ayam per Kg -->
                            <div>
                                <x-input-label for="chicken_price_per_kg" :value="__('Harga Jual Ayam per Kg (Rp)')" />
                                <x-text-input id="chicken_price_per_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="chicken_price_per_kg" :value="old('chicken_price_per_kg', $defaultMarketPrice ?? 23000)" required min="0" />
                                <x-input-error :messages="$errors->get('chicken_price_per_kg')" class="mt-2" />
                            </div>

                            <!-- Harga Pakan per Kg -->
                            <div>
                                <x-input-label for="feed_price_per_kg" :value="__('Harga Beli Pakan per Kg (Rp)')" />
                                <x-text-input id="feed_price_per_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="feed_price_per_kg" :value="old('feed_price_per_kg', $defaultFeedPrice ?? 9000)" required min="0" />
                                <x-input-error :messages="$errors->get('feed_price_per_kg')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Nama Pembeli -->
                        <div>
                            <x-input-label for="buyer_name" :value="__('Nama Pembeli / Tengkulak')" />
                            <x-text-input id="buyer_name" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="text" name="buyer_name" :value="old('buyer_name')" required placeholder="Contoh: PT Poultry Jaya / H. Ahmad" />
                            <x-input-error :messages="$errors->get('buyer_name')" class="mt-2" />
                        </div>

                        <!-- Opsi Panen Akhir vs Panen Penjarangan -->
                        <label for="close_flock" class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <input type="hidden" name="close_flock" value="0">
                            <input id="close_flock" x-model="closeFlock" @change="if (closeFlock) chickensSold = maxBirds" type="checkbox" name="close_flock" value="1" class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                <strong class="block text-slate-900 dark:text-slate-100">Sekaligus Tutup Buku Siklus Ini (Panen Akhir)</strong>
                                Biarkan tercentang untuk panen kuras kandang. Lepas centang jika baru melakukan <strong>panen penjarangan (parsial)</strong> agar kandang tetap berstatus <strong>Active</strong>.
                            </span>
                        </label>

                        <div class="flex items-center justify-end pt-2">
                            <x-primary-button class="bg-emerald-600 hover:bg-emerald-700 rounded-xl">
                                <span x-text="closeFlock ? 'Proses Panen Akhir & Tutup Kandang' : 'Simpan Panen Penjarangan (Tetap Aktif)'">Proses &amp; Tutup Kandang</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
