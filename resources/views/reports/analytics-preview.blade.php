@extends('layouts.print', [
    'coop' => $coop ?? null,
    'pdfDownloadUrl' => route('reports.analytics', array_merge(request()->only(['coop_id']), ['download' => 1])),
])

@section('title', 'Laporan Analitik & Benchmarking Performa Kandang')

@section('content')
    <div class="doc-title">LAPORAN ANALITIK &amp; BENCHMARKING PERFORMA KANDANG</div>
    <div class="doc-subtitle">
        <strong>Cakupan: {{ $unitLabel }}</strong> |
        {{ count($analyticsData) }} siklus tutup buku &amp; {{ count($activeFlockBenchmarks ?? []) }} kandang aktif |
        Standar Target Bonus: IP &ge; 300
    </div>

    <!-- KPI Summary -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Rata-rata IP</div>
            <div class="kpi-value {{ ($farmAverages['ip'] ?? 0) >= 300 ? 'text-emerald' : 'text-indigo' }}">{{ number_format($farmAverages['ip'] ?? 0, 2, ',', '.') }}</div>
            <div class="kpi-unit">Indeks Penampilan</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Rata-rata FCR</div>
            <div class="kpi-value text-indigo">{{ number_format($farmAverages['fcr'] ?? 0, 3, ',', '.') }}</div>
            <div class="kpi-unit">Rasio Pakan / Kg</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Livability</div>
            <div class="kpi-value">{{ number_format($farmAverages['livability'] ?? 0, 2, ',', '.') }}%</div>
            <div class="kpi-unit">Daya Hidup</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Net Profit</div>
            <div class="kpi-value {{ ($farmAverages['total_net_profit'] ?? 0) >= 0 ? 'text-emerald' : 'text-rose' }}" style="font-size:14px;padding-top:3px;">
                Rp {{ number_format($farmAverages['total_net_profit'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="kpi-unit">Laba Bersih Panen</div>
        </div>
    </div>

    @if(empty($selectedCoop) && count($coopBenchmarks) > 0)
        <div class="section-title">Perbandingan Performa Antar Unit Kandang</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width:7%">Rank</th>
                    <th style="width:24%">Unit Kandang</th>
                    <th style="width:19%">Penanggung Jawab</th>
                    <th class="text-center" style="width:8%">Siklus</th>
                    <th class="text-center" style="width:10%">Rata IP</th>
                    <th class="text-center" style="width:9%">FCR</th>
                    <th class="text-center" style="width:10%">Livability</th>
                    <th class="text-right" style="width:13%">Total Net Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coopBenchmarks as $idx => $unit)
                    <tr>
                        <td class="text-center font-bold">#{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $unit['coop_name'] }}</td>
                        <td>{{ $unit['manager_name'] }}</td>
                        <td class="text-center">{{ $unit['cycles_count'] }}</td>
                        <td class="text-center font-bold {{ $unit['avg_ip'] >= 300 ? 'text-emerald' : 'text-rose' }}">{{ number_format($unit['avg_ip'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($unit['avg_fcr'], 3, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($unit['avg_livability'], 2, ',', '.') }}%</td>
                        <td class="text-right font-bold {{ $unit['total_net_profit'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                            Rp {{ number_format($unit['total_net_profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Rincian Performa per Siklus Panen</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th style="width:23%">Kandang &amp; Siklus</th>
                <th class="text-center" style="width:13%">Tgl Panen</th>
                <th style="width:15%">Pekerja / Ras</th>
                <th class="text-center" style="width:8%">Bobot</th>
                <th class="text-center" style="width:7%">FCR</th>
                <th class="text-center" style="width:8%">Hidup</th>
                <th class="text-right" style="width:12%">Net Profit</th>
                <th class="text-center" style="width:9%">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($analyticsData as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $row['flock_name'] }}</div>
                        <div class="text-sm" style="color:#64748b">{{ $row['coop_name'] }}</div>
                    </td>
                    <td class="text-center">
                        <div>{{ $row['closed_at'] }}</div>
                        <div class="text-sm" style="color:#64748b">{{ $row['harvest_age'] }} hr</div>
                    </td>
                    <td>
                        <div>{{ $row['worker_name'] }}</div>
                        <div class="text-sm" style="color:#64748b">{{ $row['breed'] }}</div>
                    </td>
                    <td class="text-center">{{ number_format($row['avg_weight_kg'], 2, ',', '.') }} kg</td>
                    <td class="text-center font-bold">{{ number_format($row['fcr'], 3, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($row['livability'], 2, ',', '.') }}%</td>
                    <td class="text-right font-bold {{ $row['net_profit'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                        Rp {{ number_format($row['net_profit'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="font-bold {{ $row['ip'] >= 300 ? 'text-emerald' : 'text-rose' }}">{{ number_format($row['ip'], 2, ',', '.') }}</span>
                        <div class="text-sm" style="color:#64748b">{{ $row['bonus_tier_label'] }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 18px; color: #64748b;">
                        Belum ada siklus panen selesai pada unit kandang ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($activeFlockBenchmarks))
        <div class="section-title">Monitoring Performa Kandang Sedang Aktif (Estimasi Berjalan)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:24%">Kandang &amp; Unit</th>
                    <th style="width:16%">Pekerja</th>
                    <th class="text-center" style="width:9%">Umur</th>
                    <th class="text-center" style="width:12%">Populasi Hidup</th>
                    <th class="text-center" style="width:10%">Bobot Akhir</th>
                    <th class="text-center" style="width:8%">Est. FCR</th>
                    <th class="text-center" style="width:8%">Est. IP</th>
                    <th class="text-right" style="width:13%">Est. Laba/Rugi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeFlockBenchmarks as $act)
                    <tr>
                        <td>
                            <div class="font-bold">{{ $act['flock_name'] }}</div>
                            <div class="text-sm" style="color:#64748b">{{ $act['coop_name'] }}</div>
                        </td>
                        <td>
                            <div>{{ $act['worker_name'] }}</div>
                            <div class="text-sm" style="color:#64748b">{{ $act['breed'] }}</div>
                        </td>
                        <td class="text-center">{{ $act['age_days'] }} / 35 hr</td>
                        <td class="text-center">
                            <div>{{ number_format($act['live_population'], 0, ',', '.') }} ekor</div>
                            <div class="text-sm" style="color:#64748b">{{ number_format($act['livability'], 2, ',', '.') }}%</div>
                        </td>
                        <td class="text-center">{{ number_format($act['avg_weight_gram'], 0, ',', '.') }} gr</td>
                        <td class="text-center font-bold">{{ number_format($act['est_fcr'], 3, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $act['est_ip'] >= 300 ? 'text-emerald' : 'text-indigo' }}">
                            {{ number_format($act['est_ip'], 2, ',', '.') }}
                        </td>
                        <td class="text-right font-bold {{ $act['estimated_profit'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                            Rp {{ number_format($act['estimated_profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top:8px;font-size:8.5px;color:#64748b">
        <strong>Keterangan Tier IP Bonus:</strong>
        IP &ge; 400 (Sangat Baik, 20%) | IP 350&ndash;399 (Baik, 15%) | IP 300&ndash;349 (Cukup / Standar, 10%) | IP &lt; 300 (Kurang, 0%).
        @if(!empty($activeFlockBenchmarks))
            <br><em>*Catatan: Metrik kandang aktif dihitung berdasarkan estimasi catatan harian terakhir dan dipisahkan dari rata-rata historis siklus tutup buku.</em>
        @endif
    </div>
@endsection
