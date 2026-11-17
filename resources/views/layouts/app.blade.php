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
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
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
                            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-gray-700 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <span class="ms-3 font-bold text-slate-800 dark:text-white">BroilerApp Pro</span>
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
                            <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none transition-colors duration-200">
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
                                    <button class="inline-flex items-center px-3 py-2 border border-slate-200 dark:border-slate-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-700 hover:text-slate-900 dark:hover:text-white focus:outline-none transition ease-in-out duration-150 shadow-md">
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
                        <div class="md:hidden px-4 py-2 bg-slate-50 border-t border-slate-200">
                            {{ $header }}
                        </div>
                    @endisset
                </header>

                <!-- Scrollable Main Content -->
                <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-900 transition-colors duration-200 flex flex-col">
                    <div class="flex-1">
                        {{ $slot }}
                    </div>
                    
                    <!-- Global Credit Footer -->
                    <footer class="mt-auto py-6 px-6 sm:px-8 text-center sm:text-right border-t border-slate-200/60 dark:border-slate-800/60 mt-8">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold">
                            Sistem P2MW 2026
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Dikembangkan oleh <span class="font-bold text-slate-700 dark:text-slate-300">M Aulia Aziz 2310020119</span>
                        </p>
                    </footer>
                </main>
            </div>
        </div>

        <!-- Global Toast Manager -->
        <div x-data="toastManager()" 
             @notify.window="add($event.detail)"
             class="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
            
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="toast.visible" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-4"
                     class="flex items-center p-4 w-full max-w-xs text-slate-500 dark:text-slate-300 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700" 
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
                    
                    <div class="ms-3 text-sm font-medium text-slate-800 dark:text-slate-200" x-text="toast.message"></div>
                    
                    <button @click="remove(toast.id)" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white dark:bg-slate-800 text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl focus:ring-2 focus:ring-gray-300 dark:focus:ring-slate-600 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 inline-flex items-center justify-center h-8 w-8 transition-colors" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('toastManager', () => ({
                    toasts: [],
                    add(toast) {
                        const id = Date.now();
                        this.toasts.push({
                            id: id,
                            message: toast.message,
                            type: toast.type || 'success',
                            visible: true
                        });
                        
                        setTimeout(() => {
                            this.remove(id);
                        }, toast.duration || 3000);
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
                                this.add({ message: '{{ addslashes(session('success')) }}', type: 'success' });
                            }, 100);
                        @endif
                        @if(session('error'))
                            setTimeout(() => {
                                this.add({ message: '{{ addslashes(session('error')) }}', type: 'error' });
                            }, 100);
                        @endif
                    }
                }));
            });
        </script>
    </body>
</html>
