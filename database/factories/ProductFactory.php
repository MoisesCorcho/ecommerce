<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'material' => fake()->optional()->randomElement(['Cuero', 'Gamuza', 'Sintético', 'Lona']),
            'dimensions' => fake()->optional()->randomElement(['36cm x 29cm x 8cm', '28cm x 22cm x 10cm', '42cm x 30cm x 12cm']),
            'is_preorder' => false,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function preorder(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preorder' => true,
        ]);
    }
}
