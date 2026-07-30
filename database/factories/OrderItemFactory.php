<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'product_name' => fake()->words(3, true),
            'variant_label' => fake()->optional()->safeColorName(),
            'sku' => strtoupper(fake()->bothify('LHB-###-???')),
            'unit_price' => fake()->numberBetween(50_000, 800_000),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
