<?php

declare(strict_types=1);

namespace App\Gateways\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\DTOs\Payments\HostedCheckoutReturnDTO;
use App\DTOs\Payments\HostedCheckoutSessionDTO;
use App\DTOs\Payments\ParsedWebhookEventDTO;
use App\Enums\Payments\PaymentWebhookOutcomeEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Models\Order;
use App\Models\Payment;

/**
 * Test double for payment providers. Tracks create calls; verifies a simple HMAC signature.
 */
class FakePaymentGateway implements PaymentGatewayInterface
{
    /** @var list<array{order_id: int, payment_id: int, amount: int, currency: string}> */
    public array $createdSessions = [];

    public bool $shouldFailCreate = false;

    public string $secret = 'fake-webhook-secret';

    public function createHostedCheckout(
        Order $order,
        Payment $payment,
        HostedCheckoutReturnDTO $returns,
    ): HostedCheckoutSessionDTO {
        if ($this->shouldFailCreate) {
            throw PaymentGatewayException::make();
        }

        $this->createdSessions[] = [
            'order_id' => (int) $order->id,
            'payment_id' => (int) $payment->id,
            'amount' => (int) $payment->amount,
            'currency' => $payment->currency->value,
        ];

        $externalId = 'fake_sess_'.$payment->id;

        return new HostedCheckoutSessionDTO(
            redirectUrl: 'https://payments.test/checkout/'.$externalId,
            externalId: $externalId,
            raw: [
                'id' => $externalId,
                'success_url' => $returns->successUrl,
                'cancel_url' => $returns->cancelUrl,
            ],
        );
    }

    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): void
    {
        $expected = hash_hmac('sha256', $rawPayload, $this->secret);

        if (! hash_equals($expected, $signatureHeader)) {
            throw InvalidPaymentWebhookSignatureException::make();
        }
    }

    public function parseWebhook(string $rawPayload): ParsedWebhookEventDTO
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);

        $outcome = PaymentWebhookOutcomeEnum::from((string) ($payload['outcome'] ?? 'ignored'));

        $amount = isset($payload['amount']) && is_numeric($payload['amount'])
            ? (int) $payload['amount']
            : null;
        $currency = isset($payload['currency']) && is_string($payload['currency']) && $payload['currency'] !== ''
            ? strtoupper($payload['currency'])
            : null;

        return new ParsedWebhookEventDTO(
            eventId: (string) ($payload['event_id'] ?? ''),
            eventType: (string) ($payload['event_type'] ?? 'fake.event'),
            outcome: $outcome,
            payload: $payload,
            paymentId: isset($payload['payment_id']) ? (int) $payload['payment_id'] : null,
            externalId: isset($payload['external_id']) ? (string) $payload['external_id'] : null,
            paymentMethod: isset($payload['payment_method']) ? (string) $payload['payment_method'] : null,
            providerPaymentIntent: isset($payload['payment_intent']) ? (string) $payload['payment_intent'] : null,
            amount: $amount,
            currency: $currency,
        );
    }

    public function sign(string $rawPayload): string
    {
        return hash_hmac('sha256', $rawPayload, $this->secret);
    }
}
