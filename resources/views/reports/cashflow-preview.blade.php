@extends('layouts.print', [
    'coop' => $coop ?? null,
    'pdfDownloadUrl' => route('reports.cash-flow', array_merge(request()->only(['filter_mode', 'month', 'start_date', 'end_date']), ['download' => 1])),
])

@section('title', 'Laporan Arus Kas')

@section('content')
    <div class="doc-title">LAPORAN ARUS KAS OPERASIONAL</div>
    <div class="doc-subtitle"><strong>Laporan Rekapitulasi Pusat — Seluruh Unit Kandang</strong> | Periode: {{ $periodLabel ?? ($records->count() > 0 ? \Carbon\Carbon::parse($records->last()->transaction_date)->locale('id')->translatedFormat('d M Y') . ' — ' . \Carbon\Carbon::parse($records->first()->transaction_date)->locale('id')->translatedFormat('d M Y') : '-') }}</div>

    <!-- KPI Summary -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Uang Masuk</div>
            <div class="kpi-value text-emerald">{{ number_format($total_in / 1000000, 1, ',', '.') }}Jt</div>
            <div class="kpi-unit">Total Pemasukan</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Uang Keluar</div>
            <div class="kpi-value text-rose">{{ number_format($total_out / 1000000, 1, ',', '.') }}Jt</div>
            <div class="kpi-unit">Total Pengeluaran</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Saldo Riil Akhir</div>
            <div class="kpi-value {{ ($closing_balance ?? $balance) >= 0 ? 'text-emerald' : 'text-rose' }}">{{ number_format(($closing_balance ?? $balance) / 1000000, 1, ',', '.') }}Jt</div>
            <div class="kpi-unit">Uang Kas Saat Itu</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Transaksi</div>
            <div class="kpi-value">{{ $records->count() }}</div>
            <div class="kpi-unit">Total Entri</div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="section-title">Rincian Transaksi Arus Kas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th class="text-center" style="width:12%">Tanggal</th>
                <th style="width:33%">Keterangan</th>
                <th class="text-center" style="width:9%">Jenis</th>
                <th class="text-right" style="width:13%">Debet (Masuk)</th>
                <th class="text-right" style="width:13%">Kredit (Keluar)</th>
                <th class="text-right" style="width:15%">Saldo Saat Itu</th>
            </tr>
        </thead>
        <tbody>
            @if(($activeFilterType ?? 'all') !== 'all' && ($opening_balance ?? 0) !== 0)
                <tr style="background:#f8fafc">
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="font-bold">Saldo Awal Bawaan Sebelum Periode</td>
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right font-bold">Rp {{ number_format($opening_balance, 0, ',', '.') }}</td>
                </tr>
            @endif
            @forelse($records->sortBy([['transaction_date', 'asc'], ['id', 'asc']])->values() as $i => $record)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($record->transaction_date)->format('d/m/Y') }}</td>
                    <td>{{ $record->description }}</td>
                    <td class="text-center">
                        <span style="padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;{{ $record->type === 'in' ? 'background:#dcfce7;color:#166534' : 'background:#ffe4e6;color:#9f1239' }}">
                            {{ $record->type === 'in' ? 'MASUK' : 'KELUAR' }}
                        </span>
                    </td>
                    <td class="text-right {{ $record->type === 'in' ? 'text-emerald font-bold' : '' }}">
                        {{ $record->type === 'in' ? 'Rp ' . number_format($record->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right {{ $record->type === 'out' ? 'text-rose font-bold' : '' }}">
                        {{ $record->type === 'out' ? 'Rp ' . number_format($record->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold {{ ($record->running_balance ?? 0) >= 0 ? '' : 'text-rose' }}">
                        Rp {{ number_format($record->running_balance ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 18px; color: #64748b;">Tidak ada transaksi kas pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9">
                <td colspan="4" class="font-bold text-right">TOTAL MUTASI PERIODE</td>
                <td class="text-right font-bold text-emerald">Rp {{ number_format($total_in, 0, ',', '.') }}</td>
                <td class="text-right font-bold text-rose">Rp {{ number_format($total_out, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($balance, 0, ',', '.') }}</td>
            </tr>
            <tr style="background:#f0fdf4">
                <td colspan="4" class="font-bold text-right" style="font-size:12px">SALDO KAS RIIL AKHIR PERIODE</td>
                <td colspan="3" class="text-right font-bold {{ ($closing_balance ?? $balance) >= 0 ? 'text-emerald' : 'text-rose' }}" style="font-size:12px">Rp {{ number_format($closing_balance ?? $balance, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
