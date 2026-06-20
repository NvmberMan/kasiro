<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // old layout keys → new theme keys (they were the same concept)
    private const THEME_MAP = [
        'modern'  => 'modern',
        'classic' => 'classic',
        'retro'   => 'retro',
    ];

    // old palette keys → new palette keys ('default' is renamed to 'violet')
    private const PALETTE_MAP = [
        'default' => 'violet',
        'emerald' => 'emerald',
        'indigo'  => 'indigo',
        'rose'    => 'rose',
        'amber'   => 'amber',
    ];

    // valid palettes per new theme
    private const THEME_PALETTES = [
        'modern'  => ['violet', 'sky', 'orange'],
        'classic' => ['slate', 'indigo', 'emerald'],
        'retro'   => ['amber', 'rose', 'teal', 'forest'],
    ];

    public function up(): void
    {
        DB::table('tenants')->whereNotNull('theme_config')->get()
            ->each(function (object $row): void {
                $config = json_decode($row->theme_config, true);
                if (! is_array($config)) {
                    return;
                }

                // old $.layout (modern/classic/retro) becomes new $.theme
                $newTheme = self::THEME_MAP[$config['layout'] ?? 'modern'] ?? 'modern';

                // remap old palette name; fall back to theme's first valid palette
                $mapped     = self::PALETTE_MAP[$config['color_palette'] ?? 'default'] ?? 'violet';
                $allowed    = self::THEME_PALETTES[$newTheme];
                $newPalette = in_array($mapped, $allowed, true) ? $mapped : $allowed[0];

                DB::table('tenants')->where('id', $row->id)->update([
                    'theme_config' => json_encode([
                        'layout'        => 'topbar',
                        'theme'         => $newTheme,
                        'color_palette' => $newPalette,
                    ]),
                ]);
            });
    }

    public function down(): void
    {
        // Irreversible data migration — snapshot before running in production.
    }
};
