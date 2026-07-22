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
use Illuminate\Support\Facades\Http;
use Throwable;

class BoldPaymentGateway implements PaymentGatewayInterface
{
    public function createHostedCheckout(
        Order $order,
        Payment $payment,
        HostedCheckoutReturnDTO $returns,
    ): HostedCheckoutSessionDTO {
        $apiKey = (string) config('ecommerce.payments.bold.api_key', '');
        $base = rtrim((string) config('ecommerce.payments.bold.api_base', 'https://integrations.api.bold.co'), '/');

        if ($apiKey === '') {
            throw PaymentGatewayException::make();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'x-api-key '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post($base.'/online/link/v1', [
                'amount_type' => 'CLOSE',
                'amount' => [
                    'currency' => $order->currency->value,
                    'total_amount' => (int) $payment->amount,
                    'tip_amount' => 0,
                ],
                'reference' => 'pay-'.$payment->id,
                'description' => 'Order '.$order->order_number,
                'callback_url' => $returns->successUrl,
            ]);
        } catch (Throwable $e) {
            throw PaymentGatewayException::make($e);
        }

        if (! $response->successful()) {
            throw PaymentGatewayException::make();
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];
        /** @var array<string, mixed> $payload */
        $payload = $body['payload'] ?? [];
        $externalId = (string) ($payload['payment_link'] ?? '');
        $url = (string) ($payload['url'] ?? '');

        if ($externalId === '' || $url === '') {
            throw PaymentGatewayException::make();
        }

        return new HostedCheckoutSessionDTO(
            redirectUrl: $url,
            externalId: $externalId,
            raw: $body,
        );
    }

    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): void
    {
        $secret = (string) (
            config('ecommerce.payments.bold.webhook_secret')
            ?: config('ecommerce.payments.bold.secret_key')
            ?: ''
        );

        if ($secret === '' || $signatureHeader === '') {
            throw InvalidPaymentWebhookSignatureException::make();
        }

        // Bold: Base64(body) then HMAC-SHA256 hex with secret key.
        $encoded = base64_encode($rawPayload);
        $expected = hash_hmac('sha256', $encoded, $secret);

        if (! hash_equals($expected, $signatureHeader)) {
            throw InvalidPaymentWebhookSignatureException::make();
        }
    }

    public function parseWebhook(string $rawPayload): ParsedWebhookEventDTO
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);

        $eventId = (string) ($payload['id'] ?? '');
        $eventType = (string) ($payload['type'] ?? '');
        /** @var array<string, mixed> $data */
        $data = $payload['data'] ?? [];

        $reference = null;
        if (isset($data['metadata']['reference']) && is_string($data['metadata']['reference'])) {
            $reference = $data['metadata']['reference'];
        }

        $paymentId = null;
        if (is_string($reference) && preg_match('/^pay-(\d+)$/', $reference, $matches) === 1) {
            $paymentId = (int) $matches[1];
        }

        $externalId = $reference
            ?? (isset($data['payment_id']) ? (string) $data['payment_id'] : null);

        $paymentMethod = isset($data['payment_method']) ? (string) $data['payment_method'] : null;

        $outcome = match ($eventType) {
            'SALE_APPROVED' => PaymentWebhookOutcomeEnum::Approved,
            'SALE_REJECTED' => PaymentWebhookOutcomeEnum::Declined,
            'VOID_APPROVED' => PaymentWebhookOutcomeEnum::Refunded,
            default => PaymentWebhookOutcomeEnum::Ignored,
        };

        return new ParsedWebhookEventDTO(
            eventId: $eventId,
            eventType: $eventType,
            outcome: $outcome,
            payload: $payload,
            paymentId: $paymentId,
            externalId: $externalId,
            paymentMethod: $paymentMethod,
        );
    }
}
