<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\ChangeCartCurrencyDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartCurrencyChangeBlockedException;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChangeCartCurrencyAction
{
    use AssertsCartOwnership;

    /**
     * @throws CartAccessDeniedException
     * @throws CartCurrencyChangeBlockedException
     * @throws Throwable
     */
    public function __invoke(ChangeCartCurrencyDTO $dto): Cart
    {
        return DB::transaction(function () use ($dto): Cart {
            /** @var Cart $cart */
            $cart = Cart::query()
                ->with(['items.productVariant.prices'])
                ->lockForUpdate()
                ->findOrFail($dto->cartId);

            $this->assertOwnsCart($cart, $dto->userId, $dto->sessionId);

            if ($cart->currency === $dto->currency) {
                return $cart;
            }

            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                if ($variant === null || $variant->priceIn($dto->currency) === null) {
                    throw CartCurrencyChangeBlockedException::make($dto->currency);
                }
            }

            $cart->update(['currency' => $dto->currency]);

            return $cart->fresh(['items.productVariant.prices']) ?? $cart;
        });
    }
}
