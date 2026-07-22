<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartItemEligibility;
use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\AddCartItemDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddCartItemAction
{
    use AssertsCartItemEligibility;
    use AssertsCartOwnership;

    /**
     * @throws CartAccessDeniedException
     * @throws CartItemNotEligibleException
     * @throws CartQuantityNotAllowedException
     * @throws InsufficientCartStockException
     * @throws Throwable
     */
    public function __invoke(AddCartItemDTO $dto): CartItem
    {
        if ($dto->quantity < 1) {
            throw CartQuantityNotAllowedException::invalid();
        }

        return DB::transaction(function () use ($dto): CartItem {
            /** @var Cart $cart */
            $cart = Cart::query()->lockForUpdate()->findOrFail($dto->cartId);
            $this->assertOwnsCart($cart, $dto->userId, $dto->sessionId);

            /** @var ProductVariant $variant */
            $variant = ProductVariant::query()
                ->with(['product', 'prices'])
                ->lockForUpdate()
                ->findOrFail($dto->productVariantId);

            $this->assertEligible($variant, $cart->currency);

            /** @var CartItem|null $existing */
            $existing = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = ($existing?->quantity ?? 0) + $dto->quantity;
            $this->assertQuantityAllowed($newQuantity, (int) $variant->stock);

            if ($existing !== null) {
                $existing->update(['quantity' => $newQuantity]);

                return $existing->fresh() ?? $existing;
            }

            $item = CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity' => $newQuantity,
            ]);

            return $item->fresh() ?? $item;
        });
    }
}
