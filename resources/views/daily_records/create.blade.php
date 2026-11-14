<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Catat Harian: {{ $flock->name }}
            </h2>
            <a href="{{ route('flocks.show', $flock) }}" class="text-sm text-slate-600 hover:text-slate-900">
                &larr; Kembali ke Detail Kandang
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-md sm:rounded-xl">
                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('daily_records.store', $flock) }}" class="space-y-6">
                        @csrf

                        <!-- Tanggal Catatan -->
                        <div>
                            <x-input-label for="record_date" :value="__('Tanggal')" />
                            <x-text-input id="record_date" class="block mt-1 w-full" type="date" name="record_date" :value="old('record_date', date('Y-m-d'))" required autofocus />
                            <x-input-error :messages="$errors->get('record_date')" class="mt-2" />
                        </div>

                        <!-- Kematian (Mortality) -->
                        <div>
                            <x-input-label for="mortality" :value="__('Jumlah Kematian / Mortality (Ekor)')" />
                            <x-text-input id="mortality" class="block mt-1 w-full" type="number" name="mortality" :value="old('mortality', 0)" required min="0" />
                            <x-input-error :messages="$errors->get('mortality')" class="mt-2" />
                        </div>

                        <!-- Afkir (Culling) -->
                        <div>
                            <x-input-label for="culling" :value="__('Jumlah Afkir / Culling (Ekor)')" />
                            <x-text-input id="culling" class="block mt-1 w-full" type="number" name="culling" :value="old('culling', 0)" required min="0" />
                            <x-input-error :messages="$errors->get('culling')" class="mt-2" />
                        </div>

                        <!-- Konsumsi Pakan (Kg) -->
                        <div>
                            <x-input-label for="feed_consumed_kg" :value="__('Konsumsi Pakan (Kg)')" />
                            <x-text-input id="feed_consumed_kg" class="block mt-1 w-full" type="number" step="0.01" name="feed_consumed_kg" :value="old('feed_consumed_kg', '0.00')" required min="0" />
                            <x-input-error :messages="$errors->get('feed_consumed_kg')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button class="ms-4">
                                {{ __('Simpan Catatan Harian') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>