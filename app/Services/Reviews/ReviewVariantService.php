<?php

declare(strict_types=1);

namespace App\Services\Reviews;

use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Products\SizeEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

/**
 * Resolves purchased variants for a user+product pair.
 */
class ReviewVariantService
{
    private const MAX_VARIANTS = 3;

    /**
     * Get the most recent purchased variants for a user+product, up to 3.
     *
     * @return list<array{sku: string, color: ?string, size: ?string}>
     */
    public function getRecentPurchasedVariants(User $user, Product $product): array
    {
        $variants = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', $this->eligibleStatuses())
            ->whereHas(
                'items.productVariant',
                fn ($query) => $query->where('product_id', $product->id),
            )
            ->with([
                'items.productVariant' => fn ($q) => $q->where('product_id', $product->id),
            ])
            ->orderByDesc('created_at')
            ->get()
            ->flatMap(fn (Order $order) => $order->items)
            ->filter(fn ($item) => $item->productVariant?->product_id === $product->id)
            ->map(fn ($item) => [
                'sku' => $item->sku ?? $item->productVariant?->sku ?? '',
                'color' => $item->productVariant?->color,
                'size' => $item->productVariant?->size instanceof SizeEnum ? $item->productVariant->size->value : $item->productVariant?->size,
            ])
            ->unique(fn (array $v): string => $v['sku'])
            ->values()
            ->take(self::MAX_VARIANTS)
            ->all();

        return $variants;
    }

    /**
     * Check if a user has purchased new variants since the review was last updated.
     */
    public function hasNewVariantsSinceReview(User $user, Product $product, ?string $lastReviewedSku = null): bool
    {
        $allPurchasedSkus = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', $this->eligibleStatuses())
            ->whereHas(
                'items.productVariant',
                fn ($query) => $query->where('product_id', $product->id),
            )
            ->with(['items.productVariant' => fn ($q) => $q->where('product_id', $product->id)])
            ->get()
            ->flatMap(fn (Order $order) => $order->items)
            ->filter(fn ($item) => $item->productVariant?->product_id === $product->id)
            ->pluck('sku')
            ->unique()
            ->values()
            ->all();

        if (empty($allPurchasedSkus)) {
            return false;
        }

        if ($lastReviewedSku === null) {
            return count($allPurchasedSkus) > 1;
        }

        return ! in_array($lastReviewedSku, $allPurchasedSkus, true)
            || count($allPurchasedSkus) > 1;
    }

    /**
     * @return list<OrderStatusEnum>
     */
    private function eligibleStatuses(): array
    {
        return array_values(array_filter(
            OrderStatusEnum::cases(),
            fn (OrderStatusEnum $status): bool => $status->isEligibleForReview(),
        ));
    }
}
