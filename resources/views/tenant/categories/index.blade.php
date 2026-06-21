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
        <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

            {{-- Controls --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <input type="search" x-model="search" @input="apply()" placeholder="Cari kategori..."
                       class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                <select x-model="sort" @change="apply()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="name:asc">Nama A-Z</option>
                    <option value="name:desc">Nama Z-A</option>
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm divide-y" x-ref="list">
                @foreach ($categories as $category)
                    <div class="flex items-center justify-between px-5 py-3 gap-4"
                         data-name="{{ mb_strtolower($category->name) }}">
                        <span class="font-medium text-gray-800">{{ $category->name }}</span>
                        <div class="flex gap-3">
                            @can('update', $category)
                            <a href="{{ route('tenant.categories.edit', ['subdomain' => $tenant->subdomain, 'category' => $category]) }}"
                               class="text-sm text-indigo-600 hover:underline">Edit</a>
                            <form method="POST"
                                  action="{{ route('tenant.categories.destroy', ['subdomain' => $tenant->subdomain, 'category' => $category]) }}"
                                  onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

            <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">
                Tidak ada kategori yang cocok dengan pencarian.
            </p>
        </div>

        @include('partials.list-controller')
    @endif
</x-tenant-page>
