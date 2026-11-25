<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
                {{ __('Tambah Kandang Baru') }}
            </h2>
            <a href="{{ route('flocks.index') }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                &larr; Kembali ke Daftar Kandang
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-md sm:rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 text-slate-900 dark:text-slate-100">
                    <form method="POST" action="{{ route('flocks.store') }}" class="space-y-6">
                        @csrf

                        <!-- Unit / Lokasi Kandang -->
                        <div>
                            <x-input-label for="coop_id" :value="__('Unit / Lokasi Kandang')" />
                            <select id="coop_id" name="coop_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Unit / Lokasi Kandang --</option>
                                @foreach($coops as $coop)
                                    <option value="{{ $coop->id }}" {{ old('coop_id') == $coop->id ? 'selected' : '' }}>
                                        {{ $coop->name }} ({{ $coop->manager_name }}){{ $coop->is_headquarters ? ' — [Kantor Pusat]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('coop_id')" class="mt-2" />
                        </div>

                        <!-- Nama Kandang -->
                        <div>
                            <x-input-label for="name" :value="__('Nama Kandang')" />
                            <x-text-input id="name" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Kandang A1" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Ras / Breed -->
                        <div>
                            <x-input-label for="breed" :value="__('Ras Ayam (Breed)')" />
                            <x-text-input id="breed" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="text" name="breed" :value="old('breed')" required placeholder="Contoh: Cobb 500 / Ross 308" />
                            <x-input-error :messages="$errors->get('breed')" class="mt-2" />
                        </div>

                        <!-- Tugaskan Anak Kandang (Worker) -->
                        @if(Auth::user()->isOwner())
                        <div>
                            <x-input-label for="worker_id" :value="__('Tugaskan Anak Kandang (Worker)')" />
                            <select id="worker_id" name="worker_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Pekerja Penanggung Jawab --</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ old('worker_id') == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }} ({{ $worker->email }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('worker_id')" class="mt-2" />
                        </div>
                        @endif

                        <!-- Tanggal Mulai -->
                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai (Chick In)')" />
                            <x-text-input id="start_date" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="date" name="start_date" :value="old('start_date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <!-- Populasi Awal -->
                        <div>
                            <x-input-label for="initial_population" :value="__('Populasi Awal (Ekor)')" />
                            <x-text-input id="initial_population" class="block mt-1 w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" type="number" name="initial_population" :value="old('initial_population')" required min="1" placeholder="Contoh: 5000" />
                            <x-input-error :messages="$errors->get('initial_population')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <a href="{{ route('flocks.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-md font-semibold text-xs text-gray-700 dark:text-slate-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-slate-600 transition">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Kandang') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
