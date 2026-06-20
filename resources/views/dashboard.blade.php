<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Greeting & stats --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Halo, {{ auth()->user()->name }}!
                </h3>
                <p class="mt-1 text-sm text-gray-500">Kelola semua toko kasir Anda dari sini.</p>

                <div class="mt-4 flex gap-6">
                    <a href="{{ route('my-stores') }}" class="text-center">
                        <span class="block text-3xl font-bold text-indigo-600">{{ $activeCount }}</span>
                        <span class="text-xs text-gray-500">Toko Aktif</span>
                    </a>
                    <div class="border-l border-gray-200"></div>
                    <a href="{{ route('archive') }}" class="text-center">
                        <span class="block text-3xl font-bold text-gray-400">{{ $archivedCount }}</span>
                        <span class="text-xs text-gray-500">Diarsipkan</span>
                    </a>
                </div>
            </div>

            {{-- Recent stores --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-700">Toko Terbaru</h4>
                    <a href="{{ route('my-stores') }}" class="text-sm text-indigo-600 hover:underline">Lihat semua →</a>
                </div>

                @if ($recent->isEmpty())
                    <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-10 text-center">
                        <p class="text-gray-400 mb-4">Belum ada toko. Mulai buat toko pertama Anda!</p>
                        <a href="{{ route('tenants.choose') }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            + Buat Toko Baru
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ($recent as $tenant)
                            <x-tenant-card :tenant="$tenant" :role="auth()->user()->roleFor($tenant)" />
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('tenants.choose') }}"
                           class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            + Buat Toko Baru
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
