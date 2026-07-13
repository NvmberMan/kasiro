<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-brand-theme="{{ $tenant?->theme() ?? 'modern' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('tenant.partials.app-height')
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @if (!empty($tenant->logo_path))
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$tenant->logo_path) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('images/kasiro-logo.ico') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.clarity')
    <x-brand-styles :config="$tenant->theme_config ?? []" />
    @stack('head')
</head>
<body class="antialiased flex flex-col" style="height:100vh;height:100dvh;height:var(--app-h,100dvh);">
    @php $navUser = auth()->user(); $navRole = $navUser?->roleFor($tenant); @endphp

    <header class="brand-primary">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a href="{{ route('tenant.home', ['subdomain' => $tenant->subdomain]) }}"
               class="flex items-center gap-3 transition hover:opacity-80" aria-label="{{ __('Beranda') }} {{ $tenant->name }}">
                @if (!empty($tenant->logo_path))
                    <img data-clarity-mask="true" src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}"
                         class="h-9 w-9 rounded-lg object-cover brand-rounded">
                @else
                    <span class="flex h-9 w-9 items-center justify-center bg-white/20 brand-rounded text-sm font-bold text-white">
                        {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
                    </span>
                @endif
                <span class="text-lg font-bold tracking-tight text-white">{{ $tenant->name }}</span>
            </a>

            <div class="ml-auto flex items-center gap-2 text-sm">
                <span class="hidden sm:block text-white/70">{{ $navUser?->name }}</span>
                <span class="rounded-full bg-white/20 text-white px-2.5 py-0.5 text-xs font-medium capitalize">{{ $navRole?->value ?? '—' }}</span>
            </div>
        </div>
    </header>

    @include('tenant.partials.nav', ['tenant' => $tenant])

    <main class="flex-1 overflow-y-auto w-full">
        <div class="max-w-7xl mx-auto lg:h-full">
            {{ $slot }}
        </div>
    </main>


    <x-flash-modal />
    @include('partials.confirm-modal')
    @stack('scripts')
</body>
</html>
