<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Enums\Commerce\CurrencyEnum;

readonly class ChangeCartCurrencyDTO
{
    public function __construct(
        public int $cartId,
        public CurrencyEnum $currency,
        public ?int $userId = null,
        public ?string $sessionId = null,
    ) {}
}
