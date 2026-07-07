@props(['config' => []])

@php
    $vars = \App\Support\ThemeConfig::cssVariables($config);
@endphp

<style>
    :root {
        @foreach ($vars as $property => $value)
            {!! $property !!}: {!! $value !!};
        @endforeach
    }

    body {
        font-family: var(--brand-font, 'Wix Madefor Text', system-ui, sans-serif);
        background-color: var(--brand-bg, #f7f8fa);
        color: var(--brand-fg, #0f172a);
    }

    /* Shared brand token helpers — keep tenant chrome cohesive across layouts. */
    .brand-primary  { background-color: var(--brand-primary); }
    .brand-accent   { background-color: var(--brand-accent); }
    .brand-text     { color: var(--brand-primary); }
    .brand-surface  { background-color: var(--brand-surface, #ffffff); }
    .brand-muted    { color: var(--brand-muted, #64748b); }
    .brand-border   { border-color: var(--brand-border, #e2e8f0); }
    .brand-rounded  { border-radius: var(--brand-radius, 0.75rem); }
    /* Per-theme elevation: soft blur (modern), refined (classic), hard offset (retro). */
    .brand-shadow   { box-shadow: var(--brand-shadow, 0 1px 3px rgba(15,23,42,0.08)); }

    .brand-card {
        background-color: var(--brand-surface, #ffffff);
        border: var(--brand-border-width, 1px) solid var(--brand-border, #e2e8f0);
        border-radius: var(--brand-radius, 0.75rem);
        box-shadow: var(--brand-shadow, 0 1px 3px rgba(15,23,42,0.08));
        transition: box-shadow .18s ease, transform .18s ease;
    }
    .brand-card-hover:hover { box-shadow: var(--brand-shadow-hover, var(--brand-shadow)); }

    .brand-btn {
        background-color: var(--brand-primary);
        color: #fff;
        border-radius: var(--brand-radius, 0.75rem);
        box-shadow: var(--brand-shadow, none);
        transition: background-color .15s ease, box-shadow .15s ease;
    }
    .brand-btn:hover { background-color: var(--brand-accent); box-shadow: var(--brand-shadow-hover, var(--brand-shadow)); }

    /* Themed scrollbar — replaces the flat OS-default bar so it matches
       each theme's palette and corner treatment (rounded for modern,
       square for classic/retro, via --brand-radius). */
    html {
        scrollbar-width: thin;
        scrollbar-color: var(--brand-muted, #94a3b8) transparent;
    }
    *::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    *::-webkit-scrollbar-track {
        background: transparent;
    }
    *::-webkit-scrollbar-thumb {
        background-color: var(--brand-muted, #94a3b8);
        border-radius: var(--brand-radius, 0px);
        border: 2px solid transparent;
        background-clip: padding-box;
    }
    *::-webkit-scrollbar-thumb:hover {
        background-color: var(--brand-primary);
    }

    /* Tinted "soft" surface using the brand hue at low alpha (active nav, chips). */
    .brand-soft     { background-color: color-mix(in srgb, var(--brand-primary) 12%, transparent); }
    .brand-ring-focus:focus { outline: none; box-shadow: 0 0 0 2px var(--brand-surface,#fff), 0 0 0 4px var(--brand-primary); }

    /* Navbar / bottombar helpers — used when nav background is brand-primary */
    .brand-nav-item       { color: rgba(255,255,255,0.65); transition: color .15s ease; }
    .brand-nav-item:hover { color: rgba(255,255,255,0.9); }
    .brand-nav-active     { color: #fff !important; background-color: rgba(255,255,255,0.18); }

    /* ==================================================================
       THEME CHARACTER — scoped by [data-brand-theme] on <html>.
       Most tenant screens use raw Tailwind utilities (rounded-*, bg-white,
       shadow-*). To make Classic and Retro genuinely *feel* different from
       Modern (not just fonts/colors), we remap those utilities per theme.
       Modern is the baseline and intentionally gets no overrides.
       ================================================================== */

    /* --- CLASSIC: squared Windows-style panels with 3D beveled edges --- */
    [data-brand-theme="classic"] .rounded,
    [data-brand-theme="classic"] .rounded-md,
    [data-brand-theme="classic"] .rounded-lg,
    [data-brand-theme="classic"] .rounded-xl,
    [data-brand-theme="classic"] .rounded-2xl,
    [data-brand-theme="classic"] .rounded-3xl,
    [data-brand-theme="classic"] .rounded-full {
        border-radius: 0 !important;
    }
    /* White cards become the silver panel face. */
    [data-brand-theme="classic"] .bg-white {
        background-color: var(--brand-surface) !important;
    }
    /* Every elevated surface gets the raised chiseled bevel instead of a soft blur. */
    [data-brand-theme="classic"] .shadow-sm,
    [data-brand-theme="classic"] .shadow,
    [data-brand-theme="classic"] .shadow-md,
    [data-brand-theme="classic"] .shadow-lg,
    [data-brand-theme="classic"] .shadow-xl,
    [data-brand-theme="classic"] .shadow-2xl {
        box-shadow: var(--brand-shadow) !important;
    }
    /* Thin light hairlines clash with the bevel — hide them so edges stay crisp. */
    [data-brand-theme="classic"] .border-gray-50,
    [data-brand-theme="classic"] .border-gray-100 {
        border-color: transparent !important;
    }
    /* Inputs read as recessed fields (classic sunken bevel). */
    [data-brand-theme="classic"] input:not([type="checkbox"]):not([type="radio"]),
    [data-brand-theme="classic"] select,
    [data-brand-theme="classic"] textarea {
        background-color: #ffffff !important;
        box-shadow: inset 1px 1px #808080, inset -1px -1px #ffffff, inset 2px 2px #404040, inset -2px -2px #dfdfdf !important;
        border-color: transparent !important;
    }
    /* Selectable option boxes (theme/layout/palette cards) use a faint gray-200
       hairline that vanishes on the silver panel. Give every 2px-bordered box a
       raised bevel so its 3D edge is always visible, hover or not. */
    [data-brand-theme="classic"] .border-2 {
        box-shadow: var(--brand-shadow);
    }

    /* Title bar + toolbar: chisel the flat brand-primary chrome into classic
       Windows 3D edges, and turn nav links into raised toolbar buttons with a
       pressed (sunken) state for the active item. */
    [data-brand-theme="classic"] header.brand-primary {
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.35), inset 0 -2px 0 rgba(0,0,0,0.28);
    }
    [data-brand-theme="classic"] nav.brand-primary {
        border-top-color: rgba(0,0,0,0.25) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.28), inset 0 -2px 0 rgba(0,0,0,0.3);
    }
    [data-brand-theme="classic"] .brand-nav-item {
        background: rgba(255,255,255,0.10);
        box-shadow: inset 1px 1px rgba(255,255,255,0.4), inset -1px -1px rgba(0,0,0,0.32);
    }
    [data-brand-theme="classic"] .brand-nav-item:hover {
        background: rgba(255,255,255,0.18);
        color: #fff;
    }
    [data-brand-theme="classic"] .brand-nav-active {
        background: rgba(0,0,0,0.16) !important;
        box-shadow: inset -1px -1px rgba(255,255,255,0.3), inset 1px 1px rgba(0,0,0,0.42) !important;
    }
    /* Topbar "Lainnya" dropdown panel: the raised bevel's white top-left/left
       highlight looks wrong on this overlay — keep only the dark bottom-right
       edge. Scoped to this panel + classic only (bottombar has no such panel). */
    [data-brand-theme="classic"] #topNavPanel {
        box-shadow: inset -2px -2px #808080, inset -1px -1px #0a0a0a !important;
    }

    /* Pagination (vendor tailwind.blade.php): text-gray-500/700 and the
       active page's bg-gray-200 pill sit too close to classic's silver
       surface to read clearly — darken the numbers and make the active
       page an unmistakable filled pill. */
    [data-brand-theme="classic"] nav[role="navigation"] a.text-gray-700,
    [data-brand-theme="classic"] nav[role="navigation"] span.text-gray-700 {
        color: var(--brand-fg) !important;
    }
    [data-brand-theme="classic"] nav[role="navigation"] .bg-gray-200 {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #fff !important;
    }

    /* --- RETRO: squared, chunky-bordered cards with a hard offset shadow --- */
    [data-brand-theme="retro"] .rounded,
    [data-brand-theme="retro"] .rounded-md,
    [data-brand-theme="retro"] .rounded-lg,
    [data-brand-theme="retro"] .rounded-xl,
    [data-brand-theme="retro"] .rounded-2xl,
    [data-brand-theme="retro"] .rounded-3xl,
    [data-brand-theme="retro"] .rounded-full {
        border-radius: 0 !important;
    }
    [data-brand-theme="retro"] .bg-white {
        background-color: var(--brand-surface) !important;
    }
    [data-brand-theme="retro"] .shadow-sm,
    [data-brand-theme="retro"] .shadow,
    [data-brand-theme="retro"] .shadow-md,
    [data-brand-theme="retro"] .shadow-lg,
    [data-brand-theme="retro"] .shadow-xl,
    [data-brand-theme="retro"] .shadow-2xl {
        box-shadow: var(--brand-shadow) !important;
    }
    /* Bold ink outlines are the retro signature. */
    [data-brand-theme="retro"] .border,
    [data-brand-theme="retro"] .border-gray-50,
    [data-brand-theme="retro"] .border-gray-100,
    [data-brand-theme="retro"] .border-gray-200 {
        border-width: 2px !important;
        border-color: var(--brand-fg) !important;
    }

    /* Pagination: same low-contrast issue as classic — darken the digits
       and give the active page a solid, unmistakable fill. */
    [data-brand-theme="retro"] nav[role="navigation"] a.text-gray-700,
    [data-brand-theme="retro"] nav[role="navigation"] span.text-gray-700 {
        color: var(--brand-fg) !important;
    }
    [data-brand-theme="retro"] nav[role="navigation"] .bg-gray-200 {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #fff !important;
    }
</style>
