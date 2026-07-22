<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\Actions\Orders\Concerns\ValidatesCartLinesForCheckout;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Orders\CheckoutPreviewDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Exceptions\Orders\OrderAccessDeniedException;
use App\Models\Cart;
use App\Services\Orders\ShippingCostService;

class ValidateCartForCheckoutAction
{
    use AssertsCartOwnership;
    use ValidatesCartLinesForCheckout;

    public function __construct(
        private readonly ShippingCostService $shippingCostService,
    ) {}

    /**
     * @throws OrderAccessDeniedException
     * @throws CheckoutCartEmptyException
     * @throws CheckoutCartNotReadyException
     */
    public function __invoke(int $cartId, CartOwnerDTO $owner): CheckoutPreviewDTO
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
        $discount = 0;
        $taxAmount = 0;
        $total = $subtotal + $shippingCost - $discount + $taxAmount;

        return new CheckoutPreviewDTO(
            cartId: (int) $cart->id,
            currency: $cart->currency,
            lines: $this->toPreviewLines($lines),
            subtotal: $subtotal,
            shippingCost: $shippingCost,
            discount: $discount,
            taxAmount: $taxAmount,
            total: $total,
        );
    }
}
