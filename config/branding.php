<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Structural layouts (maps to Blade files under tenant/layouts/)
    | Controls: navigation position, content arrangement
    |--------------------------------------------------------------------------
    */

    'layouts' => [
        'topbar'    => ['label' => 'Navigasi Atas'],
        'sidebar'   => ['label' => 'Navigasi Samping'],
        'bottombar' => ['label' => 'Navigasi Bawah'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Visual themes — controls typography and shape
    | Each theme declares its allowed palettes and CSS typography vars
    |--------------------------------------------------------------------------
    */

    'themes' => [
        'modern' => [
            'label'    => 'Modern',
            'palettes' => ['violet', 'sky', 'orange'],
            'vars'     => [
                '--brand-font'   => '"Wix Madefor Text", system-ui, sans-serif',
                '--brand-radius' => '0.875rem',
            ],
        ],
        'classic' => [
            'label'    => 'Classic',
            'palettes' => ['slate', 'indigo', 'emerald'],
            'vars'     => [
                '--brand-font'   => 'Georgia, "Times New Roman", serif',
                '--brand-radius' => '0.375rem',
            ],
        ],
        'retro' => [
            'label'    => 'Retro',
            'palettes' => ['amber', 'rose', 'teal', 'forest'],
            'vars'     => [
                '--brand-font'   => '"Courier New", Courier, monospace',
                '--brand-radius' => '0px',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Color palettes → CSS custom property values
    | Grouped conceptually by theme but stored flat for lookup efficiency.
    | Add new palettes here; register them in the parent theme's 'palettes' list.
    | Each palette is multi-dimensional: a brand primary + darker accent, a calm
    | tinted page background, a white-ish card surface, a soft border, dark body
    | text, and a muted secondary text — so the UI never collapses into one hue.
    |--------------------------------------------------------------------------
    */

    'palettes' => [
        // modern
        'violet' => [
            '--brand-primary' => '#7c3aed',
            '--brand-accent'  => '#6d28d9',
            '--brand-bg'      => '#f6f4fb',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#e8e2f6',
            '--brand-fg'      => '#241b38',
            '--brand-muted'   => '#6c6685',
        ],
        'sky' => [
            '--brand-primary' => '#0284c7',
            '--brand-accent'  => '#0369a1',
            '--brand-bg'      => '#f1f7fb',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#d9e9f4',
            '--brand-fg'      => '#0d2a3a',
            '--brand-muted'   => '#5a7689',
        ],
        'orange' => [
            '--brand-primary' => '#ea580c',
            '--brand-accent'  => '#c2410c',
            '--brand-bg'      => '#fcf6f1',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#f6e2d3',
            '--brand-fg'      => '#3a1e0e',
            '--brand-muted'   => '#8a6c5b',
        ],
        // classic
        'slate' => [
            '--brand-primary' => '#475569',
            '--brand-accent'  => '#334155',
            '--brand-bg'      => '#f7f8fa',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#e2e8f0',
            '--brand-fg'      => '#0f172a',
            '--brand-muted'   => '#64748b',
        ],
        'indigo' => [
            '--brand-primary' => '#4f46e5',
            '--brand-accent'  => '#4338ca',
            '--brand-bg'      => '#f3f4fb',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#e0e2f6',
            '--brand-fg'      => '#1e1b4b',
            '--brand-muted'   => '#6b6fa0',
        ],
        'emerald' => [
            '--brand-primary' => '#059669',
            '--brand-accent'  => '#047857',
            '--brand-bg'      => '#f2faf6',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#d4ece0',
            '--brand-fg'      => '#073a2c',
            '--brand-muted'   => '#5b8473',
        ],
        // retro
        'amber' => [
            '--brand-primary' => '#d97706',
            '--brand-accent'  => '#b45309',
            '--brand-bg'      => '#fcf8ee',
            '--brand-surface' => '#fffefb',
            '--brand-border'  => '#f0e3c4',
            '--brand-fg'      => '#3a2606',
            '--brand-muted'   => '#8a7242',
        ],
        'rose' => [
            '--brand-primary' => '#e11d48',
            '--brand-accent'  => '#be123c',
            '--brand-bg'      => '#fdf3f4',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#f7d9de',
            '--brand-fg'      => '#4c0519',
            '--brand-muted'   => '#9a5563',
        ],
        'teal' => [
            '--brand-primary' => '#0d9488',
            '--brand-accent'  => '#0f766e',
            '--brand-bg'      => '#f1faf8',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#cfeae5',
            '--brand-fg'      => '#0f4a45',
            '--brand-muted'   => '#5b8a84',
        ],
        'forest' => [
            '--brand-primary' => '#16a34a',
            '--brand-accent'  => '#15803d',
            '--brand-bg'      => '#f2faf4',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#d4ecd9',
            '--brand-fg'      => '#14532d',
            '--brand-muted'   => '#5b8468',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default values when no theme_config is set
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'layout'        => 'topbar',
        'theme'         => 'modern',
        'color_palette' => 'violet',
    ],

];
