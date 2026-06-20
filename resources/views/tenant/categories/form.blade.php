<x-tenant-page>
    <div class="max-w-md">
        <h1 class="text-xl font-semibold mb-6">
            {{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori' }}
        </h1>

        <form method="POST"
              action="{{ isset($category)
                  ? route('tenant.categories.update', ['subdomain' => $tenant->subdomain, 'category' => $category])
                  : route('tenant.categories.store', ['subdomain' => $tenant->subdomain]) }}"
              class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @csrf
            @if (isset($category)) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       required maxlength="100">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    Simpan
                </button>
                <a href="{{ route('tenant.categories.index', ['subdomain' => $tenant->subdomain]) }}"
                   class="px-5 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-tenant-page>
