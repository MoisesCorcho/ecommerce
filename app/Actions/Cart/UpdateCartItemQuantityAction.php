<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartItemEligibility;
use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\UpdateCartItemQuantityDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartItemNotFoundException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateCartItemQuantityAction
{
    use AssertsCartItemEligibility;
    use AssertsCartOwnership;

    /**
     * Returns null when quantity is 0 and the line was removed.
     *
     * @throws CartAccessDeniedException
     * @throws CartItemNotEligibleException
     * @throws CartItemNotFoundException
     * @throws CartQuantityNotAllowedException
     * @throws InsufficientCartStockException
     * @throws Throwable
     */
    public function __invoke(UpdateCartItemQuantityDTO $dto): ?CartItem
    {
        if ($dto->quantity < 0) {
            throw CartQuantityNotAllowedException::invalid();
        }

        return DB::transaction(function () use ($dto): ?CartItem {
            /** @var Cart $cart */
            $cart = Cart::query()->lockForUpdate()->findOrFail($dto->cartId);
            $this->assertOwnsCart($cart, $dto->userId, $dto->sessionId);

            /** @var CartItem|null $item */
            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_variant_id', $dto->productVariantId)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                throw CartItemNotFoundException::make();
            }

            if ($dto->quantity === 0) {
                $item->delete();

                return null;
            }

            /** @var ProductVariant $variant */
            $variant = ProductVariant::query()
                ->with(['product', 'prices'])
                ->lockForUpdate()
                ->findOrFail($dto->productVariantId);

            $this->assertEligible($variant, $cart->currency);
            $this->assertQuantityAllowed($dto->quantity, (int) $variant->stock);

            $item->update(['quantity' => $dto->quantity]);

            return $item->fresh() ?? $item;
        });
    }
}
