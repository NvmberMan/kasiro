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
            'palettes' => ['violet', 'sky', 'cyan', 'green', 'orange', 'pink'],
            'vars'     => [
                '--brand-font'         => '"Wix Madefor Text", system-ui, sans-serif',
                '--brand-radius'       => '0.875rem',
                // Soft, blurred elevation — light and airy.
                '--brand-shadow'       => '0 1px 3px rgba(15,23,42,0.08), 0 1px 2px rgba(15,23,42,0.04)',
                '--brand-shadow-hover' => '0 6px 16px rgba(15,23,42,0.12)',
                '--brand-border-width' => '1px',
            ],
        ],
        'classic' => [
            'label'    => 'Classic',
            'palettes' => ['slate', 'graphite', 'silver', 'indigo', 'emerald', 'maroon'],
            'vars'     => [
                // Classic Windows UI sans — Tahoma/Segoe, chiseled and utilitarian.
                '--brand-font'         => 'Tahoma, "Segoe UI", Verdana, Geneva, sans-serif',
                '--brand-radius'       => '0px',
                // 3D "raised panel" bevel (classic Windows chrome): white top-left
                // highlight + dark bottom-right shadow, layered, no blur.
                '--brand-shadow'       => 'inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf',
                '--brand-shadow-hover' => 'inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf',
                '--brand-border-width' => '0px',
            ],
        ],
        'retro' => [
            'label'    => 'Retro',
            'palettes' => ['amber', 'mustard', 'rose', 'teal', 'forest', 'mocha'],
            'vars'     => [
                // Vintage terminal monospace — sharp, blocky, unmistakably retro.
                '--brand-font'         => '"Space Mono", "Courier New", Courier, monospace',
                '--brand-radius'       => '0px',
                // Hard, offset "sticker" shadow — no blur, chunky borders.
                '--brand-shadow'       => '3px 3px 0 rgba(0,0,0,0.20)',
                '--brand-shadow-hover' => '5px 5px 0 rgba(0,0,0,0.24)',
                '--brand-border-width' => '2px',
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
        'cyan' => [
            '--brand-primary' => '#0891b2',
            '--brand-accent'  => '#0e7490',
            '--brand-bg'      => '#eef9fc',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#cfeef6',
            '--brand-fg'      => '#0a3844',
            '--brand-muted'   => '#4f8593',
        ],
        'green' => [
            '--brand-primary' => '#059669',
            '--brand-accent'  => '#047857',
            '--brand-bg'      => '#eefbf5',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#cceee0',
            '--brand-fg'      => '#063d2c',
            '--brand-muted'   => '#4f8c73',
        ],
        'pink' => [
            '--brand-primary' => '#db2777',
            '--brand-accent'  => '#be185d',
            '--brand-bg'      => '#fdf2f8',
            '--brand-surface' => '#ffffff',
            '--brand-border'  => '#f8d3e4',
            '--brand-fg'      => '#4d0a2b',
            '--brand-muted'   => '#9a5c77',
        ],
        // classic — Windows-style silver panels: neutral gray surfaces that let
        // the 3D bevel read, with the palette's hue reserved for the title bar
        // (header/buttons via --brand-primary). Sharp corners, chiseled edges.
        'slate' => [
            '--brand-primary' => '#3a6ea5', // classic Windows title-bar blue
            '--brand-accent'  => '#2c5580',
            '--brand-bg'      => '#d5d8dc', // silver desktop
            '--brand-surface' => '#ececee', // beveled panel face
            '--brand-border'  => '#9aa0a6',
            '--brand-fg'      => '#1c1f22',
            '--brand-muted'   => '#5b6169',
        ],
        'indigo' => [
            '--brand-primary' => '#464b8a', // indigo title bar
            '--brand-accent'  => '#363a6b',
            '--brand-bg'      => '#d6d6dd',
            '--brand-surface' => '#ececef',
            '--brand-border'  => '#9c9caa',
            '--brand-fg'      => '#1e1f2b',
            '--brand-muted'   => '#5c5e70',
        ],
        'emerald' => [
            '--brand-primary' => '#2f7d55', // green title bar
            '--brand-accent'  => '#245f3f',
            '--brand-bg'      => '#d3d9d4',
            '--brand-surface' => '#eaeeeb',
            '--brand-border'  => '#98a29a',
            '--brand-fg'      => '#1a2620',
            '--brand-muted'   => '#586a5f',
        ],
        'graphite' => [
            '--brand-primary' => '#616a75', // neutral steel-gray title bar
            '--brand-accent'  => '#474e57',
            '--brand-bg'      => '#d7d7d7',
            '--brand-surface' => '#ededed',
            '--brand-border'  => '#9c9c9c',
            '--brand-fg'      => '#1e1e1e',
            '--brand-muted'   => '#5c5c5c',
        ],
        'silver' => [
            '--brand-primary' => '#808080', // all-gray Win95 title bar (no hue)
            '--brand-accent'  => '#606060',
            '--brand-bg'      => '#cfcfcf', // classic silver desktop
            '--brand-surface' => '#e4e4e4',
            '--brand-border'  => '#949494',
            '--brand-fg'      => '#1c1c1c',
            '--brand-muted'   => '#565656',
        ],
        'maroon' => [
            '--brand-primary' => '#8f3b48', // classic burgundy title bar
            '--brand-accent'  => '#6f2c37',
            '--brand-bg'      => '#dad6d6',
            '--brand-surface' => '#eeeaea',
            '--brand-border'  => '#a49b9b',
            '--brand-fg'      => '#241a1b',
            '--brand-muted'   => '#6b5a5c',
        ],
        // retro — warm, saturated 70s vintage tones: burnt mustard,
        // dusty brick red, faded turquoise, avocado olive. On cream stock.
        'amber' => [
            '--brand-primary' => '#c2691c', // burnt mustard/orange
            '--brand-accent'  => '#99500f',
            '--brand-bg'      => '#fbf3e2', // warm cream
            '--brand-surface' => '#fffdf5',
            '--brand-border'  => '#ecd9b6',
            '--brand-fg'      => '#3d2708',
            '--brand-muted'   => '#8f6e3a',
        ],
        'rose' => [
            '--brand-primary' => '#b23b46', // dusty brick red
            '--brand-accent'  => '#8c2b35',
            '--brand-bg'      => '#faf0ec',
            '--brand-surface' => '#fffbf9',
            '--brand-border'  => '#eed2ca',
            '--brand-fg'      => '#3f1418',
            '--brand-muted'   => '#8f5a55',
        ],
        'teal' => [
            '--brand-primary' => '#2a8079', // faded turquoise
            '--brand-accent'  => '#1e615b',
            '--brand-bg'      => '#eef5f2',
            '--brand-surface' => '#f9fdfb',
            '--brand-border'  => '#cde2dc',
            '--brand-fg'      => '#123531',
            '--brand-muted'   => '#578079',
        ],
        'forest' => [
            '--brand-primary' => '#6b7d2e', // avocado olive
            '--brand-accent'  => '#526022',
            '--brand-bg'      => '#f4f4e6',
            '--brand-surface' => '#fbfcf3',
            '--brand-border'  => '#dee2c2',
            '--brand-fg'      => '#2d3512',
            '--brand-muted'   => '#6f7a48',
        ],
        'mustard' => [
            '--brand-primary' => '#b8931f', // golden mustard
            '--brand-accent'  => '#927214',
            '--brand-bg'      => '#faf5e3',
            '--brand-surface' => '#fffdf4',
            '--brand-border'  => '#e9ddb8',
            '--brand-fg'      => '#3a2f08',
            '--brand-muted'   => '#897a3c',
        ],
        'mocha' => [
            '--brand-primary' => '#6f4a2f', // vintage coffee brown
            '--brand-accent'  => '#543722',
            '--brand-bg'      => '#f4eee6',
            '--brand-surface' => '#fdfaf5',
            '--brand-border'  => '#e0d3c2',
            '--brand-fg'      => '#2e1e10',
            '--brand-muted'   => '#7d6753',
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
