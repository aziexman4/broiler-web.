@extends('layouts.print', [
    'coop' => $coop ?? null,
    'pdfDownloadUrl' => route('reports.inventory-audit', array_merge(request()->only(['filter_mode', 'month', 'start_date', 'end_date']), ['download' => 1])),
])

@section('title', 'Laporan Audit Mutasi Gudang Pakan')

@section('content')
    <div class="doc-title">LAPORAN AUDIT MUTASI GUDANG PAKAN</div>
    <div class="doc-subtitle"><strong>Laporan Rekapitulasi Pusat — Seluruh Unit Kandang</strong> | Periode: {{ $periodLabel ?? '-' }} | Total {{ $records->count() }} transaksi tercatat</div>

    <!-- KPI Summary -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Stok Masuk</div>
            <div class="kpi-value text-emerald">{{ number_format($totalIn, 0, ',', '.') }}</div>
            <div class="kpi-unit">Kg Pembelian</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Stok Keluar</div>
            <div class="kpi-value text-rose">{{ number_format($totalOut, 0, ',', '.') }}</div>
            <div class="kpi-unit">Kg Distribusi</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Selisih</div>
            <div class="kpi-value text-indigo">{{ number_format($totalIn - $totalOut, 0, ',', '.') }}</div>
            <div class="kpi-unit">Kg Tersisa</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Nilai Total</div>
            <div class="kpi-value">{{ number_format($totalValue / 1000000, 1, ',', '.') }}Jt</div>
            <div class="kpi-unit">Rp Pembelian</div>
        </div>
    </div>

    <!-- Mutation Log Table -->
    <div class="section-title">Rincian Jejak Mutasi Pakan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th class="text-center" style="width:12%">Tanggal</th>
                <th style="width:18%">Nama Pakan</th>
                <th class="text-center" style="width:10%">Jenis</th>
                <th class="text-right" style="width:12%">Kuantitas</th>
                <th class="text-right" style="width:13%">Harga/Kg</th>
                <th style="width:15%">Tujuan Kandang</th>
                <th style="width:15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $i => $record)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($record->transaction_date)->format('d/m/Y') }}</td>
                    <td class="font-bold">{{ $record->feed_name }}</td>
                    <td class="text-center">
                        <span style="padding:2px 6px;border-radius:4px;font-size:9px;font-weight:600;{{ $record->transaction_type === 'in' ? 'background:#dcfce7;color:#166534' : 'background:#ffe4e6;color:#9f1239' }}">
                            {{ $record->transaction_type === 'in' ? 'BELI' : 'PAKAI' }}
                        </span>
                    </td>
                    <td class="text-right font-bold">{{ number_format($record->quantity_kg, 1, ',', '.') }} Kg</td>
                    <td class="text-right">{{ $record->price_per_kg ? 'Rp ' . number_format($record->price_per_kg, 0, ',', '.') : '-' }}</td>
                    <td>{{ $record->flock ? $record->flock->name : '-' }}</td>
                    <td class="text-sm">{{ $record->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 18px; color: #64748b;">Tidak ada transaksi mutasi pakan pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9">
                <td colspan="4" class="font-bold text-right">TOTAL</td>
                <td class="text-right font-bold text-indigo">{{ number_format($totalIn + $totalOut, 1, ',', '.') }} Kg</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
@endsection
