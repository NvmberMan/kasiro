<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'           => 'Kedai Kopi',
                'slug'           => 'kedai-kopi',
                'description'    => 'Cocok untuk kedai kopi dan minuman. Tampilan hangat dan nyaman.',
                'default_config' => ['layout' => 'modern', 'theme' => 'warm', 'color_palette' => 'amber'],
                'is_published'   => true,
            ],
            [
                'name'           => 'Toko Retail',
                'slug'           => 'toko-retail',
                'description'    => 'Tampilan bersih dan profesional untuk toko ritel umum.',
                'default_config' => ['layout' => 'classic', 'theme' => 'light', 'color_palette' => 'indigo'],
                'is_published'   => true,
            ],
            [
                'name'           => 'Restoran',
                'slug'           => 'restoran',
                'description'    => 'Desain elegan untuk warung makan dan restoran.',
                'default_config' => ['layout' => 'retro', 'theme' => 'dark', 'color_palette' => 'rose'],
                'is_published'   => true,
            ],
        ];

        foreach ($templates as $data) {
            Template::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
