<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('LHB-###-???')),
            'color_id' => null,
            'size' => null,
            'dimensions' => null,
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    public function withColor(?Color $color = null): static
    {
        return $this->state(fn (array $attributes) => [
            'color_id' => $color?->id ?? Color::factory(),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
