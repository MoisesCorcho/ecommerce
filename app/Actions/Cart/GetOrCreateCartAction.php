<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\ResolveCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use InvalidArgumentException;

class GetOrCreateCartAction
{
    public function __invoke(ResolveCartDTO $dto): Cart
    {
        if ($dto->userId !== null) {
            return $this->resolveUserCart($dto->userId, $dto->currency);
        }

        if ($dto->sessionId === null || trim($dto->sessionId) === '') {
            throw new InvalidArgumentException('A session id is required to resolve a guest cart.');
        }

        return $this->resolveGuestCart($dto->sessionId, $dto->currency);
    }

    private function resolveUserCart(int $userId, ?CurrencyEnum $currency): Cart
    {
        $cart = Cart::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        if ($cart !== null) {
            return $cart;
        }

        return Cart::query()->create([
            'user_id' => $userId,
            'session_id' => null,
            'currency' => $currency ?? CurrencyEnum::Cop,
        ]);
    }

    private function resolveGuestCart(string $sessionId, ?CurrencyEnum $currency): Cart
    {
        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $sessionId)
            ->orderByDesc('id')
            ->first();

        if ($cart !== null) {
            return $cart;
        }

        return Cart::query()->create([
            'user_id' => null,
            'session_id' => $sessionId,
            'currency' => $currency ?? CurrencyEnum::Cop,
        ]);
    }
}
