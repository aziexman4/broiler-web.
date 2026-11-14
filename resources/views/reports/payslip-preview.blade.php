@extends('layouts.print', ['coop' => $coop ?? $payroll->flock?->coop])

@section('title', 'Slip Gaji — ' . $payroll->user->name)

@section('content')
    <div class="doc-title">SLIP GAJI PEKERJA</div>
    <div class="doc-subtitle">No. Slip: PAY-{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }} | Tanggal: {{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}</div>

    <!-- Employee Identity -->
    <div class="section-title">Identitas Pekerja</div>
    <table class="data-table">
        <tr>
            <th style="width:25%">Nama Lengkap</th>
            <td style="width:25%" class="font-bold">{{ $payroll->user->name }}</td>
            <th style="width:25%">Email</th>
            <td style="width:25%">{{ $payroll->user->email }}</td>
        </tr>
        <tr>
            <th>Jabatan / Role</th>
            <td>{{ ucfirst($payroll->user->role) }}</td>
            <th>Kandang Tugas</th>
            <td class="font-bold">{{ $payroll->flock ? $payroll->flock->name : '-' }}</td>
        </tr>
        <tr>
            <th>Periode Pembayaran</th>
            <td>{{ \Carbon\Carbon::parse($payroll->payment_date)->format('F Y') }}</td>
            <th>Tanggal Bayar</th>
            <td>{{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}</td>
        </tr>
    </table>

    <!-- Salary Breakdown -->
    <div class="section-title">Rincian Komponen Gaji</div>
    <table class="data-table">
        <tr style="background:#f0fdf4">
            <td colspan="2" class="font-bold text-emerald" style="text-transform:uppercase">A. PENDAPATAN</td>
        </tr>
        <tr>
            <td style="width:70%;padding-left:20px">Gaji Pokok (Base Salary)</td>
            <td class="text-right font-bold">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:20px">
                Bonus Performa Panen
                @if($payroll->bonus_amount > 0)
                    <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:600;background:#dcfce7;color:#166534;margin-left:6px">IP > 300 ✓</span>
                @else
                    <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:600;background:#fef3c7;color:#92400e;margin-left:6px">IP ≤ 300</span>
                @endif
            </td>
            <td class="text-right {{ $payroll->bonus_amount > 0 ? 'text-emerald font-bold' : '' }}">Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }}</td>
        </tr>

        <tr style="background:#fff1f2">
            <td colspan="2" class="font-bold text-rose" style="text-transform:uppercase">B. POTONGAN</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Potongan / Denda</td>
            <td class="text-right">Rp 0</td>
        </tr>

        <tr style="background:#f0fdf4;border-top:2px solid #059669">
            <td class="font-bold text-right text-emerald" style="font-size:13px">TOTAL DITERIMA (TAKE HOME PAY)</td>
            <td class="font-bold text-right text-emerald" style="font-size:13px">Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="margin-top:20px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;font-size:9px;color:#64748b;line-height:1.6">
        <strong>Catatan Skema Bonus Performa (IP):</strong> Bonus performa dihitung dari persentase gaji pokok berdasarkan capaian Indeks Performa (IP) kandang: <strong>IP ≥ 400 (20%) | IP 350–399 (15%) | IP 300–349 (10%) | IP < 300 (0%)</strong>. Slip ini berlaku sebagai bukti pembayaran yang sah.
    </div>
@endsection

@section('signature')
    @php
        $signCoop = $coop ?? $payroll->flock?->coop ?? \App\Models\Coop::where('is_headquarters', true)->first();
        $managerName = $signCoop && $signCoop->manager_name ? $signCoop->manager_name : 'Pemilik / Manajemen';
        $unitName = $signCoop && $signCoop->name ? $signCoop->name : 'Peternakan Broiler Hanau';
    @endphp
    <div style="display:flex;justify-content:space-between;margin-top:40px">
        <div style="width:200px;text-align:center;font-size:10px">
            <div>Penerima,</div>
            <div class="font-bold" style="margin-top:4px">Pekerja Kandang</div>
            <div style="height:60px"></div>
            <div class="font-bold">( {{ $payroll->user->name }} )</div>
        </div>
        <div style="width:220px;text-align:center;font-size:10px">
            <div>Mengetahui,</div>
            <div class="font-bold" style="margin-top:4px">Kepala Unit / Penanggung Jawab</div>
            <div style="height:60px"></div>
            <div class="font-bold">( {{ $managerName }} )</div>
            <div style="font-size:9px;color:#64748b;margin-top:2px">{{ $unitName }}</div>
        </div>
    </div>
@endsection
