<?php

declare(strict_types=1);

namespace App\Listeners\Cart;

use App\Actions\Cart\MergeGuestCartIntoUserCartAction;
use App\Support\Cart\CartSession;
use Illuminate\Auth\Events\Login;

/**
 * Merges the guest session cart into the authenticated user's cart after login.
 */
class MergeGuestCartOnLoginListener
{
    public function __construct(
        private readonly MergeGuestCartIntoUserCartAction $mergeGuestCart,
    ) {}

    public function handle(Login $event): void
    {
        $sessionId = CartSession::id();

        if ($sessionId === null || $sessionId === '') {
            return;
        }

        $userId = (int) $event->user->getAuthIdentifier();

        ($this->mergeGuestCart)($userId, $sessionId);

        CartSession::forget();
    }
}
