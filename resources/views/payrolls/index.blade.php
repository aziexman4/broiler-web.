<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Sistem Penggajian & Bonus Performa (Payroll)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Kelola gaji pokok dan bonus performa berbasis Index Performansi (IP) panen</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Notification -->

            <!-- Section 1: Form Terbitkan Gaji (Issue Paycheck) -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6">
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Terbitkan Gaji (Issue Paycheck)</h3>
                        <p class="text-xs text-slate-500">Hitung otomatis gaji pokok dan bonus jika Index Performansi (IP) kandang > 300</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Bonus Threshold: IP > 300 (Rp 500.000)
                    </span>
                </div>

                <form action="{{ route('payrolls.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dropdown Worker -->
                        <div>
                            <label for="user_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Pilih Pekerja / Anak Kandang <span class="text-rose-500">*</span>
                            </label>
                            <select id="user_id" name="user_id" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="" disabled selected>-- Pilih Pekerja --</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">
                                        {{ $worker->name }} ({{ ucfirst($worker->role) }}) - Gaji Pokok: Rp {{ number_format($worker->base_salary ?? 2500000, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Closed Flock -->
                        <div>
                            <label for="flock_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Pilih Periode Kandang Panen <span class="text-rose-500">*</span>
                            </label>
                            <select id="flock_id" name="flock_id" required class="w-full rounded-xl border-slate-300 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="" disabled selected>-- Pilih Kandang Selesai Panen --</option>
                                @foreach ($closedFlocks as $flock)
                                    <option value="{{ $flock->id }}">
                                        {{ $flock->name }} (Tutup: {{ $flock->closed_at ? \Carbon\Carbon::parse($flock->closed_at)->format('d M Y') : 'Panen Selesai' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Terbitkan Gaji Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Table Riwayat Penggajian -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Riwayat Penggajian (Payroll History)</h3>
                        <p class="text-xs text-slate-500">Catatan pembayaran gaji dan insentif bonus yang telah diterbitkan</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Tanggal Bayar</th>
                                <th scope="col" class="px-6 py-3.5">Nama Pekerja</th>
                                <th scope="col" class="px-6 py-3.5">Kandang</th>
                                <th scope="col" class="px-6 py-3.5">Gaji Pokok</th>
                                <th scope="col" class="px-6 py-3.5">Bonus Performa</th>
                                <th scope="col" class="px-6 py-3.5 text-right">Total Diterima</th>
                                <th scope="col" class="px-6 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            @forelse ($payrolls as $payroll)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900">
                                        {{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $payroll->user->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $payroll->flock->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($payroll->bonus_amount > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                + Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }} (IP > 300)
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-slate-900 text-base">
                                        Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('payrolls.payslip', $payroll) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-colors">
                                            🖨️ Cetak Slip
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="font-medium text-slate-500">Belum Ada Data Penggajian</p>
                                        <p class="text-xs mt-1">Gunakan formulir di atas untuk menerbitkan gaji dan bonus performa pekerja.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
