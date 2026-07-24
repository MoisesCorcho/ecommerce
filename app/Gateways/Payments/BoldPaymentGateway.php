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
use Illuminate\Support\Facades\Log;
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

        $payload = [
            'amount_type' => 'CLOSE',
            'amount' => [
                'currency' => $order->currency->value,
                'total_amount' => (int) $payment->amount,
                'tip_amount' => 0,
            ],
            'reference' => 'pay-'.$payment->id,
            'description' => 'Order '.$order->order_number,
        ];

        // Bold rejects localhost / non-public callbacks with 403 Forbidden.
        // Only send when the return URL is public HTTPS (e.g. production or tunnel).
        if ($this->isPublicHttpsUrl($returns->successUrl)) {
            $payload['callback_url'] = $returns->successUrl;
        }

        try {
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => 'x-api-key '.$apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($base.'/online/link/v1', $payload);
        } catch (Throwable $e) {
            Log::warning('Bold createHostedCheckout transport error', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);

            throw PaymentGatewayException::make($e);
        }

        if (! $response->successful()) {
            Log::warning('Bold createHostedCheckout rejected', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw PaymentGatewayException::make();
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];
        /** @var array<string, mixed> $payloadBody */
        $payloadBody = $body['payload'] ?? [];
        $externalId = (string) ($payloadBody['payment_link'] ?? '');
        $url = (string) ($payloadBody['url'] ?? '');

        if ($externalId === '' || $url === '') {
            Log::warning('Bold createHostedCheckout missing payload fields', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'body' => $body,
            ]);

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
        if ($signatureHeader === '') {
            throw InvalidPaymentWebhookSignatureException::make();
        }

        // Bold: Base64(body) → HMAC-SHA256 hex (header x-bold-signature).
        // Production: signature secret key.
        // Test mode (docs): secret MUST be empty string "" — not the prod secret_key.
        $secret = $this->webhookSigningSecret();
        $encoded = base64_encode($rawPayload);
        $expected = hash_hmac('sha256', $encoded, $secret);

        if (! hash_equals($expected, $signatureHeader)) {
            throw InvalidPaymentWebhookSignatureException::make();
        }
    }

    /**
     * Resolve the HMAC key for Bold webhooks.
     *
     * - BOLD_WEBHOOK_SECRET set (including empty "") → use as-is (empty = test mode).
     * - Unset → fall back to BOLD_SECRET_KEY for production.
     */
    private function webhookSigningSecret(): string
    {
        $webhookSecret = config('ecommerce.payments.bold.webhook_secret');

        // null/missing: fall back. Explicit "" is valid (Bold sandbox).
        if ($webhookSecret !== null) {
            return (string) $webhookSecret;
        }

        return (string) (config('ecommerce.payments.bold.secret_key') ?? '');
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

        // Bold sandbox often redacts money fields to 0 / "XXXX". Only expose amount for SH-05
        // when the payload carries a positive total (production SALE_APPROVED).
        $amount = $this->parseWebhookAmount($data);

        $currency = null;
        if (isset($data['amount']['currency']) && is_string($data['amount']['currency']) && $data['amount']['currency'] !== '') {
            $currency = strtoupper($data['amount']['currency']);
        } elseif (isset($data['currency']) && is_string($data['currency']) && $data['currency'] !== '') {
            $currency = strtoupper($data['currency']);
        }

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
            amount: $amount,
            currency: $currency,
        );
    }

    /**
     * Extract minor-unit amount from a Bold webhook data node.
     *
     * Prefers `amount.total_amount` (create API shape) then `amount.total`.
     * Non-positive values are treated as "not exposed" (null): Bold test-mode
     * SALE_APPROVED payloads redact totals to 0, which must not fail SH-05.
     *
     * @param  array<string, mixed>  $data
     */
    private function parseWebhookAmount(array $data): ?int
    {
        $candidates = [];

        if (isset($data['amount']) && is_array($data['amount'])) {
            /** @var array<string, mixed> $amountNode */
            $amountNode = $data['amount'];
            $candidates[] = $amountNode['total_amount'] ?? null;
            $candidates[] = $amountNode['total'] ?? null;
        } elseif (isset($data['amount']) && is_numeric($data['amount'])) {
            $candidates[] = $data['amount'];
        }

        foreach ($candidates as $candidate) {
            if (! is_numeric($candidate)) {
                continue;
            }

            $value = (int) $candidate;

            if ($value > 0) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Bold only accepts public HTTPS callback URLs (not localhost / private hosts).
     */
    private function isPublicHttpsUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.test') || str_ends_with($host, '.localhost')) {
            return false;
        }

        return true;
    }
}
