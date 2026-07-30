<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CouponRedemption>
 */
class CouponRedemptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'code' => strtoupper(fake()->bothify('SAVE##??')),
            'discount_amount' => fake()->numberBetween(10_000, 80_000),
            'currency' => CurrencyEnum::Cop,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
