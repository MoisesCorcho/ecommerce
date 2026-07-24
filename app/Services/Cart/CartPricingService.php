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
        $cart->loadMissing([
            'items.productVariant.product.images',
            'items.productVariant.prices',
            'items.productVariant.images',
        ]);

        $lines = [];
        $total = 0;

        foreach ($cart->items as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;
            $priceRow = $variant?->priceIn($cart->currency);
            $unitPrice = $priceRow?->price ?? 0;
            $lineSubtotal = $unitPrice * (int) $item->quantity;
            $total += $lineSubtotal;

            $variantImages = $variant?->images ?? collect();
            $image = $variantImages->firstWhere('is_primary', true)
                ?? $variantImages->sortBy('sort_order')->first()
                ?? $product?->primaryImage();

            $lines[] = new CartLineViewDTO(
                cartItemId: (int) $item->id,
                productVariantId: (int) $item->product_variant_id,
                quantity: (int) $item->quantity,
                unitPrice: $unitPrice,
                lineSubtotal: $lineSubtotal,
                sku: $variant?->sku,
                productName: $product?->name,
                imagePath: $image?->path,
                productSlug: $product?->slug,
                color: $variant?->color,
                size: $variant?->size,
                material: $product?->material,
                stock: (int) ($variant?->stock ?? 0),
                isAvailable: $variant !== null && $variant->is_active && $priceRow !== null,
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
