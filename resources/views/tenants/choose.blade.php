<x-app-layout>
    <div class="flex min-h-[60vh] flex-col items-center justify-center py-16">
        <h1 class="mb-2 text-3xl font-bold tracking-tight text-slate-900">Buat Sistem Kasir</h1>
        <p class="mb-10 text-slate-500">Pilih cara membuat aplikasi kasir kamu:</p>

        <div class="grid w-full max-w-2xl grid-cols-1 gap-5 px-4 sm:grid-cols-2">
            {{-- Custom --}}
            <a href="{{ route('tenants.create.custom') }}"
               class="group flex flex-col items-center rounded-2xl border-2 border-slate-200 bg-white px-8 py-10 text-center shadow-sm transition hover:border-blue-500 hover:shadow-md">
                <svg class="mb-4 h-14 w-14 text-[#3b5bfd]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712Zm-2.218 5.93-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                    <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                </svg>
                <h3 class="text-lg font-bold text-slate-900">Custom</h3>
                <p class="mt-2 text-sm text-slate-500">Pilih layout, tema, dan warna sendiri dari awal.</p>
            </a>

            {{-- Template --}}
            <a href="{{ route('tenants.create.template') }}"
               class="group flex flex-col items-center rounded-2xl border-2 border-slate-200 bg-white px-8 py-10 text-center shadow-sm transition hover:border-blue-500 hover:shadow-md">
                <svg class="mb-4 h-14 w-14 text-[#3b5bfd]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <rect x="3" y="3" width="8" height="8" rx="1.5" />
                    <rect x="13" y="3" width="8" height="8" rx="1.5" />
                    <rect x="3" y="13" width="18" height="8" rx="1.5" />
                </svg>
                <h3 class="text-lg font-bold text-slate-900">Pakai Template</h3>
                <p class="mt-2 text-sm text-slate-500">Pilih preset siap pakai, lalu sesuaikan nama & subdomain.</p>
            </a>
        </div>
    </div>
</x-app-layout>
