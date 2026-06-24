<x-app-layout>
    <div class="flex min-h-[60vh] flex-col items-center justify-center py-16">
        <h1 class="mb-2 text-3xl font-bold tracking-tight text-slate-900">Buat Sistem Kasir</h1>
        <p class="mb-10 text-slate-500">Pilih cara membuat aplikasi kasir kamu:</p>

        <div class="grid w-full max-w-2xl grid-cols-1 gap-5 px-4 sm:grid-cols-2">
            {{-- Custom --}}
            <a href="{{ route('tenants.create.custom') }}"
               class="group flex flex-col items-center rounded-2xl border-2 border-slate-200 bg-white px-8 py-10 text-center shadow-sm transition hover:border-blue-500 hover:shadow-md">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-3xl transition group-hover:bg-blue-100">
                    🎨
                </div>
                <h3 class="text-lg font-bold text-slate-900">Custom</h3>
                <p class="mt-2 text-sm text-slate-500">Pilih layout, tema, dan warna sendiri dari awal.</p>
            </a>

            {{-- Template --}}
            <a href="{{ route('tenants.create.template') }}"
               class="group flex flex-col items-center rounded-2xl border-2 border-slate-200 bg-white px-8 py-10 text-center shadow-sm transition hover:border-blue-500 hover:shadow-md">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-3xl transition group-hover:bg-indigo-100">
                    📋
                </div>
                <h3 class="text-lg font-bold text-slate-900">Pakai Template</h3>
                <p class="mt-2 text-sm text-slate-500">Pilih preset siap pakai, lalu sesuaikan nama & subdomain.</p>
            </a>
        </div>
    </div>
</x-app-layout>
