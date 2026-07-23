<?php

declare(strict_types=1);

namespace App\DTOs\Payments;

readonly class HostedCheckoutReturnDTO
{
    public function __construct(
        public string $successUrl,
        public string $cancelUrl,
    ) {}
}
