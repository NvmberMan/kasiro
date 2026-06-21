<x-tenant-page>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Kategori</h1>
        @can('create', \App\Models\Category::class)
        <a href="{{ route('tenant.categories.create', ['subdomain' => $tenant->subdomain]) }}"
           class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            + Tambah Kategori
        </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            @if (session('status') === 'category-created') Kategori berhasil ditambahkan.
            @elseif (session('status') === 'category-updated') Kategori berhasil diperbarui.
            @elseif (session('status') === 'category-deleted') Kategori berhasil dihapus.
            @endif
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="text-center py-12 text-gray-400">Belum ada kategori.</div>
    @else
        <div class="bg-white rounded-xl shadow-sm divide-y">
            @foreach ($categories as $category)
                <div class="flex items-center justify-between px-5 py-3 gap-4">
                    <span class="font-medium text-gray-800">{{ $category->name }}</span>
                    <div class="flex gap-3">
                        @can('update', $category)
                        <a href="{{ route('tenant.categories.edit', ['subdomain' => $tenant->subdomain, 'category' => $category]) }}"
                           class="text-sm text-indigo-600 hover:underline">Edit</a>
                        <form method="POST"
                              action="{{ route('tenant.categories.destroy', ['subdomain' => $tenant->subdomain, 'category' => $category]) }}"
                              data-confirm="Kategori ini akan dihapus."
                              data-confirm-title="Hapus Kategori?"
                              data-confirm-action="Ya, Hapus"
                              data-confirm-type="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:underline">Hapus</button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-tenant-page>
