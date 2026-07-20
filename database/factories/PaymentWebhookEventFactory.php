<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentProviderEnum;
use App\Models\PaymentWebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentWebhookEvent>
 */
class PaymentWebhookEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(PaymentProviderEnum::cases());

        return [
            'provider' => $provider,
            'event_id' => $provider === PaymentProviderEnum::Stripe
                ? 'evt_'.fake()->unique()->bothify('##############')
                : fake()->unique()->uuid(),
            'event_type' => fake()->randomElement([
                'payment_intent.succeeded',
                'payment_intent.payment_failed',
                'charge.refunded',
                'sale.approved',
            ]),
            'payload' => ['id' => fake()->uuid(), 'type' => 'test'],
            'processed_at' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processed_at' => now(),
        ]);
    }
}
