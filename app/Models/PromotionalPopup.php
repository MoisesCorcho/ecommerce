<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PromotionalPopupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'title',
    'subtitle',
    'image_path',
    'coupon_id',
    'cta_text',
    'cta_url',
    'delay_seconds',
    'is_active',
    'sort_order',
    'starts_at',
    'ends_at',
])]
class PromotionalPopup extends Model
{
    /** @use HasFactory<PromotionalPopupFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = [
        'title',
        'subtitle',
        'cta_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'delay_seconds' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Scope query to only include currently active and scheduled popups.
     *
     * @param  Builder<PromotionalPopup>  $query
     * @return Builder<PromotionalPopup>
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
     * Scope query to order popups by priority ascending and then newest ID.
     *
     * @param  Builder<PromotionalPopup>  $query
     * @return Builder<PromotionalPopup>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order', 'asc')
            ->orderByDesc('id');
    }

    /**
     * Get the translated popup title for the given locale with fallback to default locale (es).
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
     * Get the translated popup subtitle for the given locale with fallback to default locale (es).
     */
    public function getLocalizedSubtitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('subtitle', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        $fallback = $this->getTranslation('subtitle', 'es', false);

        return ! empty($fallback) ? $fallback : null;
    }

    /**
     * Get the translated popup CTA text for the given locale with fallback to default locale (es).
     */
    public function getLocalizedCtaText(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = $this->getTranslation('cta_text', $locale, false);

        if (! empty($translated)) {
            return $translated;
        }

        $fallback = $this->getTranslation('cta_text', 'es', false);

        return ! empty($fallback) ? $fallback : null;
    }

    /**
     * Check whether the popup has a valid and currently usable coupon attached.
     */
    public function hasValidCoupon(): bool
    {
        if ($this->coupon === null) {
            return false;
        }

        if (! $this->coupon->is_active) {
            return false;
        }

        if ($this->coupon->starts_at !== null && $this->coupon->starts_at > now()) {
            return false;
        }

        if ($this->coupon->expires_at !== null && $this->coupon->expires_at < now()) {
            return false;
        }

        if ($this->coupon->usage_limit !== null && $this->coupon->used_count >= $this->coupon->usage_limit) {
            return false;
        }

        return true;
    }
}
