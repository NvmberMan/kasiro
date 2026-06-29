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

    .brand-card {
        background-color: var(--brand-surface, #ffffff);
        border: 1px solid var(--brand-border, #e2e8f0);
        border-radius: var(--brand-radius, 0.75rem);
    }

    .brand-btn {
        background-color: var(--brand-primary);
        color: #fff;
        border-radius: var(--brand-radius, 0.75rem);
        transition: background-color .15s ease;
    }
    .brand-btn:hover { background-color: var(--brand-accent); }

    /* Tinted "soft" surface using the brand hue at low alpha (active nav, chips). */
    .brand-soft     { background-color: color-mix(in srgb, var(--brand-primary) 12%, transparent); }
    .brand-ring-focus:focus { outline: none; box-shadow: 0 0 0 2px var(--brand-surface,#fff), 0 0 0 4px var(--brand-primary); }

    /* Navbar / bottombar helpers — used when nav background is brand-primary */
    .brand-nav-item       { color: rgba(255,255,255,0.65); transition: color .15s ease; }
    .brand-nav-item:hover { color: rgba(255,255,255,0.9); }
    .brand-nav-active     { color: #fff !important; background-color: rgba(255,255,255,0.18); }
</style>
