<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\CartOwnerDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveCartItemAction
{
    use AssertsCartOwnership;

    /**
     * @throws CartAccessDeniedException
     * @throws CartItemNotFoundException
     * @throws Throwable
     */
    public function __invoke(int $cartId, int $productVariantId, CartOwnerDTO $owner): void
    {
        DB::transaction(function () use ($cartId, $productVariantId, $owner): void {
            /** @var Cart $cart */
            $cart = Cart::query()->lockForUpdate()->findOrFail($cartId);
            $this->assertOwnsCart($cart, $owner->userId, $owner->sessionId);

            /** @var CartItem|null $item */
            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_variant_id', $productVariantId)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                throw CartItemNotFoundException::make();
            }

            $item->delete();
        });
    }
}
