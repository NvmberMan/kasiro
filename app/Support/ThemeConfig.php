<?php

namespace App\Support;

use App\Models\Template;

class ThemeConfig
{
    /**
     * Build a theme_config array from raw user input.
     * - layout: validated against layout keys
     * - theme: validated against theme keys
     * - color_palette: validated against the selected theme's allowed palette list;
     *   falls back to the theme's first palette if the submitted value is absent or invalid.
     */
    public static function fromCustomInput(array $input): array
    {
        $defaults = config('branding.defaults');
        $layouts  = config('branding.layouts');
        $themes   = config('branding.themes');

        $layout = array_key_exists($input['layout'] ?? null, $layouts)
            ? $input['layout']
            : $defaults['layout'];

        $theme = array_key_exists($input['theme'] ?? null, $themes)
            ? $input['theme']
            : $defaults['theme'];

        $allowed = $themes[$theme]['palettes'] ?? [];
        $palette = in_array($input['color_palette'] ?? null, $allowed, true)
            ? $input['color_palette']
            : ($allowed[0] ?? $defaults['color_palette']);

        return [
            'layout'        => $layout,
            'theme'         => $theme,
            'color_palette' => $palette,
        ];
    }

    /**
     * Snapshot a template's default_config into a validated theme_config array.
     */
    public static function fromTemplate(Template $template): array
    {
        return self::fromCustomInput($template->default_config ?? []);
    }

    /**
     * Resolve theme_config to CSS custom-property declarations.
     * Merges theme typography vars (font, radius) with palette color vars.
     */
    public static function cssVariables(array $themeConfig): array
    {
        $themeKey   = $themeConfig['theme'] ?? config('branding.defaults.theme');
        $paletteKey = $themeConfig['color_palette'] ?? null;
        $themes     = config('branding.themes');
        $palettes   = config('branding.palettes');

        $themeVars   = $themes[$themeKey]['vars'] ?? [];
        $fallback    = $themes[$themeKey]['palettes'][0] ?? config('branding.defaults.color_palette');
        $paletteVars = $palettes[$paletteKey] ?? $palettes[$fallback] ?? $palettes[array_key_first($palettes)];

        return array_merge($themeVars, $paletteVars);
    }
}
