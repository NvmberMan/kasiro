<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kasir Saya') }}
        </h2>
    </x-slot>

    <div >
        <div class="mx-auto">

            @if (session('status') === 'tenant-archived')
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    {{ __('Toko berhasil diarsipkan.') }} <a href="{{ route('archive') }}" class="underline">{{ __('Lihat Arsip') }}</a>
                </div>
            @endif

            @if ($tenants->isEmpty())
                <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                    <p class="text-gray-400 mb-4">{{ __('Anda belum memiliki atau bergabung di toko aktif.') }}</p>
                    <a href="{{ route('tenants.choose') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        + {{ __('Buat Toko Baru') }}
                    </a>
                </div>
            @else
                <div x-data="listController({ defaultSort: 'name:asc' })" x-init="init()">

                    {{-- Controls --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <input type="search" x-model="search" @input="apply()" placeholder="{{ __('Cari nama toko...') }}"
                               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <select x-model="filter" @change="apply()"
                                class="w-[130px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">{{ __('Semua Peran') }}</option>
                            <option value="owner">{{ __('Pemilik') }}</option>
                            <option value="manager">{{ __('Manajer') }}</option>
                            <option value="cashier">{{ __('Kasir') }}</option>
                        </select>

                        <select x-model="sort" @change="apply()"
                                class="w-[110px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="name:asc">{{ __('Nama A-Z') }}</option>
                            <option value="name:desc">{{ __('Nama Z-A') }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" x-ref="list">
                        @foreach ($tenants as $tenant)
                            @php $role = auth()->user()->roleFor($tenant); @endphp
                            <div data-clarity-mask="true" data-name="{{ mb_strtolower($tenant->name) }}" data-filter="{{ $role?->value }}">
                                <x-tenant-card :tenant="$tenant" :role="$role">
                                    @if ($role?->canManageTenantSettings())
                                        <form method="POST" action="{{ route('tenants.archive', $tenant) }}"
                                              data-confirm="{{ __('Toko akan diarsipkan. Datanya tidak akan dihapus dan bisa dipulihkan nanti.') }}"
                                              data-confirm-title="{{ __('Arsipkan Toko?') }}"
                                              data-confirm-action="{{ __('Ya, Arsipkan') }}"
                                              data-confirm-type="primary"
                                              class="mt-1">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-500 transition hover:border-red-300 hover:bg-red-100 hover:text-red-600">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                                {{ __('Arsipkan') }}
                                            </button>
                                        </form>
                                    @endif
                                </x-tenant-card>
                            </div>
                        @endforeach
                    </div>

                    <p x-show="visibleCount === 0" style="display:none" class="text-center py-10 text-gray-400">{{ __('Tidak ada toko yang cocok.') }}</p>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('tenants.choose') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        + {{ __('Buat Toko Baru') }}
                    </a>
                </div>

                @include('partials.list-controller')
            @endif

        </div>
    </div>
</x-app-layout>
