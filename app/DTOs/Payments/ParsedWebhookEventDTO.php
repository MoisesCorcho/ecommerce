<?php

declare(strict_types=1);

namespace App\DTOs\Payments;

use App\Enums\Payments\PaymentWebhookOutcomeEnum;

readonly class ParsedWebhookEventDTO
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $eventId,
        public string $eventType,
        public PaymentWebhookOutcomeEnum $outcome,
        public array $payload,
        public ?int $paymentId = null,
        public ?string $externalId = null,
        public ?string $paymentMethod = null,
        public ?string $providerPaymentIntent = null,
        /** Minor units when the provider exposes them; null = skip amount check. */
        public ?int $amount = null,
        /** ISO currency code when present (normalized upper); null = skip currency check. */
        public ?string $currency = null,
    ) {}
}
