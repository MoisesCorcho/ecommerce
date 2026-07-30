<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commerce\CurrencyEnum;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'sku',
    'color',
    'size',
    'stock',
    'is_active',
])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ProductVariant>  $query
     * @return Builder<ProductVariant>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<ProductVariant>  $query
     * @return Builder<ProductVariant>
     */
    public function scopeWithPriceIn(Builder $query, CurrencyEnum $currency): Builder
    {
        return $query->whereHas('prices', function (Builder $prices) use ($currency): void {
            $prices->where('currency', $currency->value);
        });
    }

    public function priceIn(CurrencyEnum $currency): ?ProductVariantPrice
    {
        if ($this->relationLoaded('prices')) {
            return $this->prices->first(
                fn (ProductVariantPrice $price): bool => $price->currency === $currency
            );
        }

        return $this->prices()->where('currency', $currency->value)->first();
    }

    /**
     * Whether this specific variant is out of stock.
     * Preorder products never mark their variants as out of stock.
     */
    public function isOutOfStock(): bool
    {
        return ! $this->product->is_preorder && $this->stock <= 0;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<ProductVariantPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
