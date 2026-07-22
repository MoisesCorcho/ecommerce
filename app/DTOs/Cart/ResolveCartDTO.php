<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Enums\Commerce\CurrencyEnum;

readonly class ResolveCartDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?string $sessionId = null,
        public ?CurrencyEnum $currency = null,
    ) {}
}
