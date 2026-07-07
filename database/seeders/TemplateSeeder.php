<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Six published cashier templates, two per visual theme (classic / modern /
     * retro). Each template pairs a structural layout with a theme palette drawn
     * from config/branding.php, so the theme+palette combinations stay valid.
     *
     * Slugs are stable and are used as the firstOrCreate key, so existing tenants
     * that reference a template_id keep working across re-seeds.
     */
    public function run(): void
    {
        $templates = [
            // ---- Retro ---------------------------------------------------------
            [
                'name'           => 'Kedai Kopi',
                'slug'           => 'kedai-kopi',
                'description'    => 'Cocok untuk kedai kopi dan minuman. Tampilan hangat dan nyaman.',
                'default_config' => ['layout' => 'topbar', 'theme' => 'retro', 'color_palette' => 'amber'],
                'is_published'   => true,
            ],
            [
                'name'           => 'Restoran',
                'slug'           => 'restoran',
                'description'    => 'Desain elegan untuk warung makan dan restoran.',
                'default_config' => ['layout' => 'bottombar', 'theme' => 'retro', 'color_palette' => 'rose'],
                'is_published'   => true,
            ],

            // ---- Classic -------------------------------------------------------
            [
                'name'           => 'Toko Retail',
                'slug'           => 'toko-retail',
                'description'    => 'Tampilan bersih dan profesional untuk toko ritel umum.',
                'default_config' => ['layout' => 'sidebar', 'theme' => 'classic', 'color_palette' => 'indigo'],
                'is_published'   => true,
            ],
            [
                'name'           => 'Apotek Sehat',
                'slug'           => 'apotek-sehat',
                'description'    => 'Rapi dan terpercaya untuk apotek dan toko kesehatan.',
                'default_config' => ['layout' => 'topbar', 'theme' => 'classic', 'color_palette' => 'emerald'],
                'is_published'   => true,
            ],

            // ---- Modern --------------------------------------------------------
            [
                'name'           => 'Butik Fashion',
                'slug'           => 'butik-fashion',
                'description'    => 'Gaya modern dan stylish untuk butik dan toko pakaian.',
                'default_config' => ['layout' => 'topbar', 'theme' => 'modern', 'color_palette' => 'violet'],
                'is_published'   => true,
            ],
            [
                'name'           => 'Toko Gadget',
                'slug'           => 'toko-gadget',
                'description'    => 'Tampilan segar dan teknologi untuk toko gadget dan elektronik.',
                'default_config' => ['layout' => 'sidebar', 'theme' => 'modern', 'color_palette' => 'sky'],
                'is_published'   => true,
            ],
        ];

        foreach ($templates as $data) {
            Template::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
