<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BroilerApp PRO') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

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

    </head>
    <body class="font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
        {{ $slot }}
    </body>
</html>
