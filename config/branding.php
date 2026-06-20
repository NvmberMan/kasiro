<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Structural layouts (maps to Blade files under tenant/layouts/)
    | Controls: navigation position, content arrangement
    |--------------------------------------------------------------------------
    */

    'layouts' => [
        'topbar'  => ['label' => 'Navigasi Atas'],
        'sidebar' => ['label' => 'Navigasi Samping'],
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
                '--brand-font'   => '"Inter", system-ui, sans-serif',
                '--brand-radius' => '0.75rem',
            ],
        ],
        'classic' => [
            'label'    => 'Classic',
            'palettes' => ['slate', 'indigo', 'emerald'],
            'vars'     => [
                '--brand-font'   => 'Georgia, "Times New Roman", serif',
                '--brand-radius' => '0.25rem',
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
    |--------------------------------------------------------------------------
    */

    'palettes' => [
        // modern
        'violet' => [
            '--brand-primary' => '#7c3aed',
            '--brand-accent'  => '#6d28d9',
            '--brand-bg'      => '#faf5ff',
            '--brand-fg'      => '#2e1065',
        ],
        'sky' => [
            '--brand-primary' => '#0ea5e9',
            '--brand-accent'  => '#0284c7',
            '--brand-bg'      => '#f0f9ff',
            '--brand-fg'      => '#0c4a6e',
        ],
        'orange' => [
            '--brand-primary' => '#f97316',
            '--brand-accent'  => '#ea580c',
            '--brand-bg'      => '#fff7ed',
            '--brand-fg'      => '#431407',
        ],
        // classic
        'slate' => [
            '--brand-primary' => '#475569',
            '--brand-accent'  => '#334155',
            '--brand-bg'      => '#f8fafc',
            '--brand-fg'      => '#0f172a',
        ],
        'indigo' => [
            '--brand-primary' => '#4f46e5',
            '--brand-accent'  => '#7c3aed',
            '--brand-bg'      => '#eef2ff',
            '--brand-fg'      => '#1e1b4b',
        ],
        'emerald' => [
            '--brand-primary' => '#10b981',
            '--brand-accent'  => '#059669',
            '--brand-bg'      => '#f0fdf4',
            '--brand-fg'      => '#064e3b',
        ],
        // retro
        'amber' => [
            '--brand-primary' => '#f59e0b',
            '--brand-accent'  => '#d97706',
            '--brand-bg'      => '#fffbeb',
            '--brand-fg'      => '#451a03',
        ],
        'rose' => [
            '--brand-primary' => '#f43f5e',
            '--brand-accent'  => '#e11d48',
            '--brand-bg'      => '#fff1f2',
            '--brand-fg'      => '#4c0519',
        ],
        'teal' => [
            '--brand-primary' => '#14b8a6',
            '--brand-accent'  => '#0d9488',
            '--brand-bg'      => '#f0fdfa',
            '--brand-fg'      => '#134e4a',
        ],
        'forest' => [
            '--brand-primary' => '#16a34a',
            '--brand-accent'  => '#15803d',
            '--brand-bg'      => '#f0fdf4',
            '--brand-fg'      => '#14532d',
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
