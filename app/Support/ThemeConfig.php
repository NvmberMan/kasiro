<?php

namespace App\Support;

use App\Models\Template;

class ThemeConfig
{
    /**
     * Build a theme_config array from raw custom-flow user input.
     * Unknown/missing values fall back to config defaults.
     */
    public static function fromCustomInput(array $input): array
    {
        $defaults = config('branding.defaults');

        $layout  = in_array($input['layout'] ?? null, config('branding.layouts'), true)
            ? $input['layout']
            : $defaults['layout'];

        $theme   = in_array($input['theme'] ?? null, config('branding.themes'), true)
            ? $input['theme']
            : $defaults['theme'];

        $palette = array_key_exists($input['color_palette'] ?? null, config('branding.palettes'))
            ? $input['color_palette']
            : $defaults['color_palette'];

        return [
            'layout'        => $layout,
            'theme'         => $theme,
            'color_palette' => $palette,
        ];
    }

    /**
     * Snapshot a template's default_config into a theme_config array.
     * Unknown keys in the snapshot fall back to branding defaults so
     * stale template data never produces an invalid config.
     */
    public static function fromTemplate(Template $template): array
    {
        return self::fromCustomInput($template->default_config ?? []);
    }

    /**
     * Resolve a theme_config array to CSS custom-property declarations.
     * Returns an associative array of property-name => value strings.
     * Falls back to the 'default' palette if the stored palette is missing.
     */
    public static function cssVariables(array $themeConfig): array
    {
        $palette = $themeConfig['color_palette'] ?? 'default';
        $palettes = config('branding.palettes');

        return $palettes[$palette] ?? $palettes['default'];
    }
}
