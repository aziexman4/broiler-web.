<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen Karyawan (HRD)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Kelola akun pekerja / anak kandang dan pengaturan gaji pokok awal</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Notification -->

            <!-- Error Alert Notification -->
            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- 2-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Form Tambah Pekerja Baru -->
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 lg:col-span-1">
                    <div class="pb-4 mb-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Tambah Pekerja Baru</h3>
                        <p class="text-xs text-slate-500">Buat akun login anak kandang dan tentukan standar gaji pokok</p>
                    </div>

                    <form action="{{ route('workers.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" placeholder="misal: Budi Santoso" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" placeholder="budi@broilerapp.com" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Password <span class="text-rose-500">* (Min. 8 Karakter)</span>
                            </label>
                            <input type="password" id="password" name="password" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Gaji Pokok -->
                        <div>
                            <label for="base_salary" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Standar Gaji Pokok (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="base_salary" name="base_salary" value="2500000" min="0" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
                <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Pekerja Aktif</h3>
                            <p class="text-xs text-slate-500">Seluruh akun anak kandang yang terdaftar dalam sistem</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Nama Pekerja</th>
                                    <th scope="col" class="px-6 py-3.5">Email</th>
                                    <th scope="col" class="px-6 py-3.5">Gaji Pokok</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70">
                                @forelse ($workers as $worker)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($worker->name, 0, 2)) }}
                                            </div>
                                            <span>{{ $worker->name }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">
                                            {{ $worker->email }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-emerald-600">
                                            Rp {{ number_format($worker->base_salary, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('workers.destroy', $worker) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memberhentikan dan menghapus akun pekerja ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl transition-colors border border-rose-200">
                                                    Hapus Akun / Berhentikan
                                                </button>
                                            </form>
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
    </div>
</x-app-layout>
