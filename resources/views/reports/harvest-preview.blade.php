@extends('layouts.print')

@section('title', 'Laporan Akhir Panen & P&L — ' . $flock->name)

@section('content')
    <div class="doc-title">LAPORAN HASIL PANEN & AUDIT FINANSIAL (P&L)</div>
    <div class="doc-subtitle">Periode: {{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }} — {{ $harvest ? \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y') : 'Belum Panen' }}</div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">FCR</div>
            <div class="kpi-value text-indigo">{{ number_format($fcr, 2, ',', '.') }}</div>
            <div class="kpi-unit">Feed Conversion Ratio</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Index Performa</div>
            <div class="kpi-value text-emerald">{{ number_format($ip, 0, ',', '.') }}</div>
            <div class="kpi-unit">IP Score</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Livability</div>
            <div class="kpi-value">{{ $livability }}%</div>
            <div class="kpi-unit">Tingkat Hidup</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Laba Bersih</div>
            <div class="kpi-value {{ $trueNetProfit >= 0 ? 'text-emerald' : 'text-rose' }}">{{ $trueNetProfit >= 0 ? '+' : '' }}{{ number_format($trueNetProfit / 1000000, 1, ',', '.') }}Jt</div>
            <div class="kpi-unit">True Net Profit</div>
        </div>
    </div>

    <!-- Section 1: Identitas Kandang -->
    <div class="section-title">1. Identitas Kandang</div>
    <table class="data-table">
        <tr>
            <th style="width:25%">Nama Kandang</th>
            <td style="width:25%" class="font-bold">{{ $flock->name }}</td>
            <th style="width:25%">Ras / Breed</th>
            <td style="width:25%">{{ $flock->breed }}</td>
        </tr>
        <tr>
            <th>Tanggal Chick-in</th>
            <td>{{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }}</td>
            <th>Tanggal Panen</th>
            <td>{{ $harvest ? \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y') : 'Belum Panen' }}</td>
        </tr>
        <tr>
            <th>Populasi Awal</th>
            <td class="font-bold">{{ number_format($flock->initial_population, 0, ',', '.') }} Ekor</td>
            <th>Umur Panen</th>
            <td>{{ $harvestAge }} Hari</td>
        </tr>
    </table>

    <!-- Section 2: Performa Teknis -->
    <div class="section-title">2. Performa Teknis Periode Panen</div>
    <table class="data-table">
        <tr>
            <th style="width:40%">Total Kematian (Deplesi)</th>
            <td>{{ number_format($totalMortality, 0, ',', '.') }} Ekor</td>
        </tr>
        <tr>
            <th>Populasi Ayam Dipanen</th>
            <td class="font-bold">{{ number_format($harvest ? $harvest->total_chickens_sold : $livePopulation, 0, ',', '.') }} Ekor</td>
        </tr>
        <tr>
            <th>Total Konsumsi Pakan</th>
            <td>{{ number_format($totalFeed, 2, ',', '.') }} Kg</td>
        </tr>
        <tr>
            <th>Total Tonase Panen</th>
            <td class="font-bold">{{ number_format($harvest ? $harvest->total_weight_kg : 0, 2, ',', '.') }} Kg</td>
        </tr>
        <tr>
            <th>Rata-rata Bobot Panen</th>
            <td>{{ number_format($avgWeight, 2, ',', '.') }} Kg/Ekor</td>
        </tr>
        <tr>
            <th>Feed Conversion Ratio (FCR)</th>
            <td class="font-bold">{{ number_format($fcr, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Index Performansi (IP)</th>
            <td class="font-bold text-emerald" style="font-size:12px">{{ number_format($ip, 2, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Section 3: Laporan Laba Rugi -->
    <div class="section-title">3. Laporan Laba Rugi (Profit & Loss Statement)</div>
    <table class="data-table">
        <tr style="background:#f0fdf4">
            <td colspan="2" class="font-bold text-emerald" style="text-transform:uppercase">A. PENDAPATAN (REVENUE)</td>
        </tr>
        <tr>
            <td style="width:70%">Pendapatan Kotor Penjualan Ayam ({{ number_format($harvest ? $harvest->total_weight_kg : 0, 2, ',', '.') }} Kg × Rp {{ number_format($harvest ? $harvest->chicken_price_per_kg : 0, 0, ',', '.') }})</td>
            <td class="text-right font-bold text-emerald">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</td>
        </tr>

        <tr style="background:#fff1f2">
            <td colspan="2" class="font-bold text-rose" style="text-transform:uppercase">B. HARGA POKOK PRODUKSI (COGS)</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Biaya DOC / Bibit ({{ number_format($flock->initial_population, 0, ',', '.') }} Ekor × Rp {{ number_format($docPrice, 0, ',', '.') }})</td>
            <td class="text-right text-rose">Rp {{ number_format($totalDocCost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Biaya Pakan Konsumsi ({{ number_format($totalFeed, 2, ',', '.') }} Kg)</td>
            <td class="text-right text-rose">Rp {{ number_format($totalFeedCost, 0, ',', '.') }}</td>
        </tr>
        <tr style="background:#fff1f2">
            <td class="font-bold text-right text-rose">Total COGS (Subtotal B)</td>
            <td class="font-bold text-right text-rose">Rp {{ number_format($cogs, 0, ',', '.') }}</td>
        </tr>

        <tr style="background:#f1f5f9">
            <td colspan="2" class="font-bold text-rose" style="text-transform:uppercase">C. BIAYA OPERASIONAL (OPEX)</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Beban Gaji Pekerja Kandang & Bonus (Payroll)</td>
            <td class="text-right text-rose">Rp {{ number_format($laborCost, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="font-bold text-right text-rose">TOTAL PENGELUARAN (B + C)</td>
            <td class="font-bold text-right text-rose">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
        </tr>

        <tr style="background:#f0fdf4">
            <td class="font-bold text-right text-emerald" style="font-size:12px">LABA BERSIH SIKLUS (A − B − C)</td>
            <td class="font-bold text-right {{ $trueNetProfit >= 0 ? 'text-emerald' : 'text-rose' }}" style="font-size:12px">Rp {{ number_format($trueNetProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Section 4: Analisis Kelayakan -->
    <div class="section-title">4. Analisis Kelayakan Bisnis & Risiko</div>
    <table class="data-table">
        <tr>
            <th style="width:70%">Break-Even Point (BEP) / HPP per Kg</th>
            <td class="font-bold text-right">Rp {{ number_format($bepPricePerKg, 0, ',', '.') }} / Kg</td>
        </tr>
        <tr>
            <th>Net Profit Margin Ratio</th>
            <td class="font-bold text-right {{ $profitMargin >= 0 ? 'text-emerald' : 'text-rose' }}">{{ $profitMargin }}%</td>
        </tr>
    </table>
@endsection
