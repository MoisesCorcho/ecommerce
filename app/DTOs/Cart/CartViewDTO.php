<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Enums\Commerce\CurrencyEnum;

readonly class CartViewDTO
{
    /**
     * @param  list<CartLineViewDTO>  $lines
     */
    public function __construct(
        public int $cartId,
        public CurrencyEnum $currency,
        public array $lines,
        public int $total,
        public ?int $userId = null,
        public ?string $sessionId = null,
    ) {}
}
