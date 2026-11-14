<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laporan') - BroilerApp PRO</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            background: #e2e8f0;
            padding-top: 70px;
            padding-bottom: 40px;
        }
        /* Floating Toolbar */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .print-toolbar .toolbar-title {
            color: #e0e7ff;
            font-size: 14px;
            font-weight: 600;
        }
        .print-toolbar .toolbar-actions { display: flex; gap: 10px; }
        .btn-download {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-download:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4); }
        .btn-cancel {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px;
            background: transparent;
            color: #c7d2fe;
            border: 1px solid #6366f1;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-cancel:hover { background: rgba(99, 102, 241, 0.1); color: #fff; }

        /* A4 Paper Style */
        .paper {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 30px 40px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            min-height: 297mm;
        }

        /* Kop Surat */
        .letterhead { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
        .letterhead-logo { font-size: 20px; font-weight: 700; color: #1e1b4b; letter-spacing: 0.5px; }
        .letterhead-subtitle { font-size: 10px; color: #4f46e5; font-weight: 600; margin-top: 2px; }
        .letterhead-address { text-align: right; font-size: 9px; color: #64748b; line-height: 1.6; }
        .divider { border-bottom: 2.5px solid #1e1b4b; margin-bottom: 16px; }

        /* Document Title */
        .doc-title { text-align: center; font-size: 14px; font-weight: 700; text-transform: uppercase; color: #0f172a; margin-bottom: 16px; letter-spacing: 0.5px; }
        .doc-subtitle { text-align: center; font-size: 10px; color: #64748b; margin-top: -12px; margin-bottom: 16px; }

        /* Section Titles */
        .section-title { font-size: 11px; font-weight: 700; color: #1e293b; background: #f1f5f9; padding: 6px 10px; border-left: 4px solid #4f46e5; margin-top: 18px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }

        /* Data Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; font-size: 10.5px; }
        .data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
        .data-table tr:nth-child(even) td { background: #fafbfc; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: 700; }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .text-indigo { color: #4f46e5; }
        .text-sm { font-size: 9px; }

        /* Signature Block */
        .signature-block { display: flex; justify-content: flex-end; margin-top: 40px; }
        .signature-box { width: 200px; text-align: center; font-size: 10px; }
        .signature-space { height: 60px; }

        /* KPI Badge */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
        .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; text-align: center; }
        .kpi-label { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }
        .kpi-value { font-size: 18px; font-weight: 700; color: #1e293b; margin-top: 2px; }
        .kpi-unit { font-size: 9px; color: #94a3b8; }

        /* Print-specific styles */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .print-toolbar { display: none !important; }
            .paper { box-shadow: none; margin: 0; padding: 20px 30px; border-radius: 0; max-width: none; }
            @page { margin: 15mm 10mm; size: A4 portrait; }
        }

        @yield('extra-styles')
    </style>
</head>
<body>

    <!-- Floating Toolbar -->
    <div class="print-toolbar no-print">
        <div class="toolbar-title">📄 Pratinjau Laporan — @yield('title', 'BroilerApp PRO')</div>
        <div class="toolbar-actions">
            <button class="btn-download" onclick="window.print()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh PDF / Cetak
            </button>
            <a class="btn-cancel" href="{{ url()->previous() }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batal
            </a>
        </div>
    </div>

    <!-- A4 Paper -->
    <div class="paper">
        <!-- Letterhead -->
        <div class="letterhead">
            <div>
                <div class="letterhead-logo">🐓 SMARTCOOP SOLUTIONS</div>
                <div class="letterhead-subtitle">Sistem Manajemen Peternakan Broiler Modern & Enterprise</div>
            </div>
            <div class="letterhead-address">
                Jl. Raya Industri Peternakan No. 88, Jawa Barat<br>
                Email: support@smartcoop.id | Telp: (021) 555-0199<br>
                Website: www.broilerapp-pro.id
            </div>
        </div>
        <div class="divider"></div>

        @yield('content')

        <!-- Signature Block -->
        @hasSection('signature')
            @yield('signature')
        @else
            <div class="signature-block">
                <div class="signature-box">
                    <div>Mengetahui,</div>
                    <div class="font-bold" style="margin-top: 4px;">Pemilik / Manajemen</div>
                    <div class="signature-space"></div>
                    <div class="font-bold">( _______________________ )</div>
                    <div class="text-sm" style="color: #64748b; margin-top: 2px;">SmartCoop Solutions Pro</div>
                </div>
            </div>
        @endif

        <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8;">
            Dokumen ini digenarasi secara otomatis oleh BroilerApp PRO pada {{ now()->format('d M Y, H:i') }} WIB.<br>
            Sistem dikembangkan khusus untuk Program P2MW oleh <strong>M Aulia Aziz 231002019</strong>.
        </div>
    </div>

</body>
</html>
