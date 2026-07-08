<x-tenant-page>
    <div class="p-6">
        <div class="max-w-lg">
            <h1 class="text-xl font-semibold mb-6">
                {{ isset($product) ? __('Edit Produk') : __('Tambah Produk') }}
            </h1>

            <form method="POST" data-loading
                action="{{ isset($product)
                    ? route('tenant.products.update', ['subdomain' => $tenant->subdomain, 'product' => $product])
                    : route('tenant.products.store', ['subdomain' => $tenant->subdomain]) }}"
                enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                @csrf
                @if (isset($product))
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Produk') }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required maxlength="200">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Kategori') }}</label>
                    <select name="category_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('— Tanpa Kategori —') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        maxlength="50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Foto Produk') }}</label>
                    @if (!empty($product->image_path))
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                            class="h-24 w-24 object-cover rounded-lg border mb-2">
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-400">{{ __('Maks. 2MB. Kosongkan jika tidak ingin mengganti.') }}</p>
                    @error('image')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Harga (Rp)') }} <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $product->price ?? 0) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            min="0" step="100" required>
                        @error('price')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Stok') }} <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            min="0" required>
                        @error('stock')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('Produk Aktif') }}</label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">{{ __('Simpan') }}</button>
                    <a href="{{ route('tenant.products.index', ['subdomain' => $tenant->subdomain]) }}"
                        class="px-5 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50 transition">{{ __('Batal') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-tenant-page>
