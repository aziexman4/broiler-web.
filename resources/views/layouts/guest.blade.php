<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ in_array(config('app.name'), ['Laravel', 'BroilerApp', 'BroilerApp PRO', 'BroilerApp Pro']) ? 'SmartCoop' : config('app.name', 'SmartCoop') }} — Manajemen Broiler</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @include('partials.theme-init')
    </head>
    <body x-data="{
            themeMode: window.SmartCoopTheme ? window.SmartCoopTheme.getMode() : 'system',
            setTheme(mode) {
                const res = window.SmartCoopTheme.apply(mode);
                this.themeMode = res.mode;
            }
        }"
        @theme-changed.window="themeMode = $event.detail.mode"
        class="font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-100 dark:bg-slate-950 selection:bg-emerald-500 selection:text-white transition-colors duration-200">
        {{ $slot }}
    </body>
</html>
