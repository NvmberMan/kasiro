<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => null,
            'category_id' => null,
            'name'        => fake()->unique()->words(3, true),
            'sku'         => fake()->optional()->bothify('SKU-###??'),
            'price'       => fake()->numberBetween(5000, 200000),
            'stock'       => fake()->numberBetween(0, 100),
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
