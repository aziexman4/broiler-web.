<x-guest-layout>
    <div class="min-h-screen flex selection:bg-emerald-500 selection:text-white font-sans">

        <!-- Left Side: Agritech PKL Branding (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden flex-col justify-between p-12">
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
                    Registrasi Akun
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight mb-6">
                    Kelola Unit Kandang Broiler Secara Terukur.
                </h1>
                <p class="text-slate-300 text-base leading-relaxed mb-8">
                    Siapkan akses pemantauan siklus kandang, inventaris pakan, dan rekapitulasi laba panen dalam satu portal terpusat.
                </p>
            </div>

            <!-- Footer Quote -->
            <div class="relative z-10 text-slate-400 text-xs font-medium">
                SmartCoop — Peternakan Broiler Hanau &bull; Proyek PKL {{ date('Y') }}
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-slate-900 p-6 sm:p-10 lg:p-10 xl:p-14 relative overflow-y-auto max-h-screen transition-colors">
            <div class="absolute top-5 right-5 sm:top-6 sm:right-8 z-20">
                <x-theme-switcher />
            </div>

            <div class="w-full max-w-md py-6 lg:py-2">

                <!-- Mobile Logo Header -->
                <div class="lg:hidden flex items-center justify-center mb-8 space-x-2.5">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-xl leading-none">🐓</span>
                    </div>
                    <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Smart<span class="text-emerald-600 dark:text-emerald-400">Coop</span></span>
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Daftar Akun Baru</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium">Lengkapi data diri Anda untuk membuat akun SmartCoop.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Nama Lengkap
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                            placeholder="Cth: Budi Santoso"
                            class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Email
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                            placeholder="nama@smartcoop.com"
                            class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Kata Sandi
                        </label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Konfirmasi Kata Sandi
                        </label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            placeholder="Ulangi kata sandi"
                            class="block w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-3 px-4 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button type="submit" class="w-full flex justify-center items-center py-4 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Buat Akun Peternakan &rarr;
                        </button>
                    </div>
                </form>

                <!-- Footer Login Link -->
                <div class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold ms-1 transition-colors">
                        Masuk di sini
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
