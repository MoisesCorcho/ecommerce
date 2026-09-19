<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostCategory>
 */
class PostCategoryFactory extends Factory
{
    /**
     * @var class-string<PostCategory>
     */
    protected $model = PostCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameEs = fake('es_ES')->unique()->words(2, true);
        $nameEn = fake('en_US')->unique()->words(2, true);

        return [
            'name' => [
                'es' => ucfirst($nameEs),
                'en' => ucfirst($nameEn),
            ],
            'slug' => Str::slug($nameEs).'-'.fake()->unique()->randomNumber(4),
            'description' => [
                'es' => fake('es_ES')->sentence(),
                'en' => fake('en_US')->sentence(),
            ],
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
