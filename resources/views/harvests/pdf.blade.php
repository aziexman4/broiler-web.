<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Panen - {{ $flock->name }}</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12px;
            line-height: 1.4;
        }
        /* Kop Surat Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-logo {
            font-size: 22px;
            font-weight: bold;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 11px;
            color: #4f46e5;
            font-weight: bold;
            margin-top: 2px;
        }
        .header-address {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
        .divider {
            border-bottom: 2px solid #1e1b4b;
            margin-bottom: 15px;
        }
        /* Title */
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        /* Section styling */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-left: 4px solid #4f46e5;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
        }
        .text-right {
            text-align: right !important;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-emerald {
            color: #059669;
        }
        .text-rose {
            color: #e11d48;
        }
        /* Signature Block */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .signature-cell {
            width: 40%;
            text-align: center;
            font-size: 11px;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat / Letterhead -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="header-logo">🐓 SMARTCOOP SOLUTIONS</div>
                <div class="header-subtitle">Sistem Manajemen Peternakan Broiler Modern & Enterprise</div>
            </td>
            <td class="header-address" style="width: 45%;">
                Jl. Raya Industri Peternakan No. 88, Jawa Barat<br>
                Email: support@smartcoop.id | Telp: (021) 555-0199<br>
                Website: www.broilerapp-pro.id
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Document Title -->
    <div class="doc-title">LAPORAN HASIL PANEN & AUDIT FINANSIAL (P&L)</div>

    <!-- Section 1: Identitas Kandang -->
    <div class="section-title">1. Identitas Kandang</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Nama Kandang</th>
            <td style="width: 25%;" class="font-bold">{{ $flock->name }}</td>
            <th style="width: 25%;">Ras / Breed</th>
            <td style="width: 25%;">{{ $flock->breed }}</td>
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
            <th>Status Kandang</th>
            <td><strong style="text-transform: uppercase;">{{ $flock->status }}</strong></td>
        </tr>
    </table>

    <!-- Section 2: Performa Teknis -->
    <div class="section-title">2. Performa Teknis Periode Panen</div>
    <table class="data-table">
        <tr>
            <th style="width: 35%;">Jumlah Kematian / Afkir</th>
            <td style="width: 65%;">{{ number_format($totalMortality, 0, ',', '.') }} Ekor</td>
        </tr>
        <tr>
            <th>Populasi Ayam Dipanen (Sisa Hidup)</th>
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
            <th>FCR (Feed Conversion Ratio)</th>
            <td class="font-bold">{{ number_format($fcr, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Index Performansi (IP)</th>
            <td class="font-bold text-emerald" style="font-size: 13px;">{{ number_format($ip, 2, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Section 3: Profit & Loss Statement (P&L) -->
    <div class="section-title">3. Laporan Laba Rugi (Profit & Loss Statement)</div>
    <table class="data-table">
        <!-- PENDAPATAN -->
        <tr style="background-color: #f1f5f9;">
            <td colspan="2" class="font-bold uppercase text-emerald">A. PENDAPATAN (REVENUE)</td>
        </tr>
        <tr>
            <td style="width: 70%;">Pendapatan Kotor Penjualan Ayam ({{ number_format($harvest ? $harvest->total_weight_kg : 0, 2, ',', '.') }} Kg × Rp {{ number_format($harvest ? $harvest->chicken_price_per_kg : 0, 0, ',', '.') }})</td>
            <td style="width: 30%;" class="text-right font-bold text-emerald">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</td>
        </tr>

        <!-- COGS -->
        <tr style="background-color: #f1f5f9;">
            <td colspan="2" class="font-bold uppercase text-rose">B. HARGA POKOK PRODUKSI (COGS)</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Biaya DOC / Bibit ({{ number_format($flock->initial_population, 0, ',', '.') }} Ekor × Rp {{ number_format($docPrice, 0, ',', '.') }})</td>
            <td class="text-right text-rose">Rp {{ number_format($totalDocCost, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Biaya Pakan Konsumsi ({{ number_format($totalFeed, 2, ',', '.') }} Kg)</td>
            <td class="text-right text-rose">Rp {{ number_format($totalFeedCost, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fff1f2;">
            <td class="font-bold text-right text-rose">Total COGS (Subtotal B)</td>
            <td class="font-bold text-right text-rose">Rp {{ number_format($cogs, 0, ',', '.') }}</td>
        </tr>

        <!-- OPEX -->
        <tr style="background-color: #f1f5f9;">
            <td colspan="2" class="font-bold uppercase text-rose">C. BIAYA OPERASIONAL (OPEX)</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Beban Gaji Pekerja Kandang & Bonus (Payroll)</td>
            <td class="text-right text-rose">Rp {{ number_format($laborCost, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fff1f2;">
            <td class="font-bold text-right text-rose">Total Biaya Operasional (Subtotal C)</td>
            <td class="font-bold text-right text-rose">Rp {{ number_format($laborCost, 0, ',', '.') }}</td>
        </tr>

        <!-- TOTAL EXPENSES -->
        <tr>
            <td class="font-bold text-right text-rose">TOTAL PENGELUARAN (B + C)</td>
            <td class="font-bold text-right text-rose">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
        </tr>

        <!-- TRUE NET PROFIT -->
        <tr style="background-color: #f8fafc;">
            <td class="font-bold text-right text-emerald" style="font-size: 13px;">LABA BERSIH SIKLUS (TRUE NET PROFIT) (A - B - C)</td>
            <td class="font-bold text-right text-emerald" style="font-size: 13px;">
                Rp {{ number_format($trueNetProfit, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- Section 4: Kelayakan Finansial -->
    <div class="section-title">4. Analisis Kelayakan Bisnis & Risiko</div>
    <table class="data-table">
        <tr>
            <th style="width: 70%;">Break-Even Point (BEP) / Harga Pokok Produksi per Kg</th>
            <td style="width: 30%;" class="font-bold text-right">Rp {{ number_format($bepPricePerKg, 2, ',', '.') }} / Kg</td>
        </tr>
        <tr>
            <th>Net Profit Margin Ratio</th>
            <td class="font-bold text-emerald text-right">{{ $profitMargin }} %</td>
        </tr>
    </table>

    <!-- Signature Block -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td class="signature-cell">
                <div>Mengetahui,</div>
                <div class="font-bold" style="margin-top: 4px;">Pemilik Peternakan / Manajemen</div>
                <div class="signature-space"></div>
                <div class="font-bold">( _______________________ )</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">SmartCoop Solutions Pro</div>
            </td>
        </tr>
    </table>

</body>
</html>
