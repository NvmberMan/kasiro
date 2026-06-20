<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-styles :config="$tenant->theme_config ?? []" />
    <style>
        body { background-color: var(--brand-bg); color: var(--brand-fg); font-family: monospace; }
        .brand-box { background-color: var(--brand-primary); color: #fff; }
        .brand-accent { background-color: var(--brand-accent); }
    </style>
</head>
<body class="antialiased">
    <header class="brand-box">
        <div class="mx-auto max-w-7xl px-4 py-4 flex items-center gap-3">
            @if (!empty($tenant->logo_path))
                <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-8 w-8 rounded object-cover">
            @endif
            <span class="font-bold text-lg uppercase tracking-widest">[ {{ $tenant->name }} ]</span>
            <span class="ml-1 text-xs opacity-60 font-normal">— Retro</span>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
