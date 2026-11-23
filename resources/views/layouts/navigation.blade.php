<!-- Desktop Sidebar -->
<aside class="hidden md:flex md:flex-col md:w-64 bg-slate-900 text-slate-300 border-r border-slate-800 flex-shrink-0">
    <!-- Sidebar Header / Branding -->
    <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <span class="text-2xl">🐓</span>
            <span class="font-extrabold text-lg text-white tracking-wider">BroilerApp <span class="text-xs text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">PRO</span></span>
        </a>
    </div>

    <!-- Sidebar Navigation Links -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto px-4 py-6 space-y-6">
        <nav class="space-y-6">
            <!-- Menu Utama Section -->
            <div class="space-y-1.5">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Menu Utama
                </div>

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 dark:text-slate-300 hover:bg-slate-800 dark:hover:bg-slate-800 hover:text-white dark:hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <!-- Daftar Kandang -->
                <a href="{{ route('flocks.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('flocks.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('flocks.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Daftar Kandang
                </a>

                <!-- Gudang Pakan (Owner) -->
                @if(Auth::user()->isOwner())
                    <a href="{{ route('feeds.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('feeds.*') || request()->routeIs('feed.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('feeds.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Stok Gudang Pakan
                    </a>
                @endif

                <!-- Slip Gaji Saya (Worker) -->
                @if(Auth::user()->isWorker())
                    <a href="{{ route('worker.payslips.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('worker.payslips.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 dark:text-slate-300 hover:bg-slate-800 dark:hover:bg-slate-800 hover:text-white dark:hover:text-white' }}">
                        <svg class="w-5 h-5 me-3 {{ request()->routeIs('worker.payslips.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Slip Gaji Saya
                    </a>
                @endif

            </div>

            <!-- Modul Enterprise -->
            @can('manage-enterprise')
            <div class="space-y-1.5">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Enterprise
                </div>

                <!-- Unit & Lokasi Kandang -->
                <a href="{{ route('coops.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('coops.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('coops.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Unit & Lokasi Kandang
                </a>

                <!-- Analitik & Benchmarking -->
                <a href="{{ route('analytics.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('analytics.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('analytics.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analitik & Benchmarking
                </a>

                <!-- Riwayat Finansial -->
                <a href="{{ route('financial.history') }}"  
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('financial.history') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('financial.history') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Riwayat Finansial
                </a>

                <!-- Manajemen Pegawai (HRD) -->
                <a href="{{ route('workers.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('workers.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('workers.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen Pegawai
                </a>
                
                <a href="{{ route('activity_logs.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('activity_logs.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('activity_logs.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Log Aktivitas
                </a>

                <!-- Penggajian (Payroll) -->
                <a href="{{ route('payrolls.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('payrolls.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('payrolls.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Penggajian (Payroll)
                </a>

                <!-- Manajemen OVK (Obat/Vaksin) -->
                <a href="{{ route('ovk.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('ovk.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('ovk.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 00-1.18.12l-.3.12a2 2 0 00-1.02 2.58l.64 1.54a2 2 0 002.58 1.02l.3-.12a2 2 0 001.18-.12l2.38.48a6 6 0 003.86-.52l.32-.16a6 6 0 013.86-.52l2.38.48a2 2 0 001.18-.12l.3-.12a2 2 0 001.02-2.58l-.64-1.54z" />
                    </svg>
                    Manajemen OVK
                </a>

                <!-- Arus Kas -->
                <a href="{{ route('cash-flows.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('cash-flows.*') ? 'bg-indigo-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 me-3 text-slate-400 {{ request()->routeIs('cash-flows.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Arus Kas
                </a>
            </div>
            @endcan
        </nav>

        <!-- User Info Footer in Sidebar -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/50 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white font-bold text-sm shadow-md flex-shrink-0">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3">
                <span class="inline-flex items-center justify-center w-full px-2 py-1 text-[10px] font-bold uppercase rounded-lg tracking-wider border {{ Auth::user()->isOwner() ? 'bg-indigo-900/40 text-indigo-300 border-indigo-700/50' : 'bg-emerald-900/40 text-emerald-300 border-emerald-700/50' }}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Role: {{ Auth::user()->role }}
                </span>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Drawer -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-[cubic-bezier(0.4,0,0.2,1)] duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-[cubic-bezier(0.4,0,0.2,1)] duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden"
     @click="sidebarOpen = false">
</div>

<div x-show="sidebarOpen"
     x-transition:enter="transition ease-[cubic-bezier(0.4,0,0.2,1)] duration-300 transform"
     x-transition:enter-start="-translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="-translate-x-full"
     class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col md:hidden shadow-2xl">
    
    <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <span class="text-2xl">🐓</span>
            <span class="font-extrabold text-lg text-white">BroilerApp PRO</span>
        </a>
        <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-6">
        <nav class="space-y-6">
            <div class="space-y-1">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Menu Utama
                </div>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 dark:text-slate-300 hover:bg-slate-800 dark:hover:bg-slate-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('flocks.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('flocks.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Daftar Kandang
                </a>

                @if(Auth::user()->isOwner())
                    <a href="{{ route('feed.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('feed.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                        Stok Gudang Pakan
                    </a>
                @endif

                @if(Auth::user()->isWorker())
                    <a href="{{ route('worker.payslips.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 {{ request()->routeIs('worker.payslips.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 dark:text-slate-300 hover:bg-slate-800 dark:hover:bg-slate-800 hover:text-white dark:hover:text-white' }}">
                        Slip Gaji Saya
                    </a>
                @endif

            </div>

            @can('manage-enterprise')
            <div class="space-y-1">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Enterprise
                </div>
                <a href="{{ route('coops.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('coops.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Unit & Lokasi Kandang
                </a>

                <a href="{{ route('analytics.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('analytics.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Analitik & Benchmarking
                </a>

                <a href="{{ route('financial.history') }}"  
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('financial.history') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Riwayat Finansial
                </a>

                <a href="{{ route('workers.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('workers.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Manajemen Pegawai
                </a>
                <a href="{{ route('activity_logs.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('activity_logs.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Log Aktivitas
                </a>

                <a href="{{ route('payrolls.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('payrolls.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Penggajian (Payroll)
                </a>

                <a href="{{ route('ovk.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('ovk.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Manajemen OVK
                </a>

                <a href="{{ route('cash-flows.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('cash-flows.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">
                    Arus Kas
                </a>
            </div>
            @endcan
        </nav>
    </div>
</div>
