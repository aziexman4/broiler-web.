<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BroilerApp Pro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dark Mode FOIT Prevention -->
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.backgroundColor = '#020617'; // slate-950
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.backgroundColor = '#f1f5f9'; // slate-100
            }

            window.BroilerTheme = {
                isDark() {
                    return document.documentElement.classList.contains('dark');
                },
                palette() {
                    const dark = this.isDark();
                    return {
                        dark,
                        text: dark ? '#cbd5e1' : '#475569',
                        title: dark ? '#f8fafc' : '#0f172a',
                        grid: dark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(15, 23, 42, 0.06)',
                        tooltipBg: dark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.96)',
                        tooltipTitle: dark ? '#f8fafc' : '#0f172a',
                        tooltipBody: dark ? '#cbd5e1' : '#475569',
                        tooltipBorder: dark ? 'rgba(51, 65, 85, 0.9)' : 'rgba(226, 232, 240, 1)',
                    };
                },
                applyChartOptions(rawOptions) {
                    if (!rawOptions || typeof rawOptions !== 'object') return rawOptions;
                    const p = this.palette();

                    rawOptions.plugins = rawOptions.plugins || {};
                    rawOptions.plugins.legend = rawOptions.plugins.legend || {};
                    rawOptions.plugins.legend.labels = rawOptions.plugins.legend.labels || {};
                    rawOptions.plugins.legend.labels.color = p.text;

                    rawOptions.plugins.tooltip = rawOptions.plugins.tooltip || {};
                    rawOptions.plugins.tooltip.backgroundColor = p.tooltipBg;
                    rawOptions.plugins.tooltip.titleColor = p.tooltipTitle;
                    rawOptions.plugins.tooltip.bodyColor = p.tooltipBody;
                    rawOptions.plugins.tooltip.borderColor = p.tooltipBorder;
                    rawOptions.plugins.tooltip.borderWidth = 1;

                    rawOptions.scales = rawOptions.scales || {};
                    Object.values(rawOptions.scales).forEach((scale) => {
                        if (!scale || typeof scale !== 'object') return;
                        scale.ticks = scale.ticks || {};
                        scale.ticks.color = p.text;
                        if (scale.title && typeof scale.title === 'object') {
                            scale.title.color = p.text;
                        }
                        if (!scale.grid || scale.grid.display !== false) {
                            scale.grid = scale.grid || {};
                            scale.grid.color = p.grid;
                        }
                    });

                    return rawOptions;
                },
                applyChart(chart, options = {}) {
                    if (!chart || !chart.config) return;
                    if (!chart.config.options) {
                        chart.config.options = {};
                    }
                    this.applyChartOptions(chart.config.options);
                    if (typeof options.onUpdate === 'function') {
                        options.onUpdate(chart, this.palette());
                    }
                    chart.update('none');
                },
                createChart(ctx, config, options = {}) {
                    config.options = config.options || {};
                    this.applyChartOptions(config.options);
                    const chart = new Chart(ctx, config);
                    window.addEventListener('theme-changed', () => this.applyChart(chart, options));
                    return chart;
                },
                registerChart(chart, options = {}) {
                    if (!chart) return chart;
                    this.applyChart(chart, options);
                    window.addEventListener('theme-changed', () => this.applyChart(chart, options));
                    return chart;
                }
            };
        </script>


        <style>
            /* Anti-Flashbang (Eye-Care) Light Mode */
            html:not(.dark) body {
                background-color: #f1f5f9 !important; /* slate-100 */
            }
            html:not(.dark) .bg-white {
                background-color: #f8fafc !important; /* slate-50 */
            }
            html:not(.dark) .bg-slate-50 {
                background-color: #f1f5f9 !important; /* slate-100 */
            }
        </style>
    </head>
    <body 
        x-data="{ 
            darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
            toggleTheme() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', this.darkMode);
                document.documentElement.style.backgroundColor = this.darkMode ? '#020617' : '#f1f5f9';
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.darkMode } }));
            }
        }"
        class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200 selection:bg-indigo-500 selection:text-white">
        


        <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-slate-50 dark:bg-slate-950 overflow-hidden transition-colors duration-200">
            <!-- Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Header Bar -->
                <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 z-10 transition-colors duration-200 shadow-md">
                    <div class="px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                        <!-- Mobile Hamburger Button -->
                        <div class="flex items-center md:hidden">
                            <button @click="sidebarOpen = !sidebarOpen" aria-label="Buka menu navigasi" class="p-2 -ms-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <span class="ms-2 font-bold text-slate-800 dark:text-white">BroilerApp Pro</span>
                        </div>

                        <!-- Top Header Title/Context -->
                        <div class="hidden md:block flex-1">
                            @isset($header)
                                {{ $header }}
                            @endisset
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="flex items-center space-x-3 ms-auto">
                            <!-- Dark Mode Toggle -->
                            <button @click="toggleTheme()" type="button" :aria-pressed="darkMode.toString()" aria-label="Ganti mode tampilan" title="Ganti Light / Dark Mode" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors duration-200">
                                <!-- Sun Icon (shown in dark mode) -->
                                <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 2.78a1 1 0 011.415 0l.707.707a1 1 0 01-1.414 1.414l-.708-.707a1 1 0 010-1.414zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-2.78 4.22a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4.22-2.78a1 1 0 01-1.415 0l-.707-.707a1 1 0 011.414-1.414l.708.707a1 1 0 010 1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm2.78-4.22a1 1 0 010-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414 0zM10 5a5 5 0 100 10 5 5 0 000-10z" clip-rule="evenodd" />
                                </svg>
                                <!-- Moon Icon (shown in light mode) -->
                                <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                            </button>

                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold uppercase {{ Auth::user()->isOwner() ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                {{ Auth::user()->role }}
                            </span>

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm leading-4 font-medium rounded-xl text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white focus:outline-none transition ease-in-out duration-150 shadow-sm">
                                        <div>{{ Auth::user()->name }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    <!-- Mobile Header Slot -->
                    @isset($header)
                        <div class="md:hidden px-4 py-3 bg-slate-50 dark:bg-slate-900/95 border-t border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 transition-colors duration-200">
                            {{ $header }}
                        </div>
                    @endisset
                </header>

                <!-- Scrollable Main Content -->
                <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 transition-colors duration-200 flex flex-col">
                    <div class="flex-1">
                        {{ $slot }}
                    </div>
                    
                    <!-- Global Credit Footer -->
                    <footer class="mt-auto py-6 px-6 sm:px-8 text-center sm:text-right border-t border-slate-200/60 dark:border-slate-800/60 mt-8">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold">
                            Sistem Manajemen Peternakan Broiler &bull; Proyek PKL {{ date('Y') }}
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Dikembangkan oleh <span class="font-bold text-slate-700 dark:text-slate-300">M Aulia Aziz 2310020119</span> — FTI UNISKA Banjarmasin
                        </p>
                    </footer>
                </main>
            </div>
        </div>

        <!-- Global Alpine Confirmation Modal -->
        <div x-data="confirmDialog()"
             @open-confirm-dialog.window="open($event.detail)"
             @keydown.escape.window="if (isOpen) cancel()"
             x-show="isOpen"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-[60] overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="confirm-dialog-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div x-show="isOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="cancel()"
                     class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

                <div x-show="isOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl border border-slate-200 dark:border-slate-800 transition-all w-full max-w-md p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-2xl flex items-center justify-center"
                             :class="{
                                 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400': tone === 'danger',
                                 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400': tone === 'emerald',
                                 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400': tone === 'primary'
                             }">
                            <svg x-show="tone === 'danger'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <svg x-show="tone !== 'danger'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 id="confirm-dialog-title" class="text-base font-bold text-slate-900 dark:text-slate-100" x-text="title"></h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed" x-text="message"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5">
                        <button type="button"
                                x-ref="cancelButton"
                                @click="cancel()"
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400">
                            Batal
                        </button>
                        <button type="button"
                                x-ref="confirmButton"
                                @click="confirm()"
                                :class="{
                                    'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500': tone === 'danger',
                                    'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500': tone === 'emerald',
                                    'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500': tone === 'primary'
                                }"
                                class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                x-text="confirmText">
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Toast Manager -->
        <div x-data="toastManager()" 
             @notify.window="add($event.detail)"
             class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none max-w-[calc(100vw-2rem)]">
            
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="toast.visible" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-4"
                     class="pointer-events-auto flex items-center p-4 w-full sm:w-80 text-slate-500 dark:text-slate-300 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700" 
                     role="alert">
                    
                    <!-- Icon based on type -->
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-xl"
                         :class="{
                             'text-emerald-500 bg-emerald-100 dark:bg-emerald-900/30': toast.type === 'success',
                             'text-rose-500 bg-rose-100 dark:bg-rose-900/30': toast.type === 'error',
                             'text-amber-500 bg-amber-100 dark:bg-amber-900/30': toast.type === 'warning',
                             'text-indigo-500 bg-indigo-100 dark:bg-indigo-900/30': toast.type === 'info'
                         }">
                        <svg x-show="toast.type === 'success'" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg>
                        <svg x-show="toast.type === 'error'" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/></svg>
                        <svg x-show="toast.type === 'warning' || toast.type === 'info'" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/></svg>
                    </div>
                    
                    <div class="ms-3 text-sm font-medium text-slate-800 dark:text-slate-200 leading-snug" x-text="toast.message"></div>
                    
                    <button @click="remove(toast.id)" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white dark:bg-slate-800 text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl focus:ring-2 focus:ring-gray-300 dark:focus:ring-slate-600 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 inline-flex items-center justify-center h-8 w-8 transition-colors flex-shrink-0" aria-label="Tutup notifikasi">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <script>
            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.dataset.confirmMessage || form.dataset.confirmed === 'true') return;

                event.preventDefault();
                window.dispatchEvent(new CustomEvent('open-confirm-dialog', {
                    detail: {
                        form,
                        title: form.dataset.confirmTitle || 'Konfirmasi Aksi',
                        message: form.dataset.confirmMessage,
                        confirmText: form.dataset.confirmButton || 'Ya, Lanjutkan',
                        tone: form.dataset.confirmTone || 'danger',
                    }
                }));
            });

            document.addEventListener('alpine:init', () => {
                Alpine.data('confirmDialog', () => ({
                    isOpen: false,
                    title: 'Konfirmasi Aksi',
                    message: '',
                    confirmText: 'Ya, Lanjutkan',
                    tone: 'danger',
                    pendingForm: null,

                    open(detail) {
                        this.pendingForm = detail.form || null;
                        this.title = detail.title || 'Konfirmasi Aksi';
                        this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                        this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
                        this.tone = detail.tone || 'danger';
                        this.isOpen = true;

                        this.$nextTick(() => {
                            if (this.tone === 'danger' && this.$refs.cancelButton) {
                                this.$refs.cancelButton.focus();
                            } else if (this.$refs.confirmButton) {
                                this.$refs.confirmButton.focus();
                            }
                        });
                    },

                    cancel() {
                        this.isOpen = false;
                        this.pendingForm = null;
                    },

                    confirm() {
                        const form = this.pendingForm;
                        this.isOpen = false;
                        this.pendingForm = null;

                        if (form) {
                            form.dataset.confirmed = 'true';
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    }
                }));

                Alpine.data('toastManager', () => ({
                    toasts: [],
                    nextId: 0,
                    add(toast) {
                        if (!toast || !toast.message) return;
                        const id = ++this.nextId;

                        if (this.toasts.length >= 4) {
                            this.toasts.shift();
                        }

                        this.toasts.push({
                            id: id,
                            message: toast.message,
                            type: toast.type || 'success',
                            visible: true
                        });
                        
                        setTimeout(() => {
                            this.remove(id);
                        }, toast.duration || 3500);
                    },
                    remove(id) {
                        const toast = this.toasts.find(t => t.id === id);
                        if (toast) {
                            toast.visible = false;
                            setTimeout(() => {
                                this.toasts = this.toasts.filter(t => t.id !== id);
                            }, 300); // Wait for transition
                        }
                    },
                    init() {
                        // Check for Laravel session flashes
                        @if(session('success'))
                            setTimeout(() => {
                                this.add({ message: @js(session('success')), type: 'success' });
                            }, 100);
                        @endif
                        @if(session('error'))
                            setTimeout(() => {
                                this.add({ message: @js(session('error')), type: 'error' });
                            }, 100);
                        @endif
                        @if(session('warning'))
                            setTimeout(() => {
                                this.add({ message: @js(session('warning')), type: 'warning' });
                            }, 100);
                        @endif
                        @if(session('info'))
                            setTimeout(() => {
                                this.add({ message: @js(session('info')), type: 'info' });
                            }, 100);
                        @endif
                    }
                }));
            });
        </script>
    </body>
</html>
