<x-guest-layout>
    <div class="min-h-screen flex selection:bg-emerald-500 selection:text-white font-sans">

        <!-- Left Side: Agritech PKL Branding (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden flex-col justify-between p-12">
            <!-- Decorative Abstract Background -->
            <div class="absolute inset-0 z-0">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-900/45 via-slate-900 to-slate-950"></div>
                <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-teal-500/20 rounded-full blur-[100px] mix-blend-screen pointer-events-none"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)] opacity-30"></div>
            </div>

            <!-- Header -->
            <div class="relative z-10">
                <a href="/" class="flex items-center space-x-3 text-white group">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25 transition-transform duration-300 group-hover:scale-110">
                        <span class="text-xl leading-none">🐓</span>
                    </div>
                    <span class="font-extrabold text-2xl tracking-tight">Smart<span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Coop</span></span>
                </a>
            </div>

            <!-- Value Proposition -->
            <div class="relative z-10 max-w-lg mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/70 border border-emerald-700/50 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-6">
                    Studi Kasus PKL &bull; Kab. Seruyan
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight mb-6">
                    Monitoring Terpadu Produktivitas Kandang Ayam Broiler.
                </h1>
                <p class="text-slate-300 text-base leading-relaxed mb-8">
                    Kelola pencatatan pakan harian 3 fase, proyeksi Break-Even Point (BEP), Indeks Penampilan (IP), hingga penggajian berbasis performa pada unit Kandang Hanau, Seruyan Utama, dan Plasma C.
                </p>

                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-800/80 text-xs">
                    <div class="rounded-xl bg-slate-800/60 border border-slate-700/70 p-3">
                        <div class="font-extrabold text-emerald-400 text-base">3 Unit</div>
                        <div class="text-slate-400 mt-0.5">Kandang Aktif</div>
                    </div>
                    <div class="rounded-xl bg-slate-800/60 border border-slate-700/70 p-3">
                        <div class="font-extrabold text-teal-300 text-base">IP &ge; 300</div>
                        <div class="text-slate-400 mt-0.5">Standar Bonus</div>
                    </div>
                    <div class="rounded-xl bg-slate-800/60 border border-slate-700/70 p-3">
                        <div class="font-extrabold text-amber-300 text-base">Real-time</div>
                        <div class="text-slate-400 mt-0.5">BEP &amp; EWS</div>
                    </div>
                </div>
            </div>

            <!-- Footer Quote -->
            <div class="relative z-10 text-slate-400 text-xs font-medium">
                SmartCoop — Peternakan Broiler Hanau &bull; Proyek PKL {{ date('Y') }}
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-slate-900 p-6 sm:p-10 lg:p-10 xl:p-14 relative overflow-y-auto transition-colors">
            <!-- Top-Right 3-Mode Theme Switcher -->
            <div class="absolute top-5 right-5 sm:top-6 sm:right-8 z-20">
                <x-theme-switcher />
            </div>

            <div class="w-full max-w-md pt-8 sm:pt-4 lg:pt-0">

                <!-- Mobile Logo Header -->
                <div class="lg:hidden flex items-center justify-center mb-6 space-x-2.5">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-xl leading-none">🐓</span>
                    </div>
                    <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Smart<span class="text-emerald-600 dark:text-emerald-400">Coop</span></span>
                </div>

                <div class="mb-6 text-center lg:text-left">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Masuk ke Portal</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 font-medium">Masukkan email dan kata sandi akun SmartCoop Anda.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-4">
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Email Pekerja / Pemilik
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="nama@smartcoop.com"
                                class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Kata Sandi
                            </label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            <span class="ms-2 text-sm text-slate-600 dark:text-slate-300 font-medium group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Ingat Perangkat Ini</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors" href="{{ route('password.request') }}">
                                Lupa sandi?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Masuk ke Dashboard &rarr;
                        </button>
                    </div>
                </form>

                @if(app()->isLocal())
                    <!-- Akses Cepat Akun Demo PKL (Hanya tampil di environment local) -->
                    <div class="mt-5 rounded-2xl border border-emerald-200/90 dark:border-emerald-800/70 bg-emerald-50/70 dark:bg-slate-800/80 p-3.5">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                                Akses Cepat Demo PKL (Lokal)
                            </span>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-white/90 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-emerald-200 dark:border-slate-600">
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
                                    class="text-left rounded-xl px-3 py-2 bg-white dark:bg-slate-900/70 hover:bg-emerald-100/70 dark:hover:bg-emerald-900/40 border border-slate-200/80 dark:border-slate-700 transition-colors group">
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-300">
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
                        <a href="{{ route('register') }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold ms-1 transition-colors">
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
