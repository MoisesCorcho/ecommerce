<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\DTOs\Cart\CartLineViewDTO;
use App\DTOs\Cart\CartViewDTO;
use App\Models\Cart;

/**
 * Live pricing for cart lines from catalog prices (integers; no floats, no cart_items snapshots).
 */
class CartPricingService
{
    public function view(Cart $cart): CartViewDTO
    {
        $cart->loadMissing(['items.productVariant.product', 'items.productVariant.prices']);

        $lines = [];
        $total = 0;

        foreach ($cart->items as $item) {
            $variant = $item->productVariant;
            $priceRow = $variant?->priceIn($cart->currency);
            $unitPrice = $priceRow?->price ?? 0;
            $lineSubtotal = $unitPrice * (int) $item->quantity;
            $total += $lineSubtotal;

            $lines[] = new CartLineViewDTO(
                cartItemId: (int) $item->id,
                productVariantId: (int) $item->product_variant_id,
                quantity: (int) $item->quantity,
                unitPrice: $unitPrice,
                lineSubtotal: $lineSubtotal,
                sku: $variant?->sku,
                productName: $variant?->product?->name,
            );
        }

        return new CartViewDTO(
            cartId: (int) $cart->id,
            currency: $cart->currency,
            lines: $lines,
            total: $total,
            userId: $cart->user_id !== null ? (int) $cart->user_id : null,
            sessionId: $cart->session_id,
        );
    }
}
