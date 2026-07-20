<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currency = CurrencyEnum::Cop;
        $subtotal = fake()->numberBetween(200_000, 1_500_000);
        $shipping = fake()->numberBetween(0, 30_000);
        $discount = 0;
        $tax = 0;
        $total = $subtotal + $shipping - $discount + $tax;

        return [
            'order_number' => 'LHB-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'coupon_id' => null,
            'status' => OrderStatusEnum::Pending,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'discount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
            'shipping_address_id' => null,
            'shipping_full_name' => fake()->name(),
            'shipping_phone' => fake()->e164PhoneNumber(),
            'shipping_address_line_1' => fake()->streetAddress(),
            'shipping_address_line_2' => null,
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->state(),
            'shipping_country' => 'CO',
            'shipping_postal_code' => fake()->optional()->postcode(),
            'tracking_number' => null,
            'customer_notes' => fake()->optional()->sentence(),
            'paid_at' => null,
            'shipped_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::Paid,
            'paid_at' => now(),
        ]);
    }

    public function eur(): static
    {
        return $this->state(function (array $attributes) {
            $subtotal = fake()->numberBetween(5_000, 25_000);
            $shipping = fake()->numberBetween(0, 1_500);

            return [
                'currency' => CurrencyEnum::Eur,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'discount' => 0,
                'tax_amount' => 0,
                'total' => $subtotal + $shipping,
                'shipping_country' => 'ES',
            ];
        });
    }
}
