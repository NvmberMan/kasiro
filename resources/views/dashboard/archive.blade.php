<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arsip') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto">

            @if (session('status') === 'tenant-restored')
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ __('Toko berhasil dipulihkan.') }} <a href="{{ route('my-stores') }}" class="underline">{{ __('Lihat Kasir Saya') }}</a>
                </div>
            @endif


            @if ($tenants->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                    <p class="text-gray-400">{{ __('Tidak ada toko yang diarsipkan.') }}</p>
                </div>
            @else
                <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

                    {{-- Controls --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <input type="search" x-model="search" @input="apply()" placeholder="{{ __('Cari nama toko...') }}"
                               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <select x-model="sort" @change="apply()"
                                class="w-[110px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="name:asc">{{ __('Nama A-Z') }}</option>
                            <option value="name:desc">{{ __('Nama Z-A') }}</option>
                        </select>
                    </div>

                    <div class="space-y-3" x-ref="list">
                        @foreach ($tenants as $tenant)
                            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4 gap-4"
                                 data-clarity-mask="true" data-name="{{ mb_strtolower($tenant->name) }}">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 text-lg">{{ $tenant->name }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ $tenant->subdomain }}.{{ config('tenancy.central_domain') }}
                                    </p>
                                    <p class="text-sm text-gray-400 mt-0.5">
                                        {{ __('Diarsipkan') }} {{ $tenant->archived_at?->format('d/m/Y') }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <form method="POST" action="{{ route('tenants.restore', $tenant) }}"
                                          data-confirm="{{ __('Toko ini akan dipulihkan dan kembali aktif.') }}"
                                          data-confirm-title="{{ __('Pulihkan Toko?') }}"
                                          data-confirm-action="{{ __('Ya, Pulihkan') }}"
                                          data-confirm-type="primary">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-sm font-medium text-indigo-600 border border-indigo-400 rounded-full px-4 py-1.5 hover:bg-indigo-50 transition-colors">{{ __('Pulihkan') }}</button>
                                    </form>

                                    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                          data-confirm="{{ __('Toko dan semua datanya akan dihapus permanen dan tidak bisa dikembalikan.') }}"
                                          data-confirm-title="{{ __('Hapus Permanen?') }}"
                                          data-confirm-action="{{ __('Ya, Hapus') }}"
                                          data-confirm-type="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-sm font-medium text-red-600 border border-red-400 rounded-full px-4 py-1.5 hover:bg-red-50 transition-colors">{{ __('Hapus') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">{{ __('Tidak ada toko arsip yang cocok.') }}</p>
                </div>

                @include('partials.list-controller')
            @endif

        </div>
    </div>
</x-app-layout>
