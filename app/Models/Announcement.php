<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'text',
    'url',
    'is_active',
    'sort_order',
    'starts_at',
    'ends_at',
])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = ['text'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Scope query to only include currently active and scheduled announcements.
     *
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope query to order announcements by priority ascending and then newest ID.
     *
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order', 'asc')
            ->orderByDesc('id');
    }

    /**
     * Get the translated announcement text for the given locale with fallback to default locale (es).
     */
    public function getLocalizedText(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('text', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        return $this->getTranslation('text', 'es', false) ?: '';
    }
}
