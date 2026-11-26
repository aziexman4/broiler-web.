<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                Tambah Data Panen (Penutupan Kandang): {{ $flock->name }}
            </h2>
            <a href="{{ route('flocks.show', $flock) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                &larr; Kembali ke Detail Kandang
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 text-slate-900 dark:text-slate-100">
                    <!-- Info Alert -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl text-sm text-blue-800 dark:text-blue-200">
                        <strong>Otomatisasi Sistem:</strong> Jumlah ayam panen akan dihitung otomatis dari populasi hidup saat ini (<strong>{{ number_format($livePopulation) }} ekor</strong>). Menyimpan panen ini akan otomatis menghitung Total Pendapatan, Biaya Pakan, Laba Bersih, mencatat kas masuk, dan mengubah status kandang menjadi <strong>Closed</strong>.
                    </div>

                    <form method="POST" action="{{ route('harvests.store', $flock) }}" class="space-y-6">
                        @csrf

                        <!-- Tanggal Panen -->
                        <div>
                            <x-input-label for="harvest_date" :value="__('Tanggal Panen')" />
                            <x-text-input id="harvest_date" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="date" name="harvest_date" :value="old('harvest_date', date('Y-m-d'))" required autofocus />
                            <x-input-error :messages="$errors->get('harvest_date')" class="mt-2" />
                        </div>

                        <!-- Total Berat (Kg) -->
                        <div>
                            <x-input-label for="total_weight_kg" :value="__('Total Berat Timbangan Panen (Kg)')" />
                            <x-text-input id="total_weight_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="total_weight_kg" :value="old('total_weight_kg')" required min="0.01" placeholder="Contoh: 8500.50" />
                            <x-input-error :messages="$errors->get('total_weight_kg')" class="mt-2" />
                        </div>

                        <!-- Harga Ayam per Kg -->
                        <div>
                            <x-input-label for="chicken_price_per_kg" :value="__('Harga Jual Ayam per Kg (Rp)')" />
                            <x-text-input id="chicken_price_per_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="chicken_price_per_kg" :value="old('chicken_price_per_kg')" required min="0" placeholder="Contoh: 21500" />
                            <x-input-error :messages="$errors->get('chicken_price_per_kg')" class="mt-2" />
                        </div>

                        <!-- Harga Pakan per Kg -->
                        <div>
                            <x-input-label for="feed_price_per_kg" :value="__('Harga Beli Pakan per Kg (Rp)')" />
                            <x-text-input id="feed_price_per_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="feed_price_per_kg" :value="old('feed_price_per_kg')" required min="0" placeholder="Contoh: 8500" />
                            <x-input-error :messages="$errors->get('feed_price_per_kg')" class="mt-2" />
                        </div>

                        <!-- Nama Pembeli -->
                        <div>
                            <x-input-label for="buyer_name" :value="__('Nama Pembeli / Tengkulak')" />
                            <x-text-input id="buyer_name" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="text" name="buyer_name" :value="old('buyer_name')" required placeholder="Contoh: PT Poultry Jaya / H. Ahmad" />
                            <x-input-error :messages="$errors->get('buyer_name')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button class="ms-4 bg-emerald-600 hover:bg-emerald-700">
                                {{ __('Proses & Tutup Kandang') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
