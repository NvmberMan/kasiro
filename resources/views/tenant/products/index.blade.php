<x-tenant-page>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Produk</h1>
        @can('create', \App\Models\Product::class)
        <a href="{{ route('tenant.products.create', ['subdomain' => $tenant->subdomain]) }}"
           class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            + Tambah Produk
        </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            @if (session('status') === 'product-created') Produk berhasil ditambahkan.
            @elseif (session('status') === 'product-updated') Produk berhasil diperbarui.
            @elseif (session('status') === 'product-deleted') Produk berhasil dihapus.
            @endif
        </div>
    @endif

    @if ($products->isEmpty())
        <div class="text-center py-12 text-gray-400">Belum ada produk.</div>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3"></th>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Kategori</th>
                        <th class="px-5 py-3 text-right">Harga</th>
                        <th class="px-5 py-3 text-right">Stok</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($products as $product)
                    <tr class="{{ $product->is_active ? '' : 'opacity-50' }}">
                        <td class="px-3 py-2 w-12">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt=""
                                     class="h-10 w-10 rounded-lg object-cover border">
                            @else
                                <div class="h-10 w-10 rounded-lg bg-gray-100 border flex items-center justify-center text-gray-300 text-xs">—</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right {{ $product->stock === 0 ? 'text-red-500 font-semibold' : '' }}">
                            {{ $product->stock }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('update', $product)
                            <a href="{{ route('tenant.products.edit', ['subdomain' => $tenant->subdomain, 'product' => $product]) }}"
                               class="text-indigo-600 hover:underline mr-3">Edit</a>
                            <form method="POST" class="inline"
                                  action="{{ route('tenant.products.destroy', ['subdomain' => $tenant->subdomain, 'product' => $product]) }}"
                                  data-confirm="Produk ini akan dihapus permanen dan tidak bisa dikembalikan."
                                  data-confirm-title="Hapus Produk?"
                                  data-confirm-action="Ya, Hapus"
                                  data-confirm-type="danger">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-tenant-page>
