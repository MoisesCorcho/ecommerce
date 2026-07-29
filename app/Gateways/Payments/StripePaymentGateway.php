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

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function createHostedCheckout(
        Order $order,
        Payment $payment,
        HostedCheckoutReturnDTO $returns,
    ): HostedCheckoutSessionDTO {
        $secret = (string) config('ecommerce.payments.stripe.secret_key', '');
        $base = rtrim((string) config('ecommerce.payments.stripe.api_base', 'https://api.stripe.com'), '/');

        if ($secret === '') {
            throw PaymentGatewayException::make();
        }

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->withToken($secret)
                ->post($base.'/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => $returns->successUrl,
                    'cancel_url' => $returns->cancelUrl,
                    'client_reference_id' => (string) $payment->id,
                    'metadata[payment_id]' => (string) $payment->id,
                    'metadata[order_id]' => (string) $order->id,
                    'line_items[0][quantity]' => 1,
                    'line_items[0][price_data][currency]' => strtolower($order->currency->value),
                    'line_items[0][price_data][unit_amount]' => (int) $payment->amount,
                    'line_items[0][price_data][product_data][name]' => 'Order '.$order->order_number,
                ]);
        } catch (Throwable $e) {
            Log::channel('payments')->warning('payments.gateway.stripe.create_transport_error', [
                'provider' => 'stripe',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'exception' => $e::class,
            ]);

            throw PaymentGatewayException::make($e);
        }

        if (! $response->successful()) {
            Log::channel('payments')->warning('payments.gateway.stripe.create_rejected', [
                'provider' => 'stripe',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'body_length' => strlen($response->body()),
            ]);

            throw PaymentGatewayException::make();
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];
        $externalId = (string) ($body['id'] ?? '');
        $url = (string) ($body['url'] ?? '');

        if ($externalId === '' || $url === '') {
            Log::channel('payments')->warning('payments.gateway.stripe.create_incomplete_payload', [
                'provider' => 'stripe',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'has_session_id' => $externalId !== '',
                'has_url' => $url !== '',
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
        $secret = (string) config('ecommerce.payments.stripe.webhook_secret', '');

        if ($secret === '' || $signatureHeader === '') {
            throw InvalidPaymentWebhookSignatureException::make();
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $item) {
            [$key, $value] = array_pad(explode('=', trim($item), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if ($timestamp === null || $signatures === []) {
            throw InvalidPaymentWebhookSignatureException::make();
        }

        // Reject timestamps older than 5 minutes (replay protection).
        if (abs(time() - (int) $timestamp) > 300) {
            throw InvalidPaymentWebhookSignatureException::make();
        }

        $signedPayload = $timestamp.'.'.$rawPayload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return;
            }
        }

        throw InvalidPaymentWebhookSignatureException::make();
    }

    public function parseWebhook(string $rawPayload): ParsedWebhookEventDTO
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);

        $eventId = (string) ($payload['id'] ?? '');
        $eventType = (string) ($payload['type'] ?? '');
        /** @var array<string, mixed> $object */
        $object = $payload['data']['object'] ?? [];

        $paymentId = null;
        if (isset($object['metadata']['payment_id'])) {
            $paymentId = (int) $object['metadata']['payment_id'];
        } elseif (isset($object['client_reference_id']) && is_numeric($object['client_reference_id'])) {
            $paymentId = (int) $object['client_reference_id'];
        }

        $externalId = isset($object['id']) ? (string) $object['id'] : null;
        $paymentIntent = isset($object['payment_intent']) ? (string) $object['payment_intent'] : null;
        $paymentMethod = null;

        $amount = null;
        if (isset($object['amount_total']) && is_numeric($object['amount_total'])) {
            $amount = (int) $object['amount_total'];
        } elseif (isset($object['amount']) && is_numeric($object['amount'])) {
            $amount = (int) $object['amount'];
        }

        $currency = null;
        if (isset($object['currency']) && is_string($object['currency']) && $object['currency'] !== '') {
            $currency = strtoupper($object['currency']);
        }

        $outcome = match ($eventType) {
            'checkout.session.completed',
            'payment_intent.succeeded' => PaymentWebhookOutcomeEnum::Approved,
            'checkout.session.expired',
            'payment_intent.payment_failed' => PaymentWebhookOutcomeEnum::Declined,
            'charge.refunded',
            'refund.created' => PaymentWebhookOutcomeEnum::Refunded,
            default => PaymentWebhookOutcomeEnum::Ignored,
        };

        // Only treat checkout.session.completed as approved when payment_status is paid.
        if ($eventType === 'checkout.session.completed') {
            $paymentStatus = (string) ($object['payment_status'] ?? '');
            if ($paymentStatus !== '' && $paymentStatus !== 'paid') {
                $outcome = PaymentWebhookOutcomeEnum::Ignored;
            }
        }

        return new ParsedWebhookEventDTO(
            eventId: $eventId,
            eventType: $eventType,
            outcome: $outcome,
            payload: $payload,
            paymentId: $paymentId,
            externalId: $externalId,
            paymentMethod: $paymentMethod,
            providerPaymentIntent: $paymentIntent,
            amount: $amount,
            currency: $currency,
        );
    }
}
