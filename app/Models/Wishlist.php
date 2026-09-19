<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commerce\CurrencyEnum;
use Database\Factories\WishlistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_variant_id',
    'price_when_added',
    'currency_when_added',
])]
class Wishlist extends Model
{
    /** @use HasFactory<WishlistFactory> */
    use HasFactory;

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'price_when_added' => 'integer',
            'currency_when_added' => CurrencyEnum::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
