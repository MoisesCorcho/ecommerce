<?php

declare(strict_types=1);

namespace App\Actions\Cart\Concerns;

use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\ProductVariant;

trait AssertsCartItemEligibility
{
    public const int MAX_LINE_QUANTITY = 99;

    /**
     * @throws CartItemNotEligibleException
     */
    protected function assertEligible(ProductVariant $variant, CurrencyEnum $currency): void
    {
        if (! $variant->is_active) {
            throw CartItemNotEligibleException::make();
        }

        $product = $variant->relationLoaded('product')
            ? $variant->product
            : $variant->product()->first();

        if ($product === null || ! $product->is_active) {
            throw CartItemNotEligibleException::make();
        }

        if ($variant->priceIn($currency) === null) {
            throw CartItemNotEligibleException::make();
        }
    }

    /**
     * @throws CartQuantityNotAllowedException
     * @throws InsufficientCartStockException
     */
    protected function assertQuantityAllowed(int $quantity, int $stock): void
    {
        if ($quantity < 1) {
            throw CartQuantityNotAllowedException::invalid();
        }

        if ($quantity > self::MAX_LINE_QUANTITY) {
            throw CartQuantityNotAllowedException::exceedsMax(self::MAX_LINE_QUANTITY);
        }

        if ($quantity > $stock) {
            throw InsufficientCartStockException::make(max(0, $stock));
        }
    }

    protected function maxAllowedQuantity(int $stock): int
    {
        return min(max(0, $stock), self::MAX_LINE_QUANTITY);
    }
}
