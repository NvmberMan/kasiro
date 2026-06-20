<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed layouts (maps to Blade layout files under tenant/layouts/)
    |--------------------------------------------------------------------------
    */

    'layouts' => ['modern', 'classic', 'retro'],

    /*
    |--------------------------------------------------------------------------
    | Allowed themes (light/dark mode variant)
    |--------------------------------------------------------------------------
    */

    'themes' => ['light', 'dark', 'warm'],

    /*
    |--------------------------------------------------------------------------
    | Color palettes → CSS custom property values
    |--------------------------------------------------------------------------
    |
    | Keys are stored in theme_config.color_palette. Values are injected as
    | CSS custom properties in the tenant layout <head> via the brand-styles
    | Blade component. Add new palettes here without touching PHP logic.
    |
    */

    'palettes' => [
        'default' => [
            '--brand-primary' => '#6366f1',
            '--brand-accent'  => '#8b5cf6',
            '--brand-bg'      => '#ffffff',
            '--brand-fg'      => '#111827',
        ],
        'emerald' => [
            '--brand-primary' => '#10b981',
            '--brand-accent'  => '#059669',
            '--brand-bg'      => '#f0fdf4',
            '--brand-fg'      => '#064e3b',
        ],
        'indigo' => [
            '--brand-primary' => '#4f46e5',
            '--brand-accent'  => '#7c3aed',
            '--brand-bg'      => '#eef2ff',
            '--brand-fg'      => '#1e1b4b',
        ],
        'rose' => [
            '--brand-primary' => '#f43f5e',
            '--brand-accent'  => '#e11d48',
            '--brand-bg'      => '#fff1f2',
            '--brand-fg'      => '#4c0519',
        ],
        'amber' => [
            '--brand-primary' => '#f59e0b',
            '--brand-accent'  => '#d97706',
            '--brand-bg'      => '#fffbeb',
            '--brand-fg'      => '#451a03',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default values for the custom create flow
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'layout'        => 'modern',
        'theme'         => 'light',
        'color_palette' => 'default',
    ],

];
