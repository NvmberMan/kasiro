<x-tenant-page>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">{{ __('Laporan Penjualan') }}</h1>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">{{ __('Transaksi Hari Ini') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $todayCount }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">{{ __('Pendapatan Hari Ini') }}</p>
                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($todayTotal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">{{ __('Transaksi Bulan Ini') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $monthCount }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">{{ __('Pendapatan Bulan Ini') }}</p>
                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($monthTotal, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Revenue trend chart --}}
        @php
            $trendLabels = collect($dailySeries)->map(fn ($r) => \Carbon\Carbon::parse($r['date'])->isoFormat('D MMM'))->all();
            $trendValues = collect($dailySeries)->pluck('revenue')->all();
            $seriesTotal = collect($dailySeries)->sum('revenue');
            $peak = collect($dailySeries)->max('revenue');
        @endphp
        <div class="brand-card shadow-sm p-5 mb-6">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h2 class="font-semibold" style="color:var(--brand-fg)">{{ __('Tren Pendapatan') }}</h2>
                    <p class="text-xs brand-muted">{{ __('30 hari terakhir') }} &middot; {{ __('total') }} Rp {{ number_format($seriesTotal, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs brand-muted">{{ __('Tertinggi/hari') }}</p>
                    <p class="text-sm font-semibold" style="color:var(--brand-fg)">Rp {{ number_format($peak, 0, ',', '.') }}</p>
                </div>
            </div>
            <x-chart type="line" :labels="$trendLabels" :values="$trendValues" :height="180"
                     format="currency" empty="{{ __('Belum ada transaksi dalam 30 hari.') }}" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Daily sales last 30 days --}}
            <div class="bg-white rounded-xl border shadow-sm">
                <div class="px-5 py-4 border-b">
                    <h2 class="font-semibold text-gray-700">{{ __('Penjualan 30 Hari Terakhir') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="px-5 py-2 font-medium">{{ __('Tanggal') }}</th>
                                <th class="px-5 py-2 font-medium text-right">{{ __('Transaksi') }}</th>
                                <th class="px-5 py-2 font-medium text-right">{{ __('Pendapatan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($dailySales as $day)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-2.5">{{ \Carbon\Carbon::parse($day->date)->isoFormat('D MMM YYYY') }}</td>
                                    <td class="px-5 py-2.5 text-right">{{ $day->tx_count }}</td>
                                    <td class="px-5 py-2.5 text-right">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">{{ __('Belum ada transaksi.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top 10 products (bar chart) --}}
            <div class="bg-white rounded-xl border shadow-sm">
                <div class="px-5 py-4 border-b">
                    <h2 class="font-semibold text-gray-700">{{ __('Produk Terlaris') }}</h2>
                </div>
                <div class="p-5" data-clarity-mask="true">
                    @php
                        $prodLabels = $topProducts->map(fn ($item) => $item->product?->name ?? __('(dihapus)'))->all();
                        $prodValues = $topProducts->pluck('total_qty')->all();
                        $prodHeight = max(180, $topProducts->count() * 34);
                    @endphp
                    <x-chart type="bar" horizontal :labels="$prodLabels" :values="$prodValues"
                             :height="$prodHeight" format="number" empty="{{ __('Belum ada data.') }}" />
                </div>
            </div>
        </div>
    </div>
</x-tenant-page>
