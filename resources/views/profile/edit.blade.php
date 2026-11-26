<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash-alerts />

            <div class="p-4 sm:p-6 bg-white dark:bg-slate-800 shadow-md dark:shadow-none sm:rounded-2xl border border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        Preferensi Tampilan Tema
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Aktif saat ini:
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="themeMode === 'system' ? 'Ikuti Sistem (Auto)' : (themeMode === 'dark' ? 'Mode Gelap (Dark)' : 'Mode Terang (Light)')"></span>
                    </p>
                </div>
                <x-theme-switcher />
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
