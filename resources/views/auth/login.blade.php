<x-guest-layout>
    <div class="min-h-screen flex selection:bg-indigo-500 selection:text-white font-sans">
        
        <!-- Left Side: Enterprise Branding / Hero (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden flex-col justify-between p-12">
            <!-- Decorative Abstract Background -->
            <div class="absolute inset-0 z-0">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-900/40 via-slate-900 to-slate-900"></div>
                <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-emerald-600/20 rounded-full blur-[100px] mix-blend-screen pointer-events-none"></div>
                <!-- Grid Pattern -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)] opacity-30"></div>
            </div>

            <!-- Header -->
            <div class="relative z-10">
                <a href="/" class="flex items-center space-x-3 text-white group">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 transition-transform duration-300 group-hover:scale-110">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-2xl tracking-tight">Broiler<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">App</span></span>
                </a>
            </div>

            <!-- Value Proposition -->
            <div class="relative z-10 max-w-lg mb-20">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-6">
                    Enterprise Edition
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight mb-6">
                    Kendalikan Peternakan Anda dari Balik Meja.
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed mb-8">
                    Pantau FCR, deteksi kecurangan pakan, dan analisis Break-Even Point (BEP) secara real-time. Dibangun khusus untuk presisi dan profitabilitas tinggi.
                </p>
                
                <div class="flex items-center space-x-4">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-slate-900" src="https://ui-avatars.com/api/?name=Owner&background=4f46e5&color=fff" alt="Owner">
                        <img class="w-10 h-10 rounded-full border-2 border-slate-900" src="https://ui-avatars.com/api/?name=Worker+1&background=10b981&color=fff" alt="Worker">
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center text-xs text-white font-bold">+9</div>
                    </div>
                    <span class="text-sm font-medium text-slate-400">Dipercaya oleh ribuan siklus panen.</span>
                </div>
            </div>

            <!-- Footer Quote -->
            <div class="relative z-10 text-slate-500 text-sm font-medium">
                Sistem Manajemen Peternakan Broiler (PKL) &copy; {{ date('Y') }}
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-slate-900 p-6 sm:p-12 lg:p-24 relative transition-colors">
            
            <div class="w-full max-w-md">
                
                <!-- Mobile Logo Header -->
                <div class="lg:hidden flex items-center justify-center mb-10 space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-slate-900 dark:text-white">BroilerApp</span>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Masuk ke Portal</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium">Masukkan email dan kata sandi akses Anda.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-5">
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Email Pekerja / Pemilik
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="nama@peternakan.com"
                                class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3.5 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm sm:leading-6 transition-colors">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Kata Sandi
                            </label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3.5 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm sm:leading-6 transition-colors">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-2">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-sm text-slate-600 dark:text-slate-300 font-medium group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Ingat Perangkat Ini</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors" href="{{ route('password.request') }}">
                                Lupa sandi?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center items-center py-4 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Masuk ke Dashboard &rarr;
                        </button>
                    </div>
                </form>

                @if(app()->isLocal())
                    <!-- Akses Cepat Akun Demo PKL (Hanya tampil di environment local) -->
                    <div class="mt-8 rounded-2xl border border-indigo-200/80 dark:border-indigo-800/70 bg-indigo-50/70 dark:bg-slate-800/80 p-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                                Akses Cepat Demo PKL (Lokal)
                            </span>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-white/80 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-indigo-100 dark:border-slate-600">
                                Pass: password
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @php
                                $demoAccounts = [
                                    ['label' => 'Owner / Pemilik', 'sub' => 'Kontrol Penuh', 'email' => 'owner@smartcoop.com'],
                                    ['label' => 'Harries', 'sub' => 'Kandang Hanau', 'email' => 'harries@smartcoop.com'],
                                    ['label' => 'Danang', 'sub' => 'Kandang Seruyan Utama', 'email' => 'danang@smartcoop.com'],
                                    ['label' => 'Mujawiri', 'sub' => 'Kandang Plasma C', 'email' => 'mujawiri@smartcoop.com'],
                                ];
                            @endphp
                            @foreach($demoAccounts as $demo)
                                <button type="button"
                                    onclick="document.getElementById('email').value='{{ $demo['email'] }}';document.getElementById('password').value='password';document.getElementById('email').focus();"
                                    class="text-left rounded-xl px-3 py-2 bg-white dark:bg-slate-900/70 hover:bg-indigo-100/70 dark:hover:bg-indigo-900/40 border border-slate-200/80 dark:border-slate-700 transition-colors group">
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-700 dark:group-hover:text-indigo-300">
                                        {{ $demo['label'] }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        {{ $demo['sub'] }} &bull; {{ $demo['email'] }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Footer Access Info -->
                <div class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Pekerja baru?
                    @if(config('app.allow_public_registration'))
                        <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold ms-1 transition-colors">
                            Daftar Akun
                        </a>
                    @else
                        <span class="text-slate-700 dark:text-slate-200 font-semibold ms-1">Hubungi Pemilik / Admin untuk aktivasi akun Anda.</span>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
