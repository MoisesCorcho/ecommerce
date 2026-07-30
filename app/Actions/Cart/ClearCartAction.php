<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\CartOwnerDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClearCartAction
{
    use AssertsCartOwnership;

    /**
     * Removes all lines while keeping the cart header (identity + currency).
     *
     * @throws CartAccessDeniedException
     * @throws Throwable
     */
    public function __invoke(int $cartId, CartOwnerDTO $owner): Cart
    {
        return DB::transaction(function () use ($cartId, $owner): Cart {
            /** @var Cart $cart */
            $cart = Cart::query()->lockForUpdate()->findOrFail($cartId);
            $this->assertOwnsCart($cart, $owner->userId, $owner->sessionId);

            $cart->items()->delete();

            return $cart->fresh(['items']) ?? $cart;
        });
    }
}
