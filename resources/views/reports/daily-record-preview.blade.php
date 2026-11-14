@extends('layouts.print')

@section('title', 'Buku Recording Harian — ' . $flock->name)

@section('content')
    <div class="doc-title">BUKU RECORDING HARIAN KANDANG BROILER</div>
    <div class="doc-subtitle">{{ $flock->name }} | Ras: {{ $flock->breed }} | Chick-in: {{ \Carbon\Carbon::parse($flock->start_date)->format('d M Y') }} | Populasi Awal: {{ number_format($flock->initial_population, 0, ',', '.') }} Ekor</div>

    <!-- KPI Summary -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Total Hari</div>
            <div class="kpi-value">{{ $dailyRecords->count() }}</div>
            <div class="kpi-unit">Hari Tercatat</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Deplesi</div>
            <div class="kpi-value text-rose">{{ number_format($totalMortality, 0, ',', '.') }}</div>
            <div class="kpi-unit">Ekor Mati + Afkir</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Pakan</div>
            <div class="kpi-value text-indigo">{{ number_format($totalFeed, 0, ',', '.') }}</div>
            <div class="kpi-unit">Kg Konsumsi</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Populasi Sisa</div>
            <div class="kpi-value text-emerald">{{ number_format($livePopulation, 0, ',', '.') }}</div>
            <div class="kpi-unit">Ekor Hidup</div>
        </div>
    </div>

    <!-- Daily Records Table -->
    <div class="section-title">Catatan Harian Kandang</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width:6%">Hari</th>
                <th class="text-center" style="width:14%">Tanggal</th>
                <th class="text-center" style="width:10%">Mati</th>
                <th class="text-center" style="width:10%">Afkir</th>
                <th class="text-center" style="width:12%">Pre-Starter</th>
                <th class="text-center" style="width:12%">Starter</th>
                <th class="text-center" style="width:12%">Finisher</th>
                <th class="text-center" style="width:12%">Total Pakan</th>
                <th class="text-center" style="width:12%">Pop. Sisa</th>
            </tr>
        </thead>
        <tbody>
            @php $runningPop = $flock->initial_population; @endphp
            @foreach($dailyRecords as $i => $record)
                @php 
                    $runningPop -= ($record->mortality + $record->culling);
                    $dayFeed = $record->feed_consumed_kg > 0 
                        ? $record->feed_consumed_kg 
                        : ($record->feed_pre_starter_kg + $record->feed_starter_kg + $record->feed_finisher_kg);
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($record->record_date)->format('d/m/Y') }}</td>
                    <td class="text-center {{ $record->mortality > 0 ? 'text-rose font-bold' : '' }}">{{ $record->mortality }}</td>
                    <td class="text-center">{{ $record->culling }}</td>
                    <td class="text-center">{{ number_format($record->feed_pre_starter_kg, 1) }}</td>
                    <td class="text-center">{{ number_format($record->feed_starter_kg, 1) }}</td>
                    <td class="text-center">{{ number_format($record->feed_finisher_kg, 1) }}</td>
                    <td class="text-center font-bold">{{ number_format($dayFeed, 1) }}</td>
                    <td class="text-center">{{ number_format(max(0, $runningPop), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9">
                <td colspan="2" class="font-bold text-right">TOTAL</td>
                <td class="text-center font-bold text-rose">{{ $dailyRecords->sum('mortality') }}</td>
                <td class="text-center font-bold">{{ $dailyRecords->sum('culling') }}</td>
                <td class="text-center font-bold">{{ number_format($dailyRecords->sum('feed_pre_starter_kg'), 1) }}</td>
                <td class="text-center font-bold">{{ number_format($dailyRecords->sum('feed_starter_kg'), 1) }}</td>
                <td class="text-center font-bold">{{ number_format($dailyRecords->sum('feed_finisher_kg'), 1) }}</td>
                <td class="text-center font-bold text-indigo">{{ number_format($totalFeed, 1) }}</td>
                <td class="text-center font-bold text-emerald">{{ number_format($livePopulation, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
