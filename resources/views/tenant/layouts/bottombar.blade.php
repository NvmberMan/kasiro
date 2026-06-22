<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-styles :config="$tenant->theme_config ?? []" />
</head>
<body class="antialiased h-screen flex flex-col overflow-hidden">
    @php
        $navUser = auth()->user();
        $navRole = $navUser?->roleFor($tenant);
        $sub     = $tenant->subdomain;
        $item    = 'flex flex-col items-center justify-center gap-0.5 flex-1 min-w-[64px] px-2 py-2 brand-muted transition';
        $active  = '!text-[color:var(--brand-primary)]';
    @endphp

    {{-- Header --}}
    <header class="brand-surface border-b brand-border flex-shrink-0">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a href="{{ route('tenant.home', ['subdomain' => $sub]) }}"
               class="flex items-center gap-3 transition hover:opacity-80" aria-label="Beranda {{ $tenant->name }}">
                @if (!empty($tenant->logo_path))
                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-9 w-9 rounded-lg object-cover brand-rounded">
                @else
                    <div class="h-9 w-9 brand-rounded flex items-center justify-center text-white font-bold text-sm brand-primary">
                        {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
                    </div>
                @endif
                <span class="font-bold text-lg tracking-tight brand-text">{{ $tenant->name }}</span>
            </a>
            <div class="ml-auto flex items-center gap-2 text-sm">
                <span class="hidden sm:block brand-muted">{{ $navUser?->name }}</span>
                <span class="rounded-full brand-soft brand-text px-2.5 py-0.5 text-xs font-medium capitalize">{{ $navRole?->value ?? '—' }}</span>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <main class="flex-1 max-w-7xl mx-auto overflow-y-auto w-full">
        {{ $slot }}
    </main>

    {{-- Bottom navigation --}}
    <nav class="brand-surface border-t brand-border flex-shrink-0">
        <div class="mx-auto max-w-3xl flex items-stretch justify-around overflow-x-auto">
            <a href="{{ route('tenant.pos', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.pos') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="text-[10px] font-medium">Kasir</span>
            </a>
            <a href="{{ route('tenant.products.index', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.products.*') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-[10px] font-medium">Produk</span>
            </a>
            <a href="{{ route('tenant.categories.index', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.categories.*') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                <span class="text-[10px] font-medium">Kategori</span>
            </a>
            @if ($navRole?->canViewReports())
            <a href="{{ route('tenant.reports', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.reports') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="text-[10px] font-medium">Laporan</span>
            </a>
            <a href="{{ route('tenant.transactions', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.transactions') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-[10px] font-medium">Transaksi</span>
            </a>
            @endif
            @if ($navRole?->canManageStaff())
            <a href="{{ route('tenant.employees.index', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.employees.*') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] font-medium">Karyawan</span>
            </a>
            <a href="{{ route('tenant.settings.edit', ['subdomain' => $sub]) }}" class="{{ $item }} {{ request()->routeIs('tenant.settings.*') ? $active : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] font-medium">Pengaturan</span>
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="{{ $item }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5M5 10v9a1 1 0 001 1h12a1 1 0 001-1v-9"/></svg>
                <span class="text-[10px] font-medium">Studio</span>
            </a>
        </div>
    </nav>
    @include('partials.confirm-modal')
</body>
</html>
