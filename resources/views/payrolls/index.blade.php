<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Sistem Penggajian & Bonus Performa (Payroll)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola gaji pokok dan kalkulasi otomatis bonus berjenjang berbasis Indeks Performa (IP) panen</p>
            </div>
        </div>
    </x-slot>

    @php
        $flocksPayload = $closedFlocks->mapWithKeys(function ($f) {
            return [
                $f->id => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'worker_id' => $f->worker_id,
                    'worker_name' => $f->worker_name,
                    'worker_base_salary' => (int) $f->worker_base_salary,
                    'total_chickens_sold' => (int) $f->total_chickens_sold,
                    'total_weight_kg' => (float) $f->total_weight_kg,
                    'total_feed_kg' => (float) $f->total_feed_kg,
                    'harvest_age' => (int) $f->harvest_age,
                    'achieved_fcr' => (float) $f->achieved_fcr,
                    'achieved_ip' => (float) $f->achieved_ip,
                    'bonus_percentage' => (int) $f->bonus_percentage,
                    'bonus_tier_label' => $f->bonus_tier_label,
                    'recommended_bonus' => (int) $f->recommended_bonus,
                ]
            ];
        });

        $workersPayload = $workers->mapWithKeys(function ($w) {
            return [
                $w->id => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'base_salary' => (int) ($w->base_salary > 0 ? $w->base_salary : 2500000),
                ]
            ];
        });
    @endphp

    <div class="py-6" x-data="payrollFormCalculator()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-alerts />

            <!-- Section 1: Form Terbitkan Gaji (Issue Paycheck) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 mb-6 border-b border-slate-100 dark:border-slate-700 gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Terbitkan Gaji & Bonus Siklus (Issue Paycheck)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih siklus tutup buku untuk mengisi otomatis pekerja penanggung jawab, gaji pokok, dan rekomendasi bonus IP</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">IP ≥ 400: Bonus 20%</span>
                        <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">IP 350–399: Bonus 15%</span>
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">IP 300–349: Bonus 10%</span>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">IP &lt; 300: 0%</span>
                    </div>
                </div>

                <form action="{{ route('payrolls.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dropdown Closed Flock -->
                        <div>
                            <label for="flock_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                1. Pilih Siklus Tutup Buku (Kandang Panen) <span class="text-rose-500">*</span>
                            </label>
                            <select id="flock_id" name="flock_id" x-model="selectedFlockId" @change="onFlockChange()" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Kandang Selesai Panen --</option>
                                @foreach ($closedFlocks as $flock)
                                    <option value="{{ $flock->id }}">
                                        {{ $flock->name }} — IP: {{ number_format($flock->achieved_ip, 2) }} ({{ $flock->worker_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Worker -->
                        <div>
                            <label for="user_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                2. Pekerja / Anak Kandang Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <select id="user_id" name="user_id" x-model="selectedWorkerId" @change="onWorkerChange()" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Pekerja --</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">
                                        {{ $worker->name }} ({{ ucfirst($worker->role) }}) — Gaji Pokok: Rp {{ number_format($worker->base_salary ?? 2500000, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Field Base Salary -->
                        <div>
                            <label for="base_salary" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                3. Gaji Pokok (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="base_salary" name="base_salary" x-model.number="baseSalary" @input="recalculateRecommendedBonusOnSalaryEdit()" min="0" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold">
                            <p class="mt-1 text-[11px] text-slate-400">Terisi otomatis sesuai standar gaji pokok pegawai yang dipilih.</p>
                        </div>

                        <!-- Field Bonus Amount -->
                        <div>
                            <label for="bonus_amount" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                4. Nominal Bonus Performa (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="bonus_amount" name="bonus_amount" x-model.number="bonusAmount" min="0" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 shadow-md focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                            <p class="mt-1 text-[11px] text-slate-400">Otomatis dihitung dari persentase IP siklus, namun tetap dapat disesuaikan manual oleh Owner.</p>
                        </div>
                    </div>

                    <!-- Dynamic Evaluation Info Box -->
                    <div x-show="activeFlock" x-transition class="p-4 rounded-2xl border bg-slate-50 dark:bg-slate-900/60 border-indigo-200 dark:border-indigo-800/70" x-cloak style="display: none;">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Evaluasi Siklus Kandang:</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300': activeFlock && activeFlock.achieved_ip >= 400,
                                              'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300': activeFlock && activeFlock.achieved_ip >= 350 && activeFlock.achieved_ip < 400,
                                              'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300': activeFlock && activeFlock.achieved_ip >= 300 && activeFlock.achieved_ip < 350,
                                              'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300': activeFlock && activeFlock.achieved_ip < 300
                                          }"
                                          x-text="activeFlock ? `Capaian IP: ${activeFlock.achieved_ip} (FCR: ${activeFlock.achieved_fcr}) — Rekomendasi Bonus ${activeFlock.bonus_percentage}% (${activeFlock.bonus_tier_label})` : ''">
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400" x-show="activeFlock">
                                    Populasi Panen: <strong x-text="activeFlock ? activeFlock.total_chickens_sold.toLocaleString('id-ID') : 0"></strong> ekor |
                                    Tonase: <strong x-text="activeFlock ? activeFlock.total_weight_kg.toLocaleString('id-ID') : 0"></strong> Kg |
                                    Total Pakan Fisik: <strong x-text="activeFlock ? activeFlock.total_feed_kg.toLocaleString('id-ID') : 0"></strong> Kg |
                                    Umur Panen: <strong x-text="activeFlock ? activeFlock.harvest_age : 0"></strong> hari
                                </p>
                            </div>
                            <div class="text-left md:text-right border-t md:border-t-0 pt-2 md:pt-0 border-slate-200 dark:border-slate-700">
                                <div class="text-[11px] uppercase font-semibold text-slate-500 dark:text-slate-400">Estimasi Total Diterima (Take Home Pay)</div>
                                <div class="text-xl font-black text-indigo-600 dark:text-indigo-400" x-text="'Rp ' + ((Number(baseSalary) || 0) + (Number(bonusAmount) || 0)).toLocaleString('id-ID')"></div>
                            </div>
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Riwayat Penggajian (Payroll History)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Catatan pembayaran gaji dan insentif bonus yang telah diterbitkan</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
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
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700">
                            @forelse ($payrolls as $payroll)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                        {{ $payroll->user->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                            {{ $payroll->flock->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($payroll->bonus_amount > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                + Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-slate-900 dark:text-white text-base">
                                        Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('payrolls.payslip', $payroll) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors">
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

                @if(method_exists($payrolls, 'links'))
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                        {{ $payrolls->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('payrollFormCalculator', () => ({
                flocks: @json($flocksPayload),
                workers: @json($workersPayload),
                selectedFlockId: '',
                selectedWorkerId: '',
                baseSalary: 0,
                bonusAmount: 0,
                activeFlock: null,

                onFlockChange() {
                    const flock = this.flocks[this.selectedFlockId] || null;
                    this.activeFlock = flock;

                    if (flock) {
                        if (flock.worker_id && this.workers[flock.worker_id]) {
                            this.selectedWorkerId = String(flock.worker_id);
                            this.baseSalary = this.workers[flock.worker_id].base_salary;
                        } else {
                            this.baseSalary = flock.worker_base_salary || 2500000;
                        }
                        this.bonusAmount = Math.round(this.baseSalary * (flock.bonus_percentage / 100));
                    } else {
                        this.bonusAmount = 0;
                    }
                },

                onWorkerChange() {
                    const worker = this.workers[this.selectedWorkerId] || null;
                    if (worker) {
                        this.baseSalary = worker.base_salary;
                        if (this.activeFlock) {
                            this.bonusAmount = Math.round(this.baseSalary * (this.activeFlock.bonus_percentage / 100));
                        }
                    }
                },

                recalculateRecommendedBonusOnSalaryEdit() {
                    if (this.activeFlock) {
                        this.bonusAmount = Math.round((Number(this.baseSalary) || 0) * (this.activeFlock.bonus_percentage / 100));
                    }
                }
            }));
        });
    </script>
</x-app-layout>
