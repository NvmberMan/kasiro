<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kasir Saya') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status') === 'tenant-archived')
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    Toko berhasil diarsipkan. <a href="{{ route('archive') }}" class="underline">Lihat Arsip</a>
                </div>
            @endif

            @if ($tenants->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                    <p class="text-gray-400 mb-4">Anda belum memiliki atau bergabung di toko aktif.</p>
                    <a href="{{ route('tenants.choose') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        + Buat Toko Baru
                    </a>
                </div>
            @else
                <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

                    {{-- Controls --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <input type="search" x-model="search" @input="apply()" placeholder="Cari nama toko..."
                               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <select x-model="filter" @change="apply()"
                                class="w-[130px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Peran</option>
                            <option value="owner">Pemilik</option>
                            <option value="manager">Manajer</option>
                            <option value="cashier">Kasir</option>
                        </select>

                        <select x-model="sort" @change="apply()"
                                class="w-[110px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="name:asc">Nama A-Z</option>
                            <option value="name:desc">Nama Z-A</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" x-ref="list">
                        @foreach ($tenants as $tenant)
                            @php $role = auth()->user()->roleFor($tenant); @endphp
                            <div data-name="{{ mb_strtolower($tenant->name) }}" data-filter="{{ $role?->value }}">
                                <x-tenant-card :tenant="$tenant" :role="$role">
                                    @if ($role?->canManageTenantSettings())
                                        <form method="POST" action="{{ route('tenants.archive', $tenant) }}"
                                              data-confirm="Toko akan diarsipkan. Datanya tidak akan dihapus dan bisa dipulihkan nanti."
                                              data-confirm-title="Arsipkan Toko?"
                                              data-confirm-action="Ya, Arsipkan"
                                              data-confirm-type="primary"
                                              class="mt-2">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                                                Arsipkan
                                            </button>
                                        </form>
                                    @endif
                                </x-tenant-card>
                            </div>
                        @endforeach
                    </div>

                    <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">
                        Tidak ada toko yang cocok.
                    </p>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('tenants.choose') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        + Buat Toko Baru
                    </a>
                </div>

                @include('partials.list-controller')
            @endif

        </div>
    </div>
</x-app-layout>
