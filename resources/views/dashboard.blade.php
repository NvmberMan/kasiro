<x-app-layout>
    @php
        $roleLabels = ['owner' => 'Pemilik', 'manager' => 'Manajer', 'cashier' => 'Kasir'];
        $scheme = request()->isSecure() ? 'https' : 'http';
        $central = config('tenancy.central_domain');
    @endphp

    {{-- Header: greeting + stat cards --}}
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Halo, {{ auth()->user()->name }}!</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola sistem kasir untuk toko Anda di sini.</p>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('my-stores') }}" class="min-w-[6rem] rounded-2xl bg-white px-6 py-4 text-center shadow-sm ring-1 ring-slate-100 transition hover:shadow">
                <span class="block text-3xl font-extrabold text-blue-600">{{ $activeCount }}</span>
                <span class="text-xs text-slate-500">Toko Aktif</span>
            </a>
            <a href="{{ route('archive') }}" class="min-w-[6rem] rounded-2xl bg-white px-6 py-4 text-center shadow-sm ring-1 ring-slate-100 transition hover:shadow">
                <span class="block text-3xl font-extrabold text-slate-800">{{ $archivedCount }}</span>
                <span class="text-xs text-slate-500">Diarsipkan</span>
            </a>
        </div>
    </div>

    {{-- Recent stores --}}
    <div class="mt-10">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Sistem Kasir Terbaru</h2>
            <a href="{{ route('my-stores') }}"
               class="rounded-full border border-blue-600 px-4 py-1.5 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                Lihat Semua
            </a>
        </div>

        @if ($recent->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
                <p class="mb-4 text-slate-400">Belum ada toko. Mulai buat toko pertama Anda!</p>
                <a href="{{ route('tenants.choose') }}"
                   class="inline-flex rounded-full bg-lime-400 px-6 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-lime-500">
                    Buat Toko Baru
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($recent as $tenant)
                    @php
                        $role = auth()->user()->roleFor($tenant);
                        $roleLabel = $role ? ($roleLabels[$role->value] ?? ucfirst($role->value)) : null;
                        $storeUrl = $scheme.'://'.$tenant->subdomain.'.'.$central;
                    @endphp
                    @php
                        $screenshot = $tenant->screenshotUrl();
                        $brandColor = \App\Support\ThemeConfig::cssVariables($tenant->theme_config ?? [])['--brand-primary'] ?? '#6366f1';
                    @endphp
                    <a href="{{ $storeUrl }}" class="group block">
                        <div class="relative h-44 w-full rounded-xl ring-1 ring-slate-200 transition group-hover:ring-blue-400 overflow-hidden bg-slate-100">
                            @if ($screenshot)
                                <img src="{{ $screenshot }}" alt="{{ $tenant->name }}"
                                     class="h-full w-full object-cover object-top transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $brandColor }};"></div>
                            @else
                                {{-- Skeleton while screenshot is being generated --}}
                                <div class="h-full w-full animate-pulse p-4 flex flex-col gap-2"
                                     style="background: linear-gradient(135deg, {{ $brandColor }}12 0%, {{ $brandColor }}06 100%);">
                                    <div class="h-3 rounded-full bg-gray-200 w-2/5"></div>
                                    <div class="flex gap-2 mt-1">
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-200"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-100"></div>
                                        <div class="h-16 flex-1 rounded-lg bg-gray-100"></div>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-200 w-1/2 mt-auto"></div>
                                </div>
                            @endif

                            {{-- Hover overlay with "Lihat" button --}}
                            <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-lime-400 px-5 py-2 text-sm font-semibold text-slate-900 shadow-lg">
                                    Lihat
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        @if ($roleLabel)
                            <span class="mt-3 inline-block rounded-full border border-lime-500 bg-lime-50 px-3 py-0.5 text-xs font-medium text-lime-700">
                                {{ $roleLabel }}
                            </span>
                        @endif
                        <p class="mt-2 font-semibold text-slate-800">{{ $tenant->name }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- CTA banner --}}
    <div class="mt-10 border-t border-slate-200 pt-10">
        <div class="flex flex-col items-center justify-between gap-4 rounded-3xl bg-gradient-to-r from-blue-300 via-blue-400 to-blue-600 px-8 py-6 sm:flex-row">
            <h3 class="text-xl font-bold text-slate-900">Buat Sistem Kasir Impian Anda</h3>
            <a href="{{ route('tenants.choose') }}"
               class="inline-flex items-center gap-2 rounded-full bg-lime-400 px-6 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-lime-500">
                Buat Sekarang
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/></svg>
            </a>
        </div>
    </div>
</x-app-layout>
