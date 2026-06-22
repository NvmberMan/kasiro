<x-tenant-page>
    <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3 mb-6">
            <h1 class="text-xl font-semibold">Riwayat Transaksi</h1>
            <a href="{{ route('tenant.pos', ['subdomain' => $tenant->subdomain]) }}"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Buka Kasir
            </a>
        </div>

        @if ($transactions->isEmpty())
            <div class="text-center py-12 text-gray-400">Belum ada transaksi.</div>
        @else
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left">Waktu</th>
                            <th class="px-5 py-3 text-left">Kasir</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Bayar</th>
                            <th class="px-5 py-3 text-right">Kembali</th>
                            <th class="px-5 py-3 text-center">Item</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($transactions as $tx)
                            <tr>
                                <td class="px-5 py-3 text-gray-600">{{ $tx->transacted_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-3">{{ $tx->cashier->name }}</td>
                                <td class="px-5 py-3 text-right font-medium">Rp
                                    {{ number_format($tx->total, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right">Rp {{ number_format($tx->paid, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right">Rp {{ number_format($tx->change, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center text-gray-500">{{ $tx->items->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</x-tenant-page>
