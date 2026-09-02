<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titleEs = fake('es_ES')->sentence(5);
        $titleEn = fake('en_US')->sentence(5);

        return [
            'post_category_id' => PostCategory::factory(),
            'author_id' => User::factory(),
            'title' => [
                'es' => $titleEs,
                'en' => $titleEn,
            ],
            'slug' => Str::slug($titleEs).'-'.fake()->unique()->randomNumber(4),
            'excerpt' => [
                'es' => fake('es_ES')->paragraph(2),
                'en' => fake('en_US')->paragraph(2),
            ],
            'content' => [
                'es' => '<p>'.implode('</p><p>', fake('es_ES')->paragraphs(4)).'</p>',
                'en' => '<p>'.implode('</p><p>', fake('en_US')->paragraphs(4)).'</p>',
            ],
            'cover_image_path' => null,
            'meta_title' => [
                'es' => $titleEs.' | Leen',
                'en' => $titleEn.' | Leen',
            ],
            'meta_description' => [
                'es' => fake('es_ES')->sentence(),
                'en' => fake('en_US')->sentence(),
            ],
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatusEnum::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is scheduled for a future date.
     */
    public function scheduled(?CarbonInterface $publishedAt = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatusEnum::Published,
            'published_at' => $publishedAt ?? now()->addDays(3),
        ]);
    }
}
