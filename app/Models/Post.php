<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Blog\PostStatusEnum;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'post_category_id',
    'author_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'cover_image_path',
    'meta_title',
    'meta_description',
    'status',
    'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasTranslations;

    protected static function booted(): void
    {
        static::deleting(static function (Post $post): void {
            if ($post->cover_image_path && Storage::disk('public')->exists($post->cover_image_path)) {
                Storage::disk('public')->delete($post->cover_image_path);
            }
        });
    }

    /**
     * @var array<int, string>
     */
    public array $translatable = [
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatusEnum::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope query to only include published posts whose publication date has passed.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PostStatusEnum::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Get the translated title with fallback to default locale (es).
     */
    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('title', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        return $this->getTranslation('title', 'es', false) ?: '';
    }

    /**
     * Get the translated excerpt with fallback to default locale (es).
     */
    public function getLocalizedExcerpt(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('excerpt', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        return $this->getTranslation('excerpt', 'es', false) ?: '';
    }

    /**
     * Get the translated content with fallback to default locale (es).
     */
    public function getLocalizedContent(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('content', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        return $this->getTranslation('content', 'es', false) ?: '';
    }

    /**
     * Get the translated meta title with fallback to localized post title + brand.
     */
    public function getLocalizedMetaTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('meta_title', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        $fallbackEs = $this->getTranslation('meta_title', 'es', false);
        if (! empty($fallbackEs)) {
            return $fallbackEs;
        }

        $title = $this->getLocalizedTitle($locale);

        return $title !== '' ? "{$title} | Leen" : 'Leen';
    }

    /**
     * Get the translated meta description with fallback to localized excerpt.
     */
    public function getLocalizedMetaDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('meta_description', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        $fallbackEs = $this->getTranslation('meta_description', 'es', false);
        if (! empty($fallbackEs)) {
            return $fallbackEs;
        }

        return $this->getLocalizedExcerpt($locale);
    }

    /**
     * Calculate estimated reading time in minutes (based on 200 words/min).
     */
    public function readingTime(?string $locale = null): int
    {
        $content = strip_tags($this->getLocalizedContent($locale));
        $wordCount = str_word_count($content);

        return max(1, (int) ceil($wordCount / 200));
    }
}
