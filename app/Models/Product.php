<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commerce\CurrencyEnum;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'description',
    'material',
    'is_preorder',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_preorder' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Products visible on the public storefront for a given currency.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopePublishedForStorefront(Builder $query, CurrencyEnum $currency): Builder
    {
        return $query
            ->active()
            ->whereHas('variants', function (Builder $variants) use ($currency): void {
                $variants
                    ->where('is_active', true)
                    ->whereHas('prices', function (Builder $prices) use ($currency): void {
                        $prices->where('currency', $currency->value);
                    });
            });
    }

    public function primaryImage(): ?ProductImage
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true)
                ?? $this->images->sortBy('sort_order')->first();
        }

        return $this->images()
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Whether all active variants are out of stock.
     * Preorder products are never considered out of stock.
     */
    public function isOutOfStock(): bool
    {
        if ($this->is_preorder) {
            return false;
        }

        return $this->variants
            ->filter(fn (ProductVariant $v): bool => $v->is_active)
            ->every(fn (ProductVariant $v): bool => $v->stock <= 0);
    }

    /**
     * Unique color strings from active variants.
     *
     * @return Collection<int, string>
     */
    public function availableColors(): Collection
    {
        return $this->variants
            ->filter(fn (ProductVariant $v): bool => $v->is_active && $v->color !== null)
            ->pluck('color')
            ->unique()
            ->values();
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
