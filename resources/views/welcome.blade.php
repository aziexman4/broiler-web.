<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BroilerApp - Smart Poultry Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|outfit:400,600,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Outfit', sans-serif; }
        .hero-pattern {
            background-color: #0f172a;
            background-image: radial-gradient(circle at 15% 50%, rgba(79, 70, 229, 0.15), transparent 25%),
                              radial-gradient(circle at 85% 30%, rgba(16, 185, 129, 0.15), transparent 25%);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="antialiased bg-slate-900 text-slate-200 selection:bg-indigo-500 selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)" :class="{ 'bg-slate-900/90 backdrop-blur-md shadow-lg': scrolled, 'bg-transparent': !scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="font-heading font-bold text-2xl tracking-tight text-white">Broiler<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">App</span></span>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="font-semibold text-slate-200 hover:text-white hover:bg-slate-800 px-4 py-2 rounded-lg transition-all duration-300">Kembali ke Dashboard &rarr;</a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-indigo-600 border border-transparent rounded-full hover:bg-indigo-500 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 focus:ring-offset-slate-900">
                                Masuk (Login)
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 lg:pb-32 overflow-hidden hero-pattern min-h-screen flex items-center">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-semibold mb-8">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                Proyek Inovasi P2MW 2026
            </div>
            
            <h1 class="font-heading text-5xl sm:text-7xl font-extrabold tracking-tight text-white mb-8 leading-tight">
                Manajemen Peternakan Cerdas,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-emerald-400 to-indigo-400 animate-gradient-x">Maksimalkan Laba.</span>
            </h1>
            
            <p class="mt-4 max-w-2xl text-lg sm:text-xl text-slate-400 mx-auto mb-10 leading-relaxed">
                Platform SaaS Enterprise untuk peternakan ayam Broiler. Dilengkapi dengan <b>Live Forecasting</b>, <b>Anti-Fraud System</b>, dan analisis finansial cerdas yang bekerja secara real-time.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-full hover:bg-indigo-500 hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-1 transition-all duration-300 w-full sm:w-auto">
                            Masuk ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-white bg-indigo-600 rounded-full hover:bg-indigo-500 hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-1 transition-all duration-300 w-full sm:w-auto">
                            Login Sekarang
                        </a>
                    @endauth
                @endif
                <a href="#features" class="inline-flex justify-center items-center px-8 py-4 text-base font-bold text-slate-300 bg-slate-800 border border-slate-700 rounded-full hover:bg-slate-700 hover:text-white transition-all duration-300 w-full sm:w-auto">
                    Jelajahi Fitur &darr;
                </a>
            </div>
        </div>

        <!-- Decorative background elements -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-indigo-500/20 rounded-full blur-[120px] -z-10 mix-blend-screen pointer-events-none"></div>
    </div>

    <!-- Infinite Carousel Section -->
    <div class="py-10 bg-slate-900 border-t border-slate-800 overflow-hidden relative z-20">
        <div class="absolute left-0 top-0 w-32 h-full bg-gradient-to-r from-slate-900 to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 w-32 h-full bg-gradient-to-l from-slate-900 to-transparent z-10 pointer-events-none"></div>
        
        <div class="flex space-x-12 animate-marquee whitespace-nowrap items-center opacity-70">
            <!-- Set 1 -->
            <div class="flex space-x-12 items-center">
                <span class="text-xl font-bold text-slate-400">Laravel 11</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">TailwindCSS</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Alpine.js</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Live BEP Forecasting</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Anti-Fraud EWS</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
            </div>
            <!-- Set 2 (Duplicate for infinite scroll) -->
            <div class="flex space-x-12 items-center">
                <span class="text-xl font-bold text-slate-400">Laravel 11</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">TailwindCSS</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Alpine.js</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Live BEP Forecasting</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
                <span class="text-xl font-bold text-slate-400">Anti-Fraud EWS</span>
                <span class="text-xl font-bold text-slate-400">&bull;</span>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-slate-950 relative z-20 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="font-heading text-3xl sm:text-4xl font-bold text-white mb-4">Fitur Skala Enterprise</h2>
                <p class="text-slate-400 text-lg">BroilerApp tidak hanya merekap data, tapi juga memprediksi dan melindungi peternakan Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-2xl hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-500/20 transition-all duration-300 group cursor-default">
                    <div class="w-14 h-14 bg-indigo-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-500/20 transition-colors">
                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-3">Live BEP Forecasting</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Sistem cerdas kami menggambar target Titik Impas (BEP) secara real-time setiap kali pakan diberikan, memberikan Anda kendali finansial penuh hari demi hari.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-2xl hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-500/20 transition-all duration-300 group cursor-default">
                    <div class="w-14 h-14 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500/20 transition-colors">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-3">Anti-Fraud EWS</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Pekerja tidak bisa memanipulasi pakan. Algoritma biologi kami (Kapasitas Lambung Maksimum) akan mendeteksi dan memblokir input anomali pencurian pakan otomatis.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-2xl hover:-translate-y-2 hover:shadow-2xl hover:shadow-rose-500/20 transition-all duration-300 group cursor-default">
                    <div class="w-14 h-14 bg-rose-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-rose-500/20 transition-colors">
                        <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-3">Early Warning System</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Sistem mendeteksi tingkat kematian ayam (Mortality Rate) di atas 0.5% secara instan, memberikan peringatan visual merah agar pemilik bisa langsung mengambil tindakan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="font-heading font-bold text-xl text-white">Broiler<span class="text-emerald-400">App</span></span>
            </div>
            <p class="text-slate-500 text-sm text-center md:text-left">
                &copy; {{ date('Y') }} Dibangun khusus untuk Program P2MW.<br>
                Dikembangkan oleh <span class="text-slate-300 font-semibold">M Aulia Aziz 2310020119</span>
            </p>
        </div>
    </footer>

    <style>
        .animate-gradient-x {
            background-size: 200% auto;
            animation: gradient-x 3s linear infinite;
        }
        @keyframes gradient-x {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animate-marquee {
            animation: marquee 20s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</body>
</html>
