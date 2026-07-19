<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CouponTypeEnum;
use App\Enums\CurrencyEnum;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'type' => CouponTypeEnum::Percentage,
            'value' => fake()->numberBetween(5, 30),
            'currency' => null,
            'min_order_amount' => null,
            'min_order_currency' => null,
            'usage_limit' => fake()->optional()->numberBetween(10, 500),
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function fixed(CurrencyEnum $currency = CurrencyEnum::Cop): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponTypeEnum::Fixed,
            'value' => $currency === CurrencyEnum::Cop
                ? fake()->numberBetween(20_000, 100_000)
                : fake()->numberBetween(500, 2_000),
            'currency' => $currency,
        ]);
    }

    public function percentage(int $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponTypeEnum::Percentage,
            'value' => $value,
            'currency' => null,
        ]);
    }
}
