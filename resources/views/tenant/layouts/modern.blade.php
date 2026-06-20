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
        .brand-primary { background-color: var(--brand-primary); }
        .brand-accent { background-color: var(--brand-accent); }
        .brand-text { color: var(--brand-primary); }
    </style>
</head>
<body class="antialiased">
    <header class="brand-primary text-white shadow">
        <div class="mx-auto max-w-7xl px-4 py-4 flex items-center gap-3">
            <a href="{{ route('tenant.home', ['subdomain' => $tenant->subdomain]) }}"
               class="flex items-center gap-3 hover:opacity-90 transition" aria-label="Beranda {{ $tenant->name }}">
                @if (!empty($tenant->logo_path))
                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-8 w-8 rounded object-cover">
                @endif
                <span class="font-bold text-lg tracking-wide">{{ $tenant->name }}</span>
            </a>
        </div>
    </header>
    @include('tenant.partials.nav', ['tenant' => $tenant])

    <main class="mx-auto max-w-7xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
