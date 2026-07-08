@props(['tenant', 'role' => null])

@php
    $screenshot = $tenant->screenshotUrl();
    $brandColor = \App\Support\ThemeConfig::cssVariables($tenant->theme_config ?? [])['--brand-primary'] ?? '#6366f1';
    $roleLabels = ['owner' => __('Pemilik'), 'manager' => __('Manajer'), 'cashier' => __('Kasir')];
    $roleLabel = $role ? ($roleLabels[$role->value] ?? ucfirst($role->value)) : null;
@endphp

<div class="group">
    <a href="{{ $tenant->subdomainUrl() }}" target="_blank" rel="noopener" class="block">
        <div class="relative aspect-video w-full rounded-xl ring-1 ring-slate-200 transition group-hover:ring-blue-400 overflow-hidden bg-slate-100">
            @if ($screenshot)
                <img src="{{ $screenshot }}" alt="{{ $tenant->name }}"
                     class="h-full w-full object-cover object-top transition-transform duration-300 group-hover:scale-105">
                <div class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $brandColor }};"></div>
            @else
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
                    {{ __('Lihat') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8"/>
                    </svg>
                </span>
            </div>
        </div>
    </a>

    @if ($roleLabel)
        <span class="mt-3 inline-block rounded-full border border-lime-500 bg-lime-50 px-3 py-0.5 text-xs font-medium text-lime-700">
            {{ $roleLabel }}
        </span>
    @endif
    <p class="mt-2 font-semibold text-slate-800">{{ $tenant->name }}</p>

    {{ $slot }}
</div>
