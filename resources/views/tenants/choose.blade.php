<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Aplikasi Kasir') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600 mb-8 text-center">Pilih cara membuat aplikasi kasir kamu:</p>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                {{-- Custom --}}
                <a href="{{ route('tenants.create.custom') }}"
                   class="block rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-indigo-500 hover:shadow-md transition">
                    <div class="text-4xl mb-3">🎨</div>
                    <h3 class="font-semibold text-gray-900 text-lg">Custom</h3>
                    <p class="mt-2 text-sm text-gray-500">Pilih layout, tema, dan warna sendiri dari awal.</p>
                </a>

                {{-- Template --}}
                <a href="{{ route('tenants.create.template') }}"
                   class="block rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-indigo-500 hover:shadow-md transition">
                    <div class="text-4xl mb-3">📋</div>
                    <h3 class="font-semibold text-gray-900 text-lg">Pakai Template</h3>
                    <p class="mt-2 text-sm text-gray-500">Pilih preset siap pakai, lalu sesuaikan nama & subdomain.</p>
                </a>

                {{-- Showcase --}}
                <a href="{{ route('tenants.showcase') }}"
                   class="block rounded-xl border-2 border-gray-200 bg-white p-6 text-center hover:border-indigo-500 hover:shadow-md transition">
                    <div class="text-4xl mb-3">🏪</div>
                    <h3 class="font-semibold text-gray-900 text-lg">Galeri Showcase</h3>
                    <p class="mt-2 text-sm text-gray-500">Lihat contoh toko nyata, klik untuk buat seketika.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
