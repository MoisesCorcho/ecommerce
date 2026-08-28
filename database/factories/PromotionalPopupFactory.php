<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\PromotionalPopup;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionalPopup>
 */
class PromotionalPopupFactory extends Factory
{
    /**
     * @var class-string<PromotionalPopup>
     */
    protected $model = PromotionalPopup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'es' => fake('es_ES')->sentence(4),
                'en' => fake('en_US')->sentence(4),
            ],
            'subtitle' => [
                'es' => fake('es_ES')->sentence(6),
                'en' => fake('en_US')->sentence(6),
            ],
            'image_path' => null,
            'coupon_id' => null,
            'cta_text' => [
                'es' => 'Ver Oferta',
                'en' => 'View Offer',
            ],
            'cta_url' => fake()->optional(0.7)->url(),
            'delay_seconds' => 5,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Indicate that the promotional popup is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the promotional popup has scheduled start and end dates.
     */
    public function scheduled(?CarbonInterface $startsAt = null, ?CarbonInterface $endsAt = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => $startsAt ?? now()->subDay(),
            'ends_at' => $endsAt ?? now()->addDays(7),
        ]);
    }

    /**
     * Associate a coupon to the promotional popup.
     */
    public function withCoupon(?Coupon $coupon = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'coupon_id' => $coupon?->id ?? Coupon::factory(),
        ]);
    }
}
