<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kasir Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
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
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($tenants as $tenant)
                        @php $role = auth()->user()->roleFor($tenant); @endphp
                        <x-tenant-card :tenant="$tenant" :role="$role">
                            @if ($role?->canManageTenantSettings())
                                <form method="POST" action="{{ route('tenants.archive', $tenant) }}"
                                      data-confirm="Toko akan diarsipkan. Data tidak dihapus dan bisa dipulihkan kapan saja."
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
                    @endforeach
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('tenants.choose') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        + Buat Toko Baru
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
