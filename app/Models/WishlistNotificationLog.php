<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Wishlist\WishlistNotificationTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_variant_id',
    'notification_type',
    'sent_at',
])]
class WishlistNotificationLog extends Model
{
    use HasFactory;

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'notification_type' => WishlistNotificationTypeEnum::class,
            'sent_at' => 'datetime',
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
