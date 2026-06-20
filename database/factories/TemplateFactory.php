<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $layouts  = config('branding.layouts');
        $themes   = config('branding.themes');
        $palettes = array_keys(config('branding.palettes'));

        $name = fake()->unique()->words(2, true);

        return [
            'name'           => ucwords($name),
            'slug'           => Str::slug($name).'-'.Str::random(4),
            'description'    => fake()->sentence(),
            'preview_image'  => null,
            'default_config' => [
                'layout'        => $layouts[array_rand($layouts)],
                'theme'         => $themes[array_rand($themes)],
                'color_palette' => $palettes[array_rand($palettes)],
            ],
            'is_published'   => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
