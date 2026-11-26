<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartCoop — Sistem Manajemen Produktivitas Broiler Hanau</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|outfit:400,600,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.theme-init')

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Outfit', sans-serif; }
        .animate-gradient-x {
            background-size: 200% auto;
            animation: gradient-x 4s linear infinite;
        }
        @keyframes gradient-x {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-marquee {
            animation: marquee 22s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</head>
<body x-data="{
        scrolled: false,
        themeMode: window.SmartCoopTheme ? window.SmartCoopTheme.getMode() : 'system',
        setTheme(mode) {
            const res = window.SmartCoopTheme.apply(mode);
            this.themeMode = res.mode;
        }
    }"
    @theme-changed.window="themeMode = $event.detail.mode"
    @scroll.window="scrolled = (window.pageYOffset > 20)"
    class="antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 selection:bg-emerald-500 selection:text-white transition-colors duration-200">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300"
         :class="scrolled ? 'bg-white/90 dark:bg-slate-900/90 backdrop-blur-md shadow-sm border-b border-slate-200/80 dark:border-slate-800' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25">
                        <span class="text-xl leading-none">🐓</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Smart<span class="text-emerald-600 dark:text-emerald-400">Coop</span></span>
                        <span class="hidden sm:inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300/70 dark:border-emerald-800">HANAU</span>
                    </div>
                </div>

                <!-- Right Controls -->
                <div class="flex items-center gap-3">
                    <x-theme-switcher />
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="font-semibold text-sm text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-white bg-slate-200/70 dark:bg-slate-800 px-4 py-2 rounded-xl transition-all">Dashboard &rarr;</a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white transition-all duration-200 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 hover:-translate-y-0.5">
                                Masuk (Login)
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden min-h-screen flex items-center">
        <div class="absolute inset-0 -z-10 pointer-events-none">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-500/15 dark:bg-emerald-500/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-10 right-1/4 w-[450px] h-[450px] bg-teal-500/15 dark:bg-teal-500/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 dark:bg-emerald-500/10 border border-emerald-300/70 dark:border-emerald-500/25 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-semibold mb-8">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600 dark:bg-emerald-400"></span>
                </span>
                Proyek Praktik Kerja Lapangan (PKL) {{ date('Y') }} &bull; Kab. Seruyan
            </div>

            <h1 class="font-heading text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6 leading-tight">
                Monitoring Kandang Terpadu,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-600 dark:from-emerald-400 dark:via-teal-300 dark:to-emerald-400 animate-gradient-x">Optimalisasi Produktivitas Broiler.</span>
            </h1>

            <p class="mt-4 max-w-2xl text-base sm:text-lg text-slate-600 dark:text-slate-400 mx-auto mb-10 leading-relaxed">
                Sistem informasi manajemen kepegawaian dan pemantauan harian peternakan ayam broiler untuk <strong>Kandang Hanau</strong>, <strong>Kandang Seruyan Utama</strong>, dan <strong>Kandang Plasma C</strong>. Dilengkapi <strong>Live BEP Forecasting</strong>, <strong>Validasi Biologis Pakan</strong>, dan perhitungan bonus <strong>Indeks Penampilan (IP)</strong> otomatis.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:from-emerald-700 hover:to-teal-700 shadow-xl shadow-emerald-600/25 hover:-translate-y-0.5 transition-all duration-300 w-full sm:w-auto">
                            Masuk ke Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:from-emerald-700 hover:to-teal-700 shadow-xl shadow-emerald-600/25 hover:-translate-y-0.5 transition-all duration-300 w-full sm:w-auto">
                            Login Portal SmartCoop
                        </a>
                    @endauth
                @endif
                <a href="#features" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-300 w-full sm:w-auto shadow-xs">
                    Jelajahi Modul &darr;
                </a>
            </div>
        </div>
    </div>

    <!-- Tech & Feature Marquee -->
    <div class="py-8 bg-white dark:bg-slate-900 border-y border-slate-200 dark:border-slate-800 overflow-hidden relative z-20">
        <div class="flex space-x-12 animate-marquee whitespace-nowrap items-center opacity-80">
            @for ($loopIdx = 0; $loopIdx < 2; $loopIdx++)
                <div class="flex space-x-12 items-center text-sm sm:text-base font-bold text-slate-500 dark:text-slate-400">
                    <span>Kandang Hanau</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Kandang Seruyan Utama</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Kandang Plasma C</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Live BEP Forecasting</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Benchmarking IP &amp; FCR</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Anti-Fraud Pakan 3 Fase</span>
                    <span class="text-emerald-500">&bull;</span>
                    <span>Payroll Berbasis IP</span>
                    <span class="text-emerald-500">&bull;</span>
                </div>
            @endfor
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-slate-100/70 dark:bg-slate-950 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="font-heading text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">Modul Utama SmartCoop</h2>
                <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg">Dirancang khusus untuk menjawab kebutuhan operasional harian pekerja kandang dan evaluasi finansial pemilik.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-500/15 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-slate-900 dark:text-white mb-3">Live BEP &amp; Analitik IP</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Memantau titik impas (Break-Even Point) harian secara real-time, membandingkan FCR &amp; bobot panen antar unit kandang, serta mencetak laporan PDF resmi ber-kop.</p>
                </div>

                <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-14 h-14 bg-teal-100 dark:bg-teal-500/15 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-slate-900 dark:text-white mb-3">Logistik &amp; Validasi Pakan</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Alur permintaan, pengiriman, dan penerimaan pakan Pre-Starter, Starter, &amp; Finisher yang terkontrol dengan batas kapasitas biologis harian.</p>
                </div>

                <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-14 h-14 bg-amber-100 dark:bg-amber-500/15 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-slate-900 dark:text-white mb-3">Early Warning &amp; Payroll</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Peringatan dini saat angka kematian harian melampaui ambang aman 0.5%, terintegrasi dengan perhitungan slip gaji dan bonus IP pekerja otomatis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🐓</span>
                <span class="font-heading font-extrabold text-xl text-slate-900 dark:text-white">Smart<span class="text-emerald-600 dark:text-emerald-400">Coop</span></span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm text-center md:text-right">
                &copy; {{ date('Y') }} Proyek Praktik Kerja Lapangan (PKL) — Peternakan Broiler Hanau.<br>
                Dikembangkan oleh <span class="text-slate-800 dark:text-slate-200 font-semibold">M Aulia Aziz 2310020119 — FTI UNISKA Banjarmasin</span>
            </p>
        </div>
    </footer>
</body>
</html>
