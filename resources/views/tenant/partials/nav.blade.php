@php
    $navUser = auth()->user();
    $navRole = $navUser?->roleFor($tenant);
    $sub     = $tenant->subdomain;
@endphp
<nav style="background-color:var(--brand-primary);opacity:.9;" class="border-t border-white/20">
    <div class="mx-auto max-w-7xl px-4 flex gap-1 overflow-x-auto">
        <a href="{{ route('tenant.pos', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.pos') ? 'text-white border-b-2 border-white' : '' }}">
            Kasir
        </a>
        @if ($navRole?->canManageProducts())
        <a href="{{ route('tenant.products.index', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.products.*') ? 'text-white border-b-2 border-white' : '' }}">
            Produk
        </a>
        <a href="{{ route('tenant.categories.index', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.categories.*') ? 'text-white border-b-2 border-white' : '' }}">
            Kategori
        </a>
        @endif
        @if ($navRole?->canViewReports())
        <a href="{{ route('tenant.reports', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.reports') ? 'text-white border-b-2 border-white' : '' }}">
            Laporan
        </a>
        <a href="{{ route('tenant.transactions', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.transactions') ? 'text-white border-b-2 border-white' : '' }}">
            Transaksi
        </a>
        @endif
        @if ($navRole?->canManageStaff())
        <a href="{{ route('tenant.employees.index', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.employees.*') ? 'text-white border-b-2 border-white' : '' }}">
            Karyawan
        </a>
        <a href="{{ route('tenant.settings.edit', ['subdomain' => $sub]) }}"
           class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap
                  {{ request()->routeIs('tenant.settings.*') ? 'text-white border-b-2 border-white' : '' }}">
            Pengaturan
        </a>
        @endif
        <div class="ml-auto flex items-center gap-2 py-1 text-xs text-white/70">
            {{ $navUser?->name }} ({{ $navRole?->value ?? '-' }})
        </div>
    </div>
</nav>
