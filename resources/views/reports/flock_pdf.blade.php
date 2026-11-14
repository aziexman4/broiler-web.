<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Performance Kandang - {{ $flock->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            margin: 0;
            padding: 15px;
            font-size: 12px;
        }
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #1e1b4b;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 11px;
        }
        .metrics-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .metric-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
        }
        .metric-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>BroilerApp - Laporan Performance Kandang</h1>
        <p>Nama Kandang: <strong>{{ $flock->name }}</strong> | Ras: <strong>{{ $flock->breed }}</strong> | Chick In: <strong>{{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }}</strong></p>
    </div>

    <!-- Ringkasan Metric -->
    <table class="metrics-grid">
        <tr>
            <td class="metric-card" width="25%">
                <div class="metric-title">Populasi Awal</div>
                <div class="metric-value">{{ number_format($flock->initial_population) }} ekor</div>
            </td>
            <td class="metric-card" width="25%">
                <div class="metric-title">Populasi Hidup</div>
                <div class="metric-value" style="color: #059669;">{{ number_format($livePopulation) }} ekor</div>
            </td>
            <td class="metric-card" width="25%">
                <div class="metric-title">Total Pakan</div>
                <div class="metric-value" style="color: #4f46e5;">{{ number_format($totalFeed, 2) }} kg</div>
            </td>
            <td class="metric-card" width="25%">
                <div class="metric-title">Daya Hidup (Livability)</div>
                <div class="metric-value">{{ $livability }}%</div>
            </td>
        </tr>
        <tr>
            <td class="metric-card" width="25%" style="padding-top: 8px;">
                <div class="metric-title">Total Kematian</div>
                <div class="metric-value" style="color: #dc2626;">{{ number_format($totalMortality) }} ekor</div>
            </td>
            <td class="metric-card" width="25%" style="padding-top: 8px;">
                <div class="metric-title">Total Afkir</div>
                <div class="metric-value" style="color: #ea580c;">{{ number_format($totalCulling) }} ekor</div>
            </td>
            <td class="metric-card" width="25%" style="padding-top: 8px;">
                <div class="metric-title">FCR (Feed Conv.)</div>
                <div class="metric-value">{{ $fcr }}</div>
            </td>
            <td class="metric-card" width="25%" style="padding-top: 8px;">
                <div class="metric-title">Index Performansi (IP)</div>
                <div class="metric-value" style="color: #2563eb;">{{ $ip }}</div>
            </td>
        </tr>
    </table>

    <!-- Riwayat Catatan Harian -->
    <div class="section-title">Riwayat Catatan Harian</div>
    @if($dailyRecords->isEmpty())
        <p style="color: #6b7280; font-style: italic;">Belum ada catatan harian.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th class="text-right">Kematian (Ekor)</th>
                    <th class="text-right">Afkir (Ekor)</th>
                    <th class="text-right">Konsumsi Pakan (Kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyRecords as $index => $record)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}</td>
                        <td class="text-right" style="color: #dc2626;">{{ number_format($record->mortality) }}</td>
                        <td class="text-right" style="color: #ea580c;">{{ number_format($record->culling) }}</td>
                        <td class="text-right" style="color: #4f46e5;">{{ number_format($record->feed_consumed_kg, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Data Panen -->
    <div class="section-title">Data Panen</div>
    @if($harvests->isEmpty())
        <p style="color: #6b7280; font-style: italic;">Belum ada data panen.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Panen</th>
                    <th class="text-right">Terjual (Ekor)</th>
                    <th class="text-right">Total Berat (Kg)</th>
                    <th>Nama Pembeli</th>
                    <th class="text-right">Pendapatan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($harvests as $index => $harvest)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($harvest->harvest_date)->format('d M Y') }}</td>
                        <td class="text-right">{{ number_format($harvest->total_chickens_sold) }}</td>
                        <td class="text-right">{{ number_format($harvest->total_weight_kg, 2) }}</td>
                        <td>{{ $harvest->buyer_name }}</td>
                        <td class="text-right">Rp {{ number_format($harvest->revenue, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak secara otomatis oleh BroilerApp pada {{ date('d M Y H:i') }}
    </div>

</body>
</html>
