<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-brand-theme="{{ $tenant?->theme() ?? 'modern' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('tenant.partials.app-height')
    <title>{{ $tenant->name ?? config('app.name') }}</title>
    @if (!empty($tenant->logo_path))
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$tenant->logo_path) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('images/kasiro-logo.ico') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.clarity')
    <x-brand-styles :config="$tenant->theme_config ?? []" />
    @stack('head')
</head>
<body class="antialiased flex flex-col overflow-hidden" style="height:100vh;height:100dvh;height:var(--app-h,100dvh);">
    @php
        $navUser = auth()->user();
        $navRole = $navUser?->roleFor($tenant);
        $sub     = $tenant->subdomain;
        $item    = 'flex flex-col items-center justify-center gap-0.5 flex-1 min-w-[64px] px-2 py-2 brand-nav-item transition';
        $active  = 'brand-nav-active';
    @endphp

    {{-- Header --}}
    <header class="brand-primary flex-shrink-0">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a href="{{ route('tenant.home', ['subdomain' => $sub]) }}"
               class="flex items-center gap-3 transition hover:opacity-80" aria-label="{{ __('Beranda') }} {{ $tenant->name }}">
                @if (!empty($tenant->logo_path))
                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-9 w-9 rounded-lg object-cover brand-rounded">
                @else
                    <div class="h-9 w-9 brand-rounded flex items-center justify-center text-white font-bold text-sm bg-white/20">
                        {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
                    </div>
                @endif
                <span class="font-bold text-lg tracking-tight text-white">{{ $tenant->name }}</span>
            </a>
            <div class="ml-auto flex items-center gap-2 text-sm">
                <span class="hidden sm:block text-white/70">{{ $navUser?->name }}</span>
                <span class="rounded-full bg-white/20 text-white px-2.5 py-0.5 text-xs font-medium capitalize">{{ $navRole?->value ?? '—' }}</span>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <main class="flex-1 overflow-y-auto w-full">
        <div class="max-w-7xl mx-auto lg:h-full">
            {{ $slot }}
        </div>
    </main>

    @php
        $navItems = [
            [
                'label'  => __('Beranda'),
                'href'   => route('tenant.home', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.home'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            ],
            [
                'label'  => __('Kasir'),
                'href'   => route('tenant.pos', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.pos'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
            ],
            [
                'label'  => __('Produk'),
                'href'   => route('tenant.products.index', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.products.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            ],
            [
                'label'  => __('Kategori'),
                'href'   => route('tenant.categories.index', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.categories.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>',
            ],
        ];
        if ($navRole?->canViewReports()) {
            $navItems[] = [
                'label'  => __('Laporan'),
                'href'   => route('tenant.reports', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.reports'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
            ];
            $navItems[] = [
                'label'  => __('Transaksi'),
                'href'   => route('tenant.transactions', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.transactions'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
            ];
        }
        if ($navRole?->canManageStaff()) {
            $navItems[] = [
                'label'  => __('Karyawan'),
                'href'   => route('tenant.employees.index', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.employees.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ];
            $navItems[] = [
                'label'  => __('Pengaturan'),
                'href'   => route('tenant.settings.edit', ['subdomain' => $sub]),
                'active' => request()->routeIs('tenant.settings.*'),
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ];
        }
        $navItems[] = [
            'label'  => __('Studio'),
            'href'   => route('dashboard'),
            'active' => false,
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5M5 10v9a1 1 0 001 1h12a1 1 0 001-1v-9"/>',
        ];
    @endphp

    {{-- Full navigation overlay (opened via the "more" button) --}}
    <div id="bottomNavOverlay" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="{{ __('Semua menu') }}">
        <div id="bottomNavBackdrop" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-200"></div>
        <div id="bottomNavSheet" class="absolute inset-x-0 bottom-0 brand-primary rounded-t-2xl shadow-xl translate-y-full transition-transform duration-300 ease-out">
            <div class="mx-auto max-w-3xl px-4 pt-3 pb-4">
                <div class="mx-auto mb-2 h-1 w-10 rounded-full bg-white/30"></div>
                <div class="grid grid-cols-4 gap-1">
                    @foreach ($navItems as $navItem)
                        <a href="{{ $navItem['href'] }}" data-overlay-item class="flex flex-col items-center justify-center gap-1 rounded-lg px-2 py-3 brand-nav-item transition {{ $navItem['active'] ? $active : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $navItem['icon'] !!}</svg>
                            <span class="text-[10px] font-medium">{{ $navItem['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom navigation --}}
    <nav class="brand-primary border-t border-white/15 flex-shrink-0">
        <div id="bottomNavBar" class="mx-auto max-w-3xl flex items-stretch justify-around overflow-hidden">
            @foreach ($navItems as $navItem)
                <a href="{{ $navItem['href'] }}" data-nav-item class="{{ $item }} {{ $navItem['active'] ? $active : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $navItem['icon'] !!}</svg>
                    <span class="text-[10px] font-medium">{{ $navItem['label'] }}</span>
                </a>
            @endforeach
            <button type="button" id="bottomNavMore" class="hidden {{ $item }}" aria-haspopup="true" aria-expanded="false" aria-controls="bottomNavOverlay">
                <svg id="bottomNavMoreIcon" class="w-5 h-5 flex-shrink-0 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                <span class="text-[10px] font-medium">{{ __('Lainnya') }}</span>
            </button>
        </div>
    </nav>

    {{-- Expose the bottom-nav height so page-level fixed bars (e.g. the settings
         save bar) can sit above the nav instead of covering it. Only this layout
         has a bottom nav, so other layouts leave --bottom-nav-h unset (0px). --}}
    <script>
        (function () {
            var nav = document.querySelector('nav');
            if (!nav) return;
            function setNavHeight() {
                document.documentElement.style.setProperty('--bottom-nav-h', nav.offsetHeight + 'px');
            }
            setNavHeight();
            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(setNavHeight).observe(nav);
            } else {
                window.addEventListener('resize', setNavHeight);
            }
        })();
    </script>

    <script>
        (function () {
            const bar      = document.getElementById('bottomNavBar');
            const items    = Array.from(bar.querySelectorAll('[data-nav-item]'));
            const overlayItems = Array.from(document.querySelectorAll('#bottomNavOverlay [data-overlay-item]'));
            const moreBtn  = document.getElementById('bottomNavMore');
            const moreIcon = document.getElementById('bottomNavMoreIcon');
            const overlay  = document.getElementById('bottomNavOverlay');
            const backdrop = document.getElementById('bottomNavBackdrop');
            const sheet    = document.getElementById('bottomNavSheet');
            const MIN_ITEM_WIDTH = 64;   // matches min-w-[64px] on nav items
            const MAX_VISIBLE = 4;       // max items shown alongside the "more" button
            const SHEET_ANIM_MS = 300;   // matches duration-300 on the sheet

            let isOpen = false;
            let hideTimer = null;

            function openOverlay() {
                isOpen = true;
                clearTimeout(hideTimer);
                overlay.classList.remove('hidden');
                // Force reflow so the transition runs from the hidden state
                void sheet.offsetHeight;
                backdrop.classList.add('opacity-100');
                sheet.classList.remove('translate-y-full');
                moreIcon.classList.add('rotate-90');
                moreBtn.setAttribute('aria-expanded', 'true');
            }

            function closeOverlay() {
                if (!isOpen) return;
                isOpen = false;
                backdrop.classList.remove('opacity-100');
                sheet.classList.add('translate-y-full');
                moreIcon.classList.remove('rotate-90');
                moreBtn.setAttribute('aria-expanded', 'false');
                hideTimer = setTimeout(() => overlay.classList.add('hidden'), SHEET_ANIM_MS);
            }

            function toggleOverlay() {
                isOpen ? closeOverlay() : openOverlay();
            }

            function updateNav() {
                const slots = Math.floor(bar.clientWidth / MIN_ITEM_WIDTH);
                const allFit = slots >= items.length;
                const visible = allFit ? items.length : Math.max(1, Math.min(MAX_VISIBLE, slots - 1));
                items.forEach((el, i) => el.classList.toggle('hidden', i >= visible));
                // The popup only lists items that no longer fit in the bar
                overlayItems.forEach((el, i) => el.classList.toggle('hidden', i < visible));
                moreBtn.classList.toggle('hidden', allFit);
                if (allFit) closeOverlay();
            }

            moreBtn.addEventListener('click', toggleOverlay);
            backdrop.addEventListener('click', closeOverlay);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeOverlay(); });

            if (typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(updateNav).observe(bar);
            } else {
                window.addEventListener('resize', updateNav);
            }
            updateNav();
        })();
    </script>
    <x-flash-modal />
    @include('partials.confirm-modal')
    @stack('scripts')
</body>
</html>
