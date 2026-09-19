<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantPrice>
 */
class ProductVariantPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currency = fake()->randomElement(CurrencyEnum::cases());
        $price = $currency === CurrencyEnum::Cop
            ? fake()->numberBetween(150_000, 2_500_000)
            : fake()->numberBetween(4_900, 25_000);

        return [
            'product_variant_id' => ProductVariant::factory(),
            'currency' => $currency,
            'price' => $price,
            'compare_at_price' => fake()->optional(0.3)->numberBetween($price, (int) ($price * 1.4)),
        ];
    }

    public function cop(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => CurrencyEnum::Cop,
            'price' => fake()->numberBetween(150_000, 2_500_000),
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => CurrencyEnum::Eur,
            'price' => fake()->numberBetween(4_900, 25_000),
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => CurrencyEnum::Usd,
            'price' => fake()->numberBetween(4_900, 25_000),
        ]);
    }
}
