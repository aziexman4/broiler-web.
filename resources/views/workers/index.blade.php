<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen Karyawan & Rapor Kinerja (HRD)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola akun pekerja kandang, standar gaji pokok, serta evaluasi kedisiplinan & Indeks Performa (IP)</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        editModalOpen: false,
        editFormAction: '',
        editWorker: {
            id: null,
            name: '',
            email: '',
            base_salary: 0,
            password: ''
        },
        openEditModal(worker) {
            this.editWorker = {
                id: worker.id,
                name: worker.name,
                email: worker.email,
                base_salary: worker.base_salary,
                password: ''
            };
            this.editFormAction = worker.update_url;
            this.editModalOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Error Alert Notification -->
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

            <!-- 2-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Form Tambah Pekerja Baru -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6 lg:col-span-1 h-fit">
                    <div class="pb-4 mb-6 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Tambah Pekerja Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Buat akun login anak kandang dan tentukan standar gaji pokok</p>
                    </div>

                    <form action="{{ route('workers.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="misal: Budi Santoso" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="budi@smartcoop.com" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Password <span class="text-rose-500">* (Min. 8 Karakter)</span>
                            </label>
                            <input type="password" id="password" name="password" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Gaji Pokok -->
                        <div>
                            <label for="base_salary" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Standar Gaji Pokok (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="base_salary" name="base_salary" value="{{ old('base_salary', 2500000) }}" min="0" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md transition-colors">
                                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Tambah Akun Pekerja
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Daftar Pekerja Aktif -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Pekerja & Rapor Produktivitas</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Metrik siklus kandang, kedisiplinan pencatatan, dan rata-rata capaian IP panen</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5">Nama & Email Pekerja</th>
                                    <th scope="col" class="px-5 py-3.5">Gaji Pokok</th>
                                    <th scope="col" class="px-5 py-3.5">Kinerja & Kedisiplinan</th>
                                    <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700">
                                @forelse ($workers as $worker)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    {{ strtoupper(substr($worker->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 dark:text-white">{{ $worker->name }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $worker->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                            Rp {{ number_format($worker->base_salary, 0, ',', '.') }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                                        🏠 {{ $worker->flocks_count }} Siklus ({{ $worker->closed_flocks_count }} Panen)
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800" title="Total Catatan Harian Kandang & Log Aktivitas Sistem">
                                                        📋 {{ number_format($worker->discipline_logs_count) }} Log Disiplin ({{ number_format($worker->daily_records_count) }} Rec)
                                                    </span>
                                                </div>
                                                <div>
                                                    @if ($worker->average_ip >= 400)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            🏆 Rata-rata IP: {{ number_format($worker->average_ip, 2) }} (Sangat Baik)
                                                        </span>
                                                    @elseif ($worker->average_ip >= 350)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                            ⭐ Rata-rata IP: {{ number_format($worker->average_ip, 2) }} (Baik)
                                                        </span>
                                                    @elseif ($worker->average_ip >= 300)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                            ✓ Rata-rata IP: {{ number_format($worker->average_ip, 2) }} (Cukup)
                                                        </span>
                                                    @elseif ($worker->average_ip > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            ⚠️ Rata-rata IP: {{ number_format($worker->average_ip, 2) }} (Kurang)
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                                            Belum ada siklus tutup buku
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button"
                                                    @click="openEditModal({
                                                        id: {{ $worker->id }},
                                                        name: @js($worker->name),
                                                        email: @js($worker->email),
                                                        base_salary: {{ $worker->base_salary }},
                                                        update_url: @js(route('workers.update', $worker))
                                                    })"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-xl transition-colors border border-indigo-200 dark:border-indigo-800">
                                                    ✏️ Edit
                                                </button>

                                                <form action="{{ route('workers.destroy', $worker) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memberhentikan dan menghapus akun pekerja ini?');">
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
                                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <p class="font-medium text-slate-500">Belum Ada Akun Pekerja</p>
                                            <p class="text-xs mt-1">Gunakan formulir di samping untuk menambahkan akun pekerja baru.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Modal Edit Pegawai (Alpine.js) -->
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
                            <span>✏️</span> Edit Data Pegawai Kandang
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
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="editWorker.name" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" x-model="editWorker.email" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Standar Gaji Pokok (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="base_salary" x-model="editWorker.base_salary" min="0" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Password Baru <span class="text-slate-400 font-normal lowercase">(Kosongkan jika tidak ingin mengubah password)</span>
                            </label>
                            <input type="password" name="password" x-model="editWorker.password" minlength="8" placeholder="Minimal 8 karakter..." class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
