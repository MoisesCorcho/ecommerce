<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\Actions\Orders\Concerns\ValidatesCartLinesForCheckout;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Orders\CheckoutPreviewDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Exceptions\Orders\OrderAccessDeniedException;
use App\Models\Cart;
use App\Services\Coupons\CouponPricingService;
use App\Services\Orders\ShippingCostService;

class ValidateCartForCheckoutAction
{
    use AssertsCartOwnership;
    use ValidatesCartLinesForCheckout;

    public function __construct(
        private readonly ShippingCostService $shippingCostService,
        private readonly CouponPricingService $couponPricingService,
    ) {}

    /**
     * Preview checkout totals. Optional couponCode is quoted without consuming usage (R3).
     *
     * @throws OrderAccessDeniedException
     * @throws CheckoutCartEmptyException
     * @throws CheckoutCartNotReadyException
     * @throws InvalidCouponException
     */
    public function __invoke(int $cartId, CartOwnerDTO $owner, ?string $couponCode = null): CheckoutPreviewDTO
    {
        /** @var Cart $cart */
        $cart = Cart::query()->with(['items.productVariant.product', 'items.productVariant.prices'])->findOrFail($cartId);

        try {
            $this->assertOwnsCart($cart, $owner->userId, $owner->sessionId);
        } catch (CartAccessDeniedException) {
            throw OrderAccessDeniedException::cart();
        }

        $lines = $this->validatedCheckoutLines($cart);
        $subtotal = array_sum(array_column($lines, 'lineSubtotal'));
        $shippingCost = $this->shippingCostService->standardCost($cart->currency);
        $thresholdDiscount = $cart->currency->calculateThresholdDiscount($subtotal);
        $netSubtotal = max(0, $subtotal - $thresholdDiscount);
        $discount = 0;

        if (! $this->couponPricingService->isBlank($couponCode)) {
            $quote = $this->couponPricingService->quote(
                code: (string) $couponCode,
                subtotal: $subtotal,
                currency: $cart->currency,
                userId: $owner->userId,
                discountableSubtotal: $netSubtotal,
            );
            $discount = $quote->discountAmount;
        }

        $taxAmount = 0;
        $total = max(0, $subtotal - $thresholdDiscount - $discount) + $shippingCost + $taxAmount;

        $minChargeable = $cart->currency->minimumChargeableAmount();
        if ($total > 0 && $total < $minChargeable) {
            $discount += $total;
            $total = 0;
        }

        return new CheckoutPreviewDTO(
            cartId: (int) $cart->id,
            currency: $cart->currency,
            lines: $this->toPreviewLines($lines),
            subtotal: $subtotal,
            shippingCost: $shippingCost,
            discount: $discount,
            taxAmount: $taxAmount,
            total: $total,
            thresholdDiscount: $thresholdDiscount,
        );
    }
}
