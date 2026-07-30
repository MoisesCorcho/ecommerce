<?php

declare(strict_types=1);

namespace App\Actions\Cart\Concerns;

use App\Exceptions\Cart\CartAccessDeniedException;
use App\Models\Cart;

trait AssertsCartOwnership
{
    /**
     * @throws CartAccessDeniedException
     */
    protected function assertOwnsCart(Cart $cart, ?int $userId, ?string $sessionId): void
    {
        if ($userId !== null) {
            if ($cart->user_id !== null && (int) $cart->user_id === $userId) {
                return;
            }

            throw CartAccessDeniedException::make();
        }

        if ($sessionId !== null
            && $cart->user_id === null
            && $cart->session_id !== null
            && $cart->session_id === $sessionId
        ) {
            return;
        }

        throw CartAccessDeniedException::make();
    }
}
