<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\PaymentProviderEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => PaymentProviderEnum::Bold,
            'currency' => CurrencyEnum::Cop,
            'external_id' => fake()->optional()->uuid(),
            'payment_method' => fake()->optional()->randomElement(['card', 'pse', 'nequi']),
            'status' => PaymentStatusEnum::Pending,
            'amount' => fake()->numberBetween(100_000, 1_500_000),
            'raw_response' => null,
            'paid_at' => null,
            'refunded_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatusEnum::Approved,
            'paid_at' => now(),
            'external_id' => fake()->uuid(),
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => PaymentProviderEnum::Stripe,
            'currency' => CurrencyEnum::Eur,
            'payment_method' => 'card',
            'amount' => fake()->numberBetween(5_000, 25_000),
        ]);
    }
}
