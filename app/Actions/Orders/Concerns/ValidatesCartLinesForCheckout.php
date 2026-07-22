<?php

declare(strict_types=1);

namespace App\Actions\Orders\Concerns;

use App\Actions\Cart\Concerns\AssertsCartItemEligibility;
use App\DTOs\Orders\CheckoutPreviewLineDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

trait ValidatesCartLinesForCheckout
{
    use AssertsCartItemEligibility;

    /**
     * @return list<array{item: CartItem, variant: ProductVariant, unitPrice: int, productName: string, variantLabel: ?string, sku: ?string, quantity: int, lineSubtotal: int}>
     *
     * @throws CheckoutCartEmptyException
     * @throws CheckoutCartNotReadyException
     */
    protected function validatedCheckoutLines(Cart $cart): array
    {
        $cart->loadMissing(['items.productVariant.product', 'items.productVariant.prices']);

        if ($cart->items->isEmpty()) {
            throw CheckoutCartEmptyException::make();
        }

        $lines = [];

        foreach ($cart->items as $item) {
            $lines[] = $this->validateCartItemLine($item, $cart->currency);
        }

        return $lines;
    }

    /**
     * @return array{item: CartItem, variant: ProductVariant, unitPrice: int, productName: string, variantLabel: ?string, sku: ?string, quantity: int, lineSubtotal: int}
     *
     * @throws CheckoutCartNotReadyException
     */
    protected function validateCartItemLine(CartItem $item, CurrencyEnum $currency): array
    {
        $variant = $item->productVariant;

        if ($variant === null) {
            throw CheckoutCartNotReadyException::make();
        }

        $productName = $variant->product?->name ?? $variant->sku ?? 'Item';
        $quantity = (int) $item->quantity;

        try {
            $this->assertEligible($variant, $currency);
            $this->assertQuantityAllowed($quantity, (int) $variant->stock);
        } catch (CartItemNotEligibleException) {
            throw CheckoutCartNotReadyException::notEligible($productName);
        } catch (InsufficientCartStockException) {
            throw CheckoutCartNotReadyException::insufficientStock(
                $productName,
                max(0, (int) $variant->stock),
            );
        } catch (CartQuantityNotAllowedException) {
            throw CheckoutCartNotReadyException::make();
        }

        $unitPrice = (int) ($variant->priceIn($currency)?->price ?? 0);
        $variantLabel = $this->variantLabel($variant);

        return [
            'item' => $item,
            'variant' => $variant,
            'unitPrice' => $unitPrice,
            'productName' => $productName,
            'variantLabel' => $variantLabel,
            'sku' => $variant->sku,
            'quantity' => $quantity,
            'lineSubtotal' => $unitPrice * $quantity,
        ];
    }

    /**
     * @param  list<array{item: CartItem, variant: ProductVariant, unitPrice: int, productName: string, variantLabel: ?string, sku: ?string, quantity: int, lineSubtotal: int}>  $lines
     * @return list<CheckoutPreviewLineDTO>
     */
    protected function toPreviewLines(array $lines): array
    {
        return array_map(
            static fn (array $line): CheckoutPreviewLineDTO => new CheckoutPreviewLineDTO(
                productVariantId: (int) $line['variant']->id,
                productName: $line['productName'],
                variantLabel: $line['variantLabel'],
                sku: $line['sku'],
                unitPrice: $line['unitPrice'],
                quantity: $line['quantity'],
                lineSubtotal: $line['lineSubtotal'],
            ),
            $lines,
        );
    }

    protected function variantLabel(ProductVariant $variant): ?string
    {
        $parts = array_values(array_filter([
            $variant->color,
            $variant->size,
        ], static fn (?string $value): bool => $value !== null && $value !== ''));

        if ($parts === []) {
            return null;
        }

        return implode(' / ', $parts);
    }
}
