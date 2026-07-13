<x-tenant-page>
    @php $sub = $tenant->subdomain; @endphp
    <div class="p-4 sm:p-6 max-w-7xl mx-auto">

    {{-- Welcome --}}
    <div class="mb-6">
        <h1 data-clarity-mask="true" class="text-2xl font-bold">{{ __('Selamat datang di :name', ['name' => $tenant->name]) }}</h1>
        <p data-clarity-mask="true" class="text-sm opacity-70 mt-1">
            {{ __('Halo, :name', ['name' => $user?->name ?? __('Pengguna')]) }}@if ($role) &middot; <span class="capitalize">{{ $role->value }}</span>@endif
        </p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @if ($canViewReports && $stats)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500">{{ __('Penjualan Hari Ini') }}</p>
                <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($stats['todaySales'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500">{{ __('Transaksi Hari Ini') }}</p>
                <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($stats['todayCount'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-medium text-gray-500">{{ __('Penjualan Bulan Ini') }}</p>
                <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($stats['monthSales'], 0, ',', '.') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500">{{ __('Produk Aktif') }}</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($activeProducts, 0, ',', '.') }}</p>
            @if ($lowStock > 0)
                <p class="text-xs text-amber-600 mt-1">{{ __(':count produk stok menipis', ['count' => $lowStock]) }}</p>
            @endif
        </div>
    </div>

    {{-- Sales sparkline (last 7 days) --}}
    @if ($canViewReports)
        @php
            $weekLabels = collect($weekSeries)->map(fn ($r) => \Carbon\Carbon::parse($r['date'])->isoFormat('ddd'))->all();
            $weekValues = collect($weekSeries)->pluck('revenue')->all();
            $weekTotal = collect($weekSeries)->sum('revenue');
        @endphp
        <div class="brand-card shadow-sm p-5 mb-8">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--brand-fg)">{{ __('Penjualan 7 Hari Terakhir') }}</h2>
                    <p class="text-xs brand-muted">Total Rp {{ number_format($weekTotal, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('tenant.reports', ['subdomain' => $sub]) }}" class="text-xs font-medium brand-text hover:underline">{{ __('Lihat laporan') }}</a>
            </div>
            <x-chart type="line" :labels="$weekLabels" :values="$weekValues" :height="130"
                     format="currency" empty="{{ __('Belum ada penjualan minggu ini.') }}" />
        </div>
    @endif

    {{-- Quick actions --}}
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Akses Cepat') }}</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('tenant.pos', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Buka Kasir') }}</span>
            <span class="text-xs text-gray-500">{{ __('Mulai transaksi penjualan') }}</span>
        </a>

        @if ($role?->canManageProducts())
        <a href="{{ route('tenant.products.index', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Kelola Produk') }}</span>
            <span class="text-xs text-gray-500">{{ __('Tambah & ubah produk') }}</span>
        </a>
        <a href="{{ route('tenant.categories.index', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Kategori') }}</span>
            <span class="text-xs text-gray-500">{{ __('Atur kategori produk') }}</span>
        </a>
        @endif

        @if ($role?->canViewReports())
        <a href="{{ route('tenant.reports', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Laporan') }}</span>
            <span class="text-xs text-gray-500">{{ __('Ringkasan penjualan') }}</span>
        </a>
        <a href="{{ route('tenant.transactions', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Transaksi') }}</span>
            <span class="text-xs text-gray-500">{{ __('Riwayat transaksi') }}</span>
        </a>
        @endif

        @if ($role?->canManageStaff())
        <a href="{{ route('tenant.settings.edit', ['subdomain' => $sub]) }}"
           class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-indigo-300 hover:shadow transition flex flex-col gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg brand-primary text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <span class="font-semibold text-gray-800">{{ __('Pengaturan') }}</span>
            <span class="text-xs text-gray-500">{{ __('Karyawan & toko') }}</span>
        </a>
        @endif
    </div>

    {{-- Recent transactions --}}
    @if ($canViewReports)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">{{ __('Transaksi Terbaru') }}</h2>
                <a href="{{ route('tenant.transactions', ['subdomain' => $sub]) }}"
                   class="text-xs font-medium brand-text hover:underline">{{ __('Lihat semua') }}</a>
            </div>
            @if ($recent->isEmpty())
                <p class="px-5 py-6 text-sm text-gray-500 text-center">{{ __('Belum ada transaksi.') }}</p>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[480px]">
                    <thead class="text-left text-xs text-gray-500 bg-gray-50">
                        <tr>
                            <th class="px-5 py-2 font-medium">{{ __('Tanggal') }}</th>
                            <th class="px-5 py-2 font-medium">{{ __('Waktu') }}</th>
                            <th class="px-5 py-2 font-medium">{{ __('Kasir') }}</th>
                            <th class="px-5 py-2 font-medium text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recent as $tx)
                            <tr>
                                <td class="px-5 py-2.5 text-gray-600">{{ $tx->transacted_at->format('d M Y') }}</td>
                                <td class="px-5 py-2.5 text-gray-600">{{ $tx->transacted_at->format('H:i') }}</td>
                                <td class="px-5 py-2.5 text-gray-600">{{ $tx->cashier?->name ?? '-' }}</td>
                                <td class="px-5 py-2.5 text-right font-medium">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>
    @endif
    </div>
</x-tenant-page>
