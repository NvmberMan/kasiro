<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @if (!empty($tenant->logo_path))
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$tenant->logo_path) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('images/kasiro-logo.ico') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-styles :config="$tenant->theme_config ?? []" />
    @stack('head')
</head>
<body class="antialiased h-screen flex flex-col">
    @php $navUser = auth()->user(); $navRole = $navUser?->roleFor($tenant); @endphp

    <header class="brand-surface border-b brand-border">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a href="{{ route('tenant.home', ['subdomain' => $tenant->subdomain]) }}"
               class="flex items-center gap-3 transition hover:opacity-80" aria-label="Beranda {{ $tenant->name }}">
                @if (!empty($tenant->logo_path))
                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}"
                         class="h-9 w-9 rounded-lg object-cover brand-rounded">
                @else
                    <span class="flex h-9 w-9 items-center justify-center brand-primary brand-rounded text-sm font-bold text-white">
                        {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
                    </span>
                @endif
                <span class="text-lg font-bold tracking-tight brand-text">{{ $tenant->name }}</span>
            </a>

            <div class="ml-auto flex items-center gap-2 text-sm">
                <span class="hidden sm:block brand-muted">{{ $navUser?->name }}</span>
                <span class="rounded-full brand-soft brand-text px-2.5 py-0.5 text-xs font-medium capitalize">{{ $navRole?->value ?? '—' }}</span>
            </div>
        </div>
    </header>

    @include('tenant.partials.nav', ['tenant' => $tenant])

    <main class="flex-1 max-w-7xl mx-auto overflow-y-auto w-full">
        {{ $slot }}
    </main>


    <x-flash-modal />
    @include('partials.confirm-modal')
    @stack('scripts')
</body>
</html>
