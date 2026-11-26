@php
    $unitCoop = $coop ?? $payroll->flock?->coop;
    $resolvedMetrics = $flockMetrics ?? ($payroll->flock ? \App\Services\FlockMetricsService::calculateForFlock($payroll->flock) : null);
    $resolvedBonusEval = $bonusEval ?? ($resolvedMetrics
        ? \App\Services\FlockMetricsService::evaluateIpBonus($resolvedMetrics['ip'], (int) $payroll->base_salary, (float) $resolvedMetrics['total_harvest_weight_kg'])
        : null);
    $achievedIp = (float) ($resolvedMetrics['ip'] ?? 0);
    $hasActualIp = $achievedIp > 0;
@endphp
@extends('layouts.print', [
    'coop' => $unitCoop,
    'pdfDownloadUrl' => route('payrolls.payslip', ['payroll' => $payroll, 'download' => 1]),
    'leftSignerPrefix' => 'Penerima,',
    'leftSignerRole' => 'Pekerja Kandang',
    'leftSignerName' => $payroll->user->name,
    'leftSignerUnit' => $unitCoop?->name ?? 'Peternakan Broiler Hanau',
    'rightSignerPrefix' => 'Mengetahui & Menyetujui,',
    'rightSignerRole' => 'Pemilik / Owner',
])

@section('title', 'Slip Gaji — ' . $payroll->user->name)

@section('content')
    <div class="doc-title">SLIP GAJI PEKERJA</div>
    <div class="doc-subtitle">No. Slip: PAY-{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }} | Tanggal: {{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}</div>

    <!-- Employee Identity -->
    <div class="section-title">Identitas Pekerja &amp; Siklus Kandang</div>
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
            <th>Kandang / Siklus</th>
            <td class="font-bold">{{ $payroll->flock ? $payroll->flock->name : '-' }}</td>
        </tr>
        <tr>
            <th>Periode Pembayaran</th>
            <td>{{ \Carbon\Carbon::parse($payroll->payment_date)->format('F Y') }}</td>
            <th>Tanggal Bayar</th>
            <td>{{ \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') }}</td>
        </tr>
        @if($hasActualIp)
            <tr>
                <th>Capaian IP &amp; FCR</th>
                <td class="font-bold">IP {{ number_format($achievedIp, 2, ',', '.') }} | FCR {{ number_format($resolvedMetrics['fcr'], 3, ',', '.') }}</td>
                <th>Tonase &amp; Daya Hidup</th>
                <td>{{ number_format($resolvedMetrics['total_harvest_weight_kg'], 1, ',', '.') }} Kg ({{ number_format($resolvedMetrics['livability'], 2, ',', '.') }}%)</td>
            </tr>
        @endif
    </table>

    <!-- Salary Breakdown -->
    <div class="section-title">Rincian Komponen Gaji</div>
    <table class="data-table">
        <tr style="background:#f0fdf4">
            <td colspan="2" class="font-bold text-emerald" style="text-transform:uppercase">RINCIAN PENDAPATAN</td>
        </tr>
        <tr>
            <td style="width:70%;padding-left:20px">Gaji Pokok (Base Salary)</td>
            <td class="text-right font-bold">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:20px">
                <div>
                    Bonus Performa Panen
                    @if($hasActualIp)
                        @if($achievedIp >= 300 && $payroll->bonus_amount > 0)
                            <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:700;background:#dcfce7;color:#166534;margin-left:6px">
                                IP {{ number_format($achievedIp, 2, ',', '.') }} — {{ $resolvedBonusEval['bonus_tier_label'] }} ({{ $resolvedBonusEval['bonus_percentage'] }}%) ✓
                            </span>
                        @elseif($payroll->bonus_amount > 0)
                            <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:700;background:#dcfce7;color:#166534;margin-left:6px">
                                IP {{ number_format($achievedIp, 2, ',', '.') }} — Insentif Kebijakan ✓
                            </span>
                        @else
                            <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:700;background:#fef3c7;color:#92400e;margin-left:6px">
                                IP {{ number_format($achievedIp, 2, ',', '.') }} — {{ $resolvedBonusEval['bonus_tier_label'] }} (0%)
                            </span>
                        @endif
                    @else
                        @if($payroll->bonus_amount > 0)
                            <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:600;background:#dcfce7;color:#166534;margin-left:6px">IP &ge; 300 ✓</span>
                        @else
                            <span style="padding:2px 6px;border-radius:4px;font-size:8px;font-weight:600;background:#fef3c7;color:#92400e;margin-left:6px">IP &lt; 300</span>
                        @endif
                    @endif
                </div>
                @if($hasActualIp)
                    <div style="font-size:8.5px;color:#64748b;margin-top:2px">
                        Evaluasi Panen ({{ $resolvedMetrics['harvest_age'] }} hari): {{ number_format($resolvedMetrics['total_harvest_sold'], 0, ',', '.') }} ekor terjual, bobot rata-rata {{ number_format($resolvedMetrics['avg_weight_kg'], 2, ',', '.') }} Kg/ekor.
                    </div>
                @endif
            </td>
            <td class="text-right {{ $payroll->bonus_amount > 0 ? 'text-emerald font-bold' : '' }}">Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }}</td>
        </tr>

        <tr style="background:#f0fdf4;border-top:2px solid #059669">
            <td class="font-bold text-right text-emerald" style="font-size:13px">TOTAL DITERIMA (TAKE HOME PAY)</td>
            <td class="font-bold text-right text-emerald" style="font-size:13px">Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="margin-top:20px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;font-size:9px;color:#64748b;line-height:1.6">
        <strong>Catatan Skema Bonus Performa (IP):</strong> Standar bonus dihitung berdasarkan capaian Indeks Performa (IP) siklus kandang: <strong>IP &ge; 400 (20% Gaji Pokok / Rp200/kg) | IP 350–399 (15% / Rp150/kg) | IP 300–349 (10% / Rp100/kg) | IP &lt; 300 (0%)</strong>, dengan persetujuan akhir Pemilik. Slip ini berlaku sebagai bukti pembayaran yang sah.
    </div>
@endsection
