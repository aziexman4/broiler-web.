<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Slip Gaji Saya &amp; Riwayat Bonus IP
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Pekerja: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ Auth::user()->name }}</span>
                    &bull; Acuan Gaji Pokok: <span class="font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format(Auth::user()->base_salary ?? 0, 0, ',', '.') }}</span>
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $totalPaidSum = $payrolls->sum('total_paid');
        $totalBonusSum = $payrolls->sum('bonus_amount');
        $totalCount = $payrolls->count();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Penghasilan Diterima</div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                        Rp {{ number_format($totalPaidSum, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Akumulasi gaji pokok + bonus IP</div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Akumulasi Bonus Performa (IP)</div>
                    <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        + Rp {{ number_format($totalBonusSum, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Berdasarkan pencapaian Indeks Performansi</div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jumlah Slip Terbit</div>
                    <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                        {{ number_format($totalCount) }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Siklus</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Siap dicetak dalam format A4</div>
                </div>
            </div>

            <!-- Info Skema Bonus IP -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                    <span class="font-bold text-slate-800 dark:text-slate-100">Skema Bonus Berjenjang (IP Panen):</span>
                    Bonus dihitung dari persentase gaji pokok sesuai pencapaian IP kandang saat tutup buku.
                </div>
                <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">IP &ge; 400: +20%</span>
                    <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">IP 350–399: +15%</span>
                    <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">IP 300–349: +10%</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">IP &lt; 300: 0%</span>
                </div>
            </div>

            <!-- Riwayat Slip Gaji -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Riwayat Slip Gaji Saya</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Hanya menampilkan pembayaran gaji &amp; bonus siklus yang diterbitkan untuk akun Anda</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs text-slate-500 dark:text-slate-300 uppercase bg-slate-50/70 dark:bg-slate-700/70 border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 font-semibold">Tanggal Bayar</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold">Kandang / Siklus</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold">Gaji Pokok</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold">Bonus IP</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold text-right">Total Diterima</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold text-center">Slip Gaji</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($payrolls as $payroll)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-200 font-medium">
                                        {{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                            {{ $payroll->flock->name ?? '-' }}
                                        </span>
                                        @if($payroll->flock?->coop)
                                            <div class="text-[11px] text-slate-400 dark:text-slate-400 mt-1">
                                                📍 {{ $payroll->flock->coop->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($payroll->bonus_amount > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                + Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 text-xs">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-extrabold text-slate-900 dark:text-white text-base">
                                        Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('payrolls.payslip', $payroll) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors">
                                            🖨️ Cetak Slip
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="font-semibold text-slate-600 dark:text-slate-300">Belum Ada Slip Gaji Diterbitkan</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Slip gaji dan bonus IP Anda akan muncul di sini setelah siklus kandang panen &amp; tutup buku.</p>
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
