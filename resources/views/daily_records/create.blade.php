<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                    Catat Harian: {{ $flock->name }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Chick-in: {{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }} &bull;
                    Populasi Awal: {{ number_format($flock->initial_population) }} ekor
                </p>
            </div>
            <a href="{{ route('flocks.show', $flock) }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                &larr; Kembali ke Detail Kandang
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-alerts />

            <!-- Sisa Stok Pakan Kandang Saat Ini -->
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 mb-2">
                    Sisa Stok Gudang Kandang Saat Ini
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-200 dark:bg-amber-800 px-2.5 py-1 rounded-lg">
                        Pre-Starter: {{ number_format($flock->stock_pre_starter_kg ?? 0, 2) }} Kg
                    </span>
                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-300 dark:bg-amber-700 px-2.5 py-1 rounded-lg">
                        Starter: {{ number_format($flock->stock_starter_kg ?? 0, 2) }} Kg
                    </span>
                    <span class="text-xs font-bold text-amber-900 dark:text-amber-100 bg-amber-400 dark:bg-amber-600 px-2.5 py-1 rounded-lg">
                        Finisher: {{ number_format($flock->stock_finisher_kg ?? 0, 2) }} Kg
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 text-slate-900 dark:text-slate-100">
                    <form method="POST" action="{{ route('daily_records.store', $flock) }}" class="space-y-6">
                        @csrf

                        <!-- Tanggal Catatan -->
                        <div>
                            <x-input-label for="record_date" :value="__('Tanggal Catatan')" />
                            <x-text-input
                                id="record_date"
                                class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                                type="date"
                                name="record_date"
                                min="{{ \Carbon\Carbon::parse($flock->start_date)->format('Y-m-d') }}"
                                :value="old('record_date', date('Y-m-d'))"
                                required
                                autofocus
                            />
                            <x-input-error :messages="$errors->get('record_date')" class="mt-2" />
                        </div>

                        <!-- Kematian & Afkir -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="mortality" :value="__('Jumlah Kematian / Mortality (Ekor)')" />
                                <x-text-input id="mortality" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" name="mortality" :value="old('mortality', 0)" required min="0" />
                                <x-input-error :messages="$errors->get('mortality')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="culling" :value="__('Jumlah Afkir / Culling (Ekor)')" />
                                <x-text-input id="culling" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" name="culling" :value="old('culling', 0)" required min="0" />
                                <x-input-error :messages="$errors->get('culling')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Konsumsi Pakan per Fase (Kg) -->
                        <div class="space-y-4">
                            <div class="text-sm font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-1">
                                Konsumsi Pakan per Fase (Kg)
                            </div>

                            <div>
                                <x-input-label for="feed_pre_starter_kg" :value="__('Pakan Pre-Starter (Kg)')" />
                                <x-text-input id="feed_pre_starter_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="feed_pre_starter_kg" :value="old('feed_pre_starter_kg', '0')" required min="0" />
                                <x-input-error :messages="$errors->get('feed_pre_starter_kg')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="feed_starter_kg" :value="__('Pakan Starter (Kg)')" />
                                <x-text-input id="feed_starter_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="feed_starter_kg" :value="old('feed_starter_kg', '0')" required min="0" />
                                <x-input-error :messages="$errors->get('feed_starter_kg')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="feed_finisher_kg" :value="__('Pakan Finisher (Kg)')" />
                                <x-text-input id="feed_finisher_kg" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" step="0.01" name="feed_finisher_kg" :value="old('feed_finisher_kg', '0')" required min="0" />
                                <x-input-error :messages="$errors->get('feed_finisher_kg')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Bobot Rata-rata Sampling -->
                        <div>
                            <x-input-label for="average_weight_gram" :value="__('Bobot Rata-rata Sampling (Gram/Ekor, Opsional)')" />
                            <x-text-input id="average_weight_gram" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" name="average_weight_gram" :value="old('average_weight_gram')" min="0" placeholder="Contoh: 850" />
                            <x-input-error :messages="$errors->get('average_weight_gram')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <a href="{{ route('flocks.show', $flock) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-md font-semibold text-xs text-gray-700 dark:text-slate-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-slate-600 transition">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Catatan Harian') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
