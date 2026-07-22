<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

readonly class UpdateCartItemQuantityDTO
{
    public function __construct(
        public int $cartId,
        public int $productVariantId,
        public int $quantity,
        public ?int $userId = null,
        public ?string $sessionId = null,
    ) {}
}
