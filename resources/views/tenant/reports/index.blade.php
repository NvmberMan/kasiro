<x-tenant-page>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Laporan Penjualan</h1>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $todayCount }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Pendapatan Hari Ini</p>
                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($todayTotal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Transaksi Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $monthCount }}</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-4">
                <p class="text-xs text-gray-500 mb-1">Pendapatan Bulan Ini</p>
                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($monthTotal, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Daily sales last 30 days --}}
            <div class="bg-white rounded-xl border shadow-sm">
                <div class="px-5 py-4 border-b">
                    <h2 class="font-semibold text-gray-700">Penjualan 30 Hari Terakhir</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="px-5 py-2 font-medium">Tanggal</th>
                                <th class="px-5 py-2 font-medium text-right">Transaksi</th>
                                <th class="px-5 py-2 font-medium text-right">Pendapatan</th>
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
                                <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top 10 products --}}
            <div class="bg-white rounded-xl border shadow-sm">
                <div class="px-5 py-4 border-b">
                    <h2 class="font-semibold text-gray-700">Produk Terlaris</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="px-5 py-2 font-medium">#</th>
                                <th class="px-5 py-2 font-medium">Produk</th>
                                <th class="px-5 py-2 font-medium text-right">Terjual</th>
                                <th class="px-5 py-2 font-medium text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($topProducts as $i => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-2.5">{{ $item->product?->name ?? '(dihapus)' }}</td>
                                    <td class="px-5 py-2.5 text-right">{{ $item->total_qty }}</td>
                                    <td class="px-5 py-2.5 text-right">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-page>
