@props(['tenant', 'role' => null])

<div class="rounded-xl border-2 border-gray-200 bg-white overflow-hidden flex flex-col hover:border-indigo-300 transition">
    {{-- Header strip with logo/initials --}}
    <div class="h-16 flex items-center px-4 gap-3"
         style="background-color: {{ \App\Support\ThemeConfig::cssVariables($tenant->theme_config ?? [])['--brand-primary'] ?? '#6366f1' }}20;">
        @if ($tenant->logo_path)
            <img src="{{ asset('storage/'.$tenant->logo_path) }}"
                 alt="{{ $tenant->name }}" class="h-10 w-10 rounded-full object-cover border border-white shadow">
        @else
            <span class="h-10 w-10 rounded-full flex items-center justify-center text-white text-lg font-bold shadow"
                  style="background-color: {{ \App\Support\ThemeConfig::cssVariables($tenant->theme_config ?? [])['--brand-primary'] ?? '#6366f1' }};">
                {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
            </span>
        @endif

        <div class="min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ $tenant->name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ $tenant->subdomain }}.{{ config('tenancy.central_domain') }}</p>
        </div>
    </div>

    <div class="px-4 py-3 flex-1 flex flex-col gap-2">
        {{-- Badges --}}
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600">
                {{ ucfirst($tenant->layout()) }}
            </span>
            @if ($role)
                @php
                    $badgeColor = match($role->value) {
                        'owner'   => 'bg-indigo-100 text-indigo-700',
                        'manager' => 'bg-emerald-100 text-emerald-700',
                        default   => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColor }}">
                    {{ ucfirst($role->value) }}
                </span>
            @endif
        </div>

        {{-- Link to subdomain --}}
        <a href="{{ $tenant->subdomainUrl() }}" target="_blank" rel="noopener"
           class="mt-auto inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            Buka Toko →
        </a>

        {{-- Slot for action buttons (archive/restore) --}}
        {{ $slot }}
    </div>
</div>
