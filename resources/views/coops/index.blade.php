<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Master Unit & Lokasi Kandang
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola identitas lokasi kandang, penanggung jawab unit, serta pengaturan Kop Surat Dinamis laporan cetak A4</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        createModalOpen: false,
        editModalOpen: false,
        editFormAction: '',
        editCoop: {
            id: null,
            name: '',
            tagline: '',
            address: '',
            phone: '',
            email: '',
            manager_name: '',
            is_headquarters: false
        },
        openEditModal(coop) {
            this.editCoop = {
                id: coop.id,
                name: coop.name,
                tagline: coop.tagline || '',
                address: coop.address,
                phone: coop.phone || '',
                email: coop.email || '',
                manager_name: coop.manager_name,
                is_headquarters: Boolean(coop.is_headquarters)
            };
            this.editFormAction = coop.update_url;
            this.editModalOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Notification -->
            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 shadow-md">
                    <div class="text-sm font-bold mb-1">Terdapat kesalahan input:</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 2-Column Layout (Consistent with workers/index.blade.php) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Form Tambah Unit Kandang Baru -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 lg:col-span-1 h-fit">
                    <div class="pb-4 mb-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Tambah Unit Kandang</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Daftarkan lokasi kandang & identitas kop surat cetak</p>
                        </div>
                        <button type="button" @click="createModalOpen = true" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-xl border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition" title="Buka Form di Modal">
                            ⤢ Modal
                        </button>
                    </div>

                    <form action="{{ route('coops.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama Unit / Kandang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="misal: Kandang Hanau" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="tagline" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Subjudul / Tagline Kop Surat
                            </label>
                            <input type="text" id="tagline" name="tagline" value="{{ old('tagline') }}" placeholder="misal: Unit Produksi Broiler & Pusat Distribusi" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="address" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alamat Lengkap Lokasi <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="address" name="address" rows="2" required placeholder="Desa Pembuang Hulu I, Kec. Hanau, Kab. Seruyan, Kalimantan Tengah" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    No. Telepon
                                </label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0812-xxxx-xxxx" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Email Unit
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="hanau@smartcoop.id" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="manager_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Kepala Unit / Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="manager_name" name="manager_name" value="{{ old('manager_name') }}" placeholder="Nama pejabat penandatangan laporan" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="pt-1">
                            <label class="inline-flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs text-slate-700 dark:text-slate-300">
                                    <strong class="font-semibold">Jadikan Kop Surat Kantor Pusat (HQ)</strong><br>
                                    <span class="text-slate-500 dark:text-slate-400">Digunakan otomatis untuk laporan rekapitulasi global (Arus Kas & Audit Gudang Pakan).</span>
                                </span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md transition-colors">
                                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Simpan Unit Kandang
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Unit Lokasi Kandang -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Unit & Lokasi Kandang Aktif</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Setiap unit memiliki konfigurasi kop surat dan penandatangan laporan tersendiri</p>
                        </div>
                        <button type="button" @click="createModalOpen = true" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                            + Tambah Unit Baru
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5">Unit & Alamat Kop Surat</th>
                                    <th scope="col" class="px-5 py-3.5">Kepala Unit / TTD</th>
                                    <th scope="col" class="px-5 py-3.5">Status & Siklus</th>
                                    <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700">
                                @forelse ($coops as $coop)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <span>🐓 {{ $coop->name }}</span>
                                            </div>
                                            @if ($coop->tagline)
                                                <div class="text-xs font-medium text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $coop->tagline }}</div>
                                            @endif
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $coop->address }}</div>
                                            @if ($coop->phone || $coop->email)
                                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                                    {{ $coop->phone ? 'Telp: ' . $coop->phone : '' }}
                                                    {{ ($coop->phone && $coop->email) ? ' | ' : '' }}
                                                    {{ $coop->email ? 'Email: ' . $coop->email : '' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $coop->manager_name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Kepala Unit / Penanggung Jawab</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-col gap-1.5">
                                                @if ($coop->is_headquarters)
                                                    <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        🏛️ Kantor Pusat (Kop Global)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        📍 Unit Cabang / Plasma
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                                    🏠 {{ $coop->flocks_count }} Siklus Terhubung
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button"
                                                    @click="openEditModal({
                                                        id: {{ $coop->id }},
                                                        name: @js($coop->name),
                                                        tagline: @js($coop->tagline),
                                                        address: @js($coop->address),
                                                        phone: @js($coop->phone),
                                                        email: @js($coop->email),
                                                        manager_name: @js($coop->manager_name),
                                                        is_headquarters: @js($coop->is_headquarters),
                                                        update_url: @js(route('coops.update', $coop))
                                                    })"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-xl transition-colors border border-indigo-200 dark:border-indigo-800">
                                                    ✏️ Edit
                                                </button>

                                                <form action="{{ route('coops.destroy', $coop) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit lokasi kandang ini? Siklus yang terhubung akan dilepas dari unit ini.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 dark:bg-rose-900/40 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 text-xs font-semibold rounded-xl transition-colors border border-rose-200 dark:border-rose-800">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                            <p class="font-medium text-slate-500">Belum Ada Data Unit Kandang</p>
                                            <p class="text-xs mt-1">Tambahkan unit lokasi kandang untuk mengaktifkan kop surat dinamis.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tambah Unit Kandang (Alpine.js) -->
        <div x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak style="display: none;">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="createModalOpen = false"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-700">

                    <div class="px-6 py-4 bg-indigo-600 dark:bg-indigo-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>➕</span> Tambah Unit & Lokasi Kandang Baru
                        </h3>
                        <button type="button" @click="createModalOpen = false" class="text-indigo-200 hover:text-white focus:outline-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('coops.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama Unit / Kandang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" required placeholder="Contoh: Kandang Hanau" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Subjudul / Tagline Kop Surat
                            </label>
                            <input type="text" name="tagline" placeholder="Contoh: Peternakan Ayam Broiler Modern & Kemitraan" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alamat Lengkap Lokasi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="address" rows="2" required placeholder="Alamat lengkap unit kandang..." class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    No. Telepon
                                </label>
                                <input type="text" name="phone" placeholder="0812-xxxx-xxxx" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Email Unit
                                </label>
                                <input type="email" name="email" placeholder="unit@smartcoop.id" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Kepala Unit / Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="manager_name" required placeholder="Nama pejabat penandatangan laporan" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_headquarters" value="1" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Jadikan Kop Surat Kantor Pusat (HQ) untuk Laporan Global</span>
                            </label>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition">
                                Simpan Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Unit Kandang (Alpine.js) -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak style="display: none;">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="editModalOpen = false"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-700">

                    <div class="px-6 py-4 bg-indigo-600 dark:bg-indigo-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>✏️</span> Edit Unit & Lokasi Kandang
                        </h3>
                        <button type="button" @click="editModalOpen = false" class="text-indigo-200 hover:text-white focus:outline-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="editFormAction" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama Unit / Kandang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="editCoop.name" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Subjudul / Tagline Kop Surat
                            </label>
                            <input type="text" name="tagline" x-model="editCoop.tagline" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alamat Lengkap Lokasi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="address" rows="2" x-model="editCoop.address" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    No. Telepon
                                </label>
                                <input type="text" name="phone" x-model="editCoop.phone" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Email Unit
                                </label>
                                <input type="email" name="email" x-model="editCoop.email" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Kepala Unit / Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="manager_name" x-model="editCoop.manager_name" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_headquarters" value="1" x-model="editCoop.is_headquarters" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Jadikan Kop Surat Kantor Pusat (HQ) untuk Laporan Global</span>
                            </label>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
