<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Announcement;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @var class-string<Announcement>
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => [
                'es' => fake('es_ES')->sentence(6),
                'en' => fake('en_US')->sentence(6),
            ],
            'url' => fake()->optional(0.5)->url(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Indicate that the announcement is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the announcement has scheduled start and end dates.
     */
    public function scheduled(?CarbonInterface $startsAt = null, ?CarbonInterface $endsAt = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => $startsAt ?? now()->subDay(),
            'ends_at' => $endsAt ?? now()->addDays(7),
        ]);
    }
}
