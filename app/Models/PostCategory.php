<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'name',
    'slug',
    'description',
    'sort_order',
    'is_active',
])]
class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = [
        'name',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Scope query to only include active categories.
     *
     * @param  Builder<PostCategory>  $query
     * @return Builder<PostCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to order categories by priority ascending and then newest ID.
     *
     * @param  Builder<PostCategory>  $query
     * @return Builder<PostCategory>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order', 'asc')
            ->orderByDesc('id');
    }

    /**
     * Get the translated category name for the given locale with fallback to default locale (es).
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('name', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        return $this->getTranslation('name', 'es', false) ?: '';
    }
}
