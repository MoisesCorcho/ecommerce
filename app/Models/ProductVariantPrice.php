<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commerce\CurrencyEnum;
use Database\Factories\ProductVariantPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_variant_id',
    'currency',
    'price',
    'compare_at_price',
])]
class ProductVariantPrice extends Model
{
    /** @use HasFactory<ProductVariantPriceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => CurrencyEnum::class,
            'price' => 'integer',
            'compare_at_price' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Whether this price represents a promotional discount.
     */
    public function hasDiscount(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    /**
     * Calculate discount percentage relative to compare_at_price.
     */
    public function discountPercentage(): ?int
    {
        if (! $this->hasDiscount() || $this->compare_at_price === 0) {
            return null;
        }

        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }
}
