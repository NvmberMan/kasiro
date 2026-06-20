<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-styles :config="$tenant->theme_config ?? []" />
    <style>
        body { background-color: var(--brand-bg); color: var(--brand-fg); }
        .brand-border { border-color: var(--brand-primary); }
        .brand-text { color: var(--brand-primary); }
    </style>
</head>
<body class="antialiased">
    <header class="border-b-4 brand-border bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            @if (!empty($tenant->logo_path))
                <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-8 w-8 rounded object-cover">
            @endif
            <span class="font-serif font-bold text-xl brand-text">{{ $tenant->name }}</span>
            <span class="ml-1 text-xs text-gray-400 font-normal">— Classic</span>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
