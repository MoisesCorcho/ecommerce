<?php

declare(strict_types=1);

namespace App\DTOs\Payments;

readonly class HostedCheckoutSessionDTO
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $redirectUrl,
        public string $externalId,
        public array $raw = [],
    ) {}
}
