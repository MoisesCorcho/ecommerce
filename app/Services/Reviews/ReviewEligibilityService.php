<?php

declare(strict_types=1);

namespace App\Services\Reviews;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

/**
 * Purchase eligibility for product reviews (D8).
 */
class ReviewEligibilityService
{
    /**
     * @var list<OrderStatusEnum>
     */
    private const ELIGIBLE_STATUSES = [
        OrderStatusEnum::Paid,
        OrderStatusEnum::Processing,
        OrderStatusEnum::Shipped,
        OrderStatusEnum::Delivered,
    ];

    public function hasEligiblePurchase(User $user, Product $product): bool
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', self::ELIGIBLE_STATUSES)
            ->whereHas(
                'items.productVariant',
                fn ($query) => $query->where('product_id', $product->id),
            )
            ->exists();
    }

    public function isVerifiedPurchase(User $user, Product $product): bool
    {
        return $this->hasEligiblePurchase($user, $product);
    }

    public function canCreateReview(User $user, Product $product): bool
    {
        if (! $this->hasEligiblePurchase($user, $product)) {
            return false;
        }

        return ! Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }
}
