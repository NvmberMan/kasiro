<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'owner_id' => null,
            'name' => $name,
            'subdomain' => Str::lower(Str::slug($name).Str::random(4)),
            'logo_path' => null,
            'status' => Tenant::STATUS_ACTIVE,
            'template_id' => null,
            'theme_config' => [
                'layout' => 'modern',
                'theme' => 'light',
                'color_palette' => 'default',
            ],
            'archived_at' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => Tenant::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    public function subdomain(string $subdomain): static
    {
        return $this->state(fn () => ['subdomain' => $subdomain]);
    }
}
