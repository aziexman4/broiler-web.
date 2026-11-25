<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-alerts />

            <div class="p-4 sm:p-6 bg-white dark:bg-slate-800 shadow-md dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        Preferensi Tampilan Tema
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Aktif saat ini:
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="darkMode ? 'Mode Gelap (Dark Mode)' : 'Mode Terang (Eye-Care Light)'"></span>
                    </p>
                </div>
                <button type="button"
                        @click="toggleTheme()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-100 text-xs font-semibold uppercase tracking-wider border border-slate-200 dark:border-slate-600 transition-colors">
                    <span x-show="!darkMode">Aktifkan Dark Mode</span>
                    <span x-show="darkMode" style="display: none;">Aktifkan Light Mode</span>
                </button>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow-md dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700 transition-colors">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow-md dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700 transition-colors">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-slate-800 shadow-md dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700 transition-colors">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
