<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

/**
 * Identity used to authorize cart mutations (guest session and/or authenticated user).
 */
readonly class CartOwnerDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?string $sessionId = null,
    ) {}
}
