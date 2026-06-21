<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Wix+Madefor+Text:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important}</style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen overflow-hidden bg-gradient-to-tr from-blue-800 via-blue-600 to-blue-500">
            {{-- Aksen dekoratif lembut --}}
            <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
                <a href="/" class="mb-6">
                    <img src="{{ asset('images/kasiro-logo-white.png') }}" alt="{{ config('app.name') }}" class="h-7 w-auto">
                </a>

                <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl shadow-blue-900/20 sm:p-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
