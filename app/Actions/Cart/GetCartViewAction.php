<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Cart\CartViewDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Models\Cart;
use App\Services\Cart\CartPricingService;

class GetCartViewAction
{
    use AssertsCartOwnership;

    public function __construct(
        private readonly CartPricingService $pricing,
    ) {}

    /**
     * @throws CartAccessDeniedException
     */
    public function __invoke(int $cartId, CartOwnerDTO $owner): CartViewDTO
    {
        /** @var Cart $cart */
        $cart = Cart::query()->with(['items.productVariant.product', 'items.productVariant.prices'])->findOrFail($cartId);
        $this->assertOwnsCart($cart, $owner->userId, $owner->sessionId);

        return $this->pricing->view($cart);
    }
}
