@php
    $navUser = auth()->user();
    $navRole = $navUser?->roleFor($tenant);
    $sub     = $tenant->subdomain;
    $base    = 'flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-full whitespace-nowrap transition brand-nav-item';
    $on      = 'brand-nav-active';

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
        ],
        [
            'label'  => __('Produk'),
            'href'   => route('tenant.products.index', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.products.*'),
        ],
        [
            'label'  => __('Kategori'),
            'href'   => route('tenant.categories.index', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.categories.*'),
        ],
    ];
    if ($navRole?->canViewReports()) {
        $navItems[] = [
            'label'  => __('Laporan'),
            'href'   => route('tenant.reports', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.reports'),
        ];
        $navItems[] = [
            'label'  => __('Transaksi'),
            'href'   => route('tenant.transactions', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.transactions'),
        ];
    }
    if ($navRole?->canManageStaff()) {
        $navItems[] = [
            'label'  => __('Karyawan'),
            'href'   => route('tenant.employees.index', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.employees.*'),
        ];
        $navItems[] = [
            'label'  => __('Pengaturan'),
            'href'   => route('tenant.settings.edit', ['subdomain' => $sub]),
            'active' => request()->routeIs('tenant.settings.*'),
        ];
    }
    $navItems[] = [
        'label'  => __('Studio'),
        'href'   => route('dashboard'),
        'active' => false,
        'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5M5 10v9a1 1 0 001 1h12a1 1 0 001-1v-9"/>',
        'right'  => true,
    ];
@endphp
{{-- Backdrop for the full-menu dropdown --}}
<div id="topNavBackdrop" class="hidden fixed inset-0 z-30 bg-black/50 opacity-0 transition-opacity duration-200"></div>

<nav class="brand-primary border-t border-white/15 relative z-40">
    <div id="topNavBar" class="mx-auto max-w-7xl px-4 flex items-center gap-1 overflow-hidden py-2">
        @foreach ($navItems as $navItem)
            <a href="{{ $navItem['href'] }}" data-nav-item
               class="{{ !empty($navItem['right']) ? 'ml-auto' : '' }} {{ $base }} {{ $navItem['active'] ? $on : '' }}">
                @if (!empty($navItem['icon']))
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $navItem['icon'] !!}</svg>
                @endif
                {{ $navItem['label'] }}
            </a>
        @endforeach
        <button type="button" id="topNavMore" class="hidden ml-auto {{ $base }}"
                aria-haspopup="true" aria-expanded="false" aria-controls="topNavOverlay">
            <svg id="topNavMoreIcon" class="h-4 w-4 flex-shrink-0 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
            {{ __('Lainnya') }}
        </button>
    </div>

    {{-- Full navigation dropdown (opened via the "more" button) --}}
    <div id="topNavOverlay" class="hidden absolute inset-x-0 top-full z-50" role="dialog" aria-modal="true" aria-label="{{ __('Semua menu') }}">
        <div id="topNavPanel" class="brand-primary border-t border-white/15 rounded-b-2xl shadow-xl opacity-0 -translate-y-3 transition-all duration-300 ease-out">
            <div class="mx-auto max-w-7xl px-4 py-3 flex flex-wrap items-center gap-1">
                @foreach ($navItems as $navItem)
                    <a href="{{ $navItem['href'] }}" data-overlay-item class="{{ $base }} {{ $navItem['active'] ? $on : '' }}">
                        @if (!empty($navItem['icon']))
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $navItem['icon'] !!}</svg>
                        @endif
                        {{ $navItem['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>

<script>
    (function () {
        const bar      = document.getElementById('topNavBar');
        const items    = Array.from(bar.querySelectorAll('[data-nav-item]'));
        const overlayItems = Array.from(document.querySelectorAll('#topNavOverlay [data-overlay-item]'));
        const moreBtn  = document.getElementById('topNavMore');
        const moreIcon = document.getElementById('topNavMoreIcon');
        const overlay  = document.getElementById('topNavOverlay');
        const panel    = document.getElementById('topNavPanel');
        const backdrop = document.getElementById('topNavBackdrop');
        const GAP = 4;             // matches gap-1 on the bar
        const MAX_VISIBLE = 4;     // max items shown alongside the "more" button
        const PANEL_ANIM_MS = 300; // matches duration-300 on the panel

        let isOpen = false;
        let hideTimer = null;

        function openOverlay() {
            isOpen = true;
            clearTimeout(hideTimer);
            overlay.classList.remove('hidden');
            backdrop.classList.remove('hidden');
            // Force reflow so the transition runs from the hidden state
            void panel.offsetHeight;
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', '-translate-y-3');
            moreIcon.classList.add('rotate-90');
            moreBtn.setAttribute('aria-expanded', 'true');
        }

        function closeOverlay() {
            if (!isOpen) return;
            isOpen = false;
            backdrop.classList.remove('opacity-100');
            panel.classList.add('opacity-0', '-translate-y-3');
            moreIcon.classList.remove('rotate-90');
            moreBtn.setAttribute('aria-expanded', 'false');
            hideTimer = setTimeout(() => {
                overlay.classList.add('hidden');
                backdrop.classList.add('hidden');
            }, PANEL_ANIM_MS);
        }

        function toggleOverlay() {
            isOpen ? closeOverlay() : openOverlay();
        }

        function updateNav() {
            // Show everything first so real pill widths can be measured
            items.forEach((el) => el.classList.remove('hidden'));
            moreBtn.classList.remove('hidden');

            const style = getComputedStyle(bar);
            const avail = bar.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
            const widths = items.map((el) => el.offsetWidth);
            const totalAll = widths.reduce((a, b) => a + b, 0) + GAP * (items.length - 1);

            if (totalAll <= avail) {
                moreBtn.classList.add('hidden');
                closeOverlay();
                return;
            }

            let used = moreBtn.offsetWidth;
            let visible = 0;
            while (visible < items.length && visible < MAX_VISIBLE) {
                const next = used + GAP + widths[visible];
                if (next > avail) break;
                used = next;
                visible++;
            }
            visible = Math.max(1, visible);
            items.forEach((el, i) => el.classList.toggle('hidden', i >= visible));
            // The popup only lists items that no longer fit in the bar
            overlayItems.forEach((el, i) => el.classList.toggle('hidden', i < visible));
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
