<?php

declare(strict_types=1);

namespace App\Support\Cart;

use App\Actions\Cart\GetOrCreateCartAction;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Cart\ResolveCartDTO;
use App\Models\Cart;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Support\Facades\Auth;

/**
 * Shared cart identity resolution for Livewire / HTTP surfaces.
 */
trait ResolvesCurrentCart
{
    protected function cartOwner(): CartOwnerDTO
    {
        $user = Auth::user();

        if ($user !== null) {
            return new CartOwnerDTO(userId: (int) $user->getAuthIdentifier());
        }

        return new CartOwnerDTO(sessionId: CartSession::ensureId());
    }

    protected function resolveCurrentCart(?GetOrCreateCartAction $getOrCreateCart = null): Cart
    {
        $getOrCreateCart ??= app(GetOrCreateCartAction::class);
        $user = Auth::user();

        if ($user !== null) {
            return $getOrCreateCart(new ResolveCartDTO(
                userId: (int) $user->getAuthIdentifier(),
                currency: CurrentCurrency::get(),
            ));
        }

        return $getOrCreateCart(new ResolveCartDTO(
            sessionId: CartSession::ensureId(),
            currency: CurrentCurrency::get(),
        ));
    }
}
