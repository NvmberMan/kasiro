<x-tenant-page>
    <div class="p-4 sm:p-6 h-full">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">{{ __('Produk') }}</h1>
            @can('create', \App\Models\Product::class)
                <a href="{{ route('tenant.products.create', ['subdomain' => $tenant->subdomain]) }}"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    + {{ __('Tambah Produk') }}
                </a>
            @endcan
        </div>

        @if ($products->isEmpty())
            <div class="text-center py-12 text-gray-400">{{ __('Belum ada produk.') }}</div>
        @else
            <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

                {{-- Controls --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    <input type="search" x-model="search" @input="apply()" placeholder="{{ __('Cari nama produk atau SKU...') }}"
                        class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                    <select x-model="filter" @change="apply()"
                        class="border min-w-[160px] border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('Semua Kategori') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select x-model="sort" @change="apply()"
                        class="min-w-[150px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="name:asc">{{ __('Nama A-Z') }}</option>
                        <option value="name:desc">{{ __('Nama Z-A') }}</option>
                        <option value="price:asc">{{ __('Harga Terendah') }}</option>
                        <option value="price:desc">{{ __('Harga Tertinggi') }}</option>
                        <option value="stock:desc">{{ __('Stok Terbanyak') }}</option>
                        <option value="stock:asc">{{ __('Stok Tersedikit') }}</option>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[680px]">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <tr>
                                <th class="px-5 py-3"></th>
                                <th class="px-5 py-3 text-left">{{ __('Nama') }}</th>
                                <th class="px-5 py-3 text-left">{{ __('Kategori') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Harga') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Stok') }}</th>
                                <th class="px-5 py-3 text-center">{{ __('Status') }}</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" x-ref="list">
                            @foreach ($products as $product)
                                <tr class="{{ $product->is_active ? '' : 'opacity-50' }}"
                                    data-name="{{ mb_strtolower($product->name) }}"
                                    data-search="{{ mb_strtolower($product->sku ?? '') }}"
                                    data-filter="{{ $product->category_id }}" data-price="{{ $product->price }}"
                                    data-stock="{{ $product->stock }}">
                                    <td class="px-3 py-2 w-12">
                                        @if ($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt=""
                                                class="h-10 w-10 rounded-lg object-cover border">
                                        @else
                                            <div
                                                class="h-10 w-10 rounded-lg bg-gray-100 border flex items-center justify-center text-gray-300 text-xs">
                                                —</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                                    <td class="px-5 py-3 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">Rp
                                        {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td
                                        class="px-5 py-3 text-right {{ $product->stock === 0 ? 'text-red-500 font-semibold' : '' }}">
                                        {{ $product->stock }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $product->is_active ? __('Aktif') : __('Nonaktif') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        @can('update', $product)
                                            <a href="{{ route('tenant.products.edit', ['subdomain' => $tenant->subdomain, 'product' => $product]) }}"
                                                class="text-indigo-600 hover:underline mr-3">{{ __('Edit') }}</a>
                                            <form method="POST" class="inline"
                                                action="{{ route('tenant.products.destroy', ['subdomain' => $tenant->subdomain, 'product' => $product]) }}"
                                                data-confirm="{{ __('Produk ini akan dihapus permanen dan tidak bisa dikembalikan.') }}"
                                                data-confirm-title="{{ __('Hapus Produk?') }}" data-confirm-action="{{ __('Ya, Hapus') }}"
                                                data-confirm-type="danger">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline">{{ __('Hapus') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">{{ __('Tidak ada produk yang cocok dengan pencarian.') }}</p>
            </div>

            @include('partials.list-controller')
        @endif
    </div>
</x-tenant-page>
