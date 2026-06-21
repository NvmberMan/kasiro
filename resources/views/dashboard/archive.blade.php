<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arsip') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status') === 'tenant-restored')
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    Toko berhasil dipulihkan. <a href="{{ route('my-stores') }}" class="underline">Lihat Kasir Saya</a>
                </div>
            @endif

            @if ($tenants->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                    <p class="text-gray-400">Tidak ada toko yang diarsipkan.</p>
                </div>
            @else
                <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

                    {{-- Controls --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <input type="search" x-model="search" @input="apply()" placeholder="Cari nama toko..."
                               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <select x-model="sort" @change="apply()"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="name:asc">Nama A-Z</option>
                            <option value="name:desc">Nama Z-A</option>
                        </select>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100" x-ref="list">
                        @foreach ($tenants as $tenant)
                            <div class="flex items-center justify-between px-6 py-4 gap-4"
                                 data-name="{{ mb_strtolower($tenant->name) }}">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $tenant->subdomain }}.{{ config('tenancy.central_domain') }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Diarsipkan {{ $tenant->archived_at?->diffForHumans() }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('tenants.restore', $tenant) }}"
                                      onsubmit="return confirm('Pulihkan toko ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        Pulihkan
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">
                        Tidak ada toko arsip yang cocok.
                    </p>
                </div>

                @include('partials.list-controller')
            @endif

        </div>
    </div>
</x-app-layout>
