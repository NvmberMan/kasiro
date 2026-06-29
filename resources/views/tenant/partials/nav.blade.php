@php
    $navUser = auth()->user();
    $navRole = $navUser?->roleFor($tenant);
    $sub     = $tenant->subdomain;
    $base    = 'flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-full whitespace-nowrap transition brand-nav-item';
    $on      = 'brand-nav-active';
@endphp
<nav class="brand-primary border-t border-white/15">
    <div class="mx-auto max-w-7xl px-4 flex items-center gap-1 overflow-x-auto py-2">
        <a href="{{ route('tenant.pos', ['subdomain' => $sub]) }}"
           class="{{ $base }} {{ request()->routeIs('tenant.pos') ? $on : '' }}">
            Kasir
        </a>
        <a href="{{ route('tenant.products.index', ['subdomain' => $sub]) }}"
           class="{{ $base }} {{ request()->routeIs('tenant.products.*') ? $on : '' }}">
            Produk
        </a>
        <a href="{{ route('tenant.categories.index', ['subdomain' => $sub]) }}"
           class="{{ $base }} {{ request()->routeIs('tenant.categories.*') ? $on : '' }}">
            Kategori
        </a>
        @if ($navRole?->canViewReports())
            <a href="{{ route('tenant.reports', ['subdomain' => $sub]) }}"
               class="{{ $base }} {{ request()->routeIs('tenant.reports') ? $on : '' }}">
                Laporan
            </a>
            <a href="{{ route('tenant.transactions', ['subdomain' => $sub]) }}"
               class="{{ $base }} {{ request()->routeIs('tenant.transactions') ? $on : '' }}">
                Transaksi
            </a>
        @endif
        @if ($navRole?->canManageStaff())
            <a href="{{ route('tenant.employees.index', ['subdomain' => $sub]) }}"
               class="{{ $base }} {{ request()->routeIs('tenant.employees.*') ? $on : '' }}">
                Karyawan
            </a>
            <a href="{{ route('tenant.settings.edit', ['subdomain' => $sub]) }}"
               class="{{ $base }} {{ request()->routeIs('tenant.settings.*') ? $on : '' }}">
                Pengaturan
            </a>
        @endif

        <a href="{{ route('dashboard') }}" class="ml-auto {{ $base }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5M5 10v9a1 1 0 001 1h12a1 1 0 001-1v-9"/></svg>
            Studio
        </a>
    </div>
</nav>
