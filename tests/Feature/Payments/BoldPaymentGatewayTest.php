<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\DTOs\Payments\HostedCheckoutReturnDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Enums\Payments\PaymentWebhookOutcomeEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Gateways\Payments\BoldPaymentGateway;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class BoldPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_hosted_checkout_omits_localhost_callback_url(): void
    {
        config([
            'ecommerce.payments.bold.api_key' => 'test-identity-key',
            'ecommerce.payments.bold.api_base' => 'https://integrations.api.bold.co',
        ]);

        Http::fake([
            'integrations.api.bold.co/online/link/v1' => Http::response([
                'payload' => [
                    'payment_link' => 'LNK_TEST123',
                    'url' => 'https://checkout.bold.co/payment/LNK_TEST123',
                ],
                'errors' => [],
            ], 200),
        ]);

        [$order, $payment] = $this->makeCopOrderAndPayment();

        $session = app(BoldPaymentGateway::class)->createHostedCheckout(
            $order,
            $payment,
            new HostedCheckoutReturnDTO(
                successUrl: 'http://localhost/orders/1/thank-you?payment=processing',
                cancelUrl: 'http://localhost/orders/1/thank-you?payment=cancelled',
            ),
        );

        $this->assertSame('https://checkout.bold.co/payment/LNK_TEST123', $session->redirectUrl);
        $this->assertSame('LNK_TEST123', $session->externalId);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://integrations.api.bold.co/online/link/v1'
                && ($data['reference'] ?? null) === 'pay-'.$this->lastPaymentId()
                && ! array_key_exists('callback_url', $data)
                && ($data['amount']['total_amount'] ?? null) === 100_000
                && ($data['amount']['currency'] ?? null) === 'COP';
        });
    }

    public function test_create_hosted_checkout_sends_public_https_callback_url(): void
    {
        config([
            'ecommerce.payments.bold.api_key' => 'test-identity-key',
            'ecommerce.payments.bold.api_base' => 'https://integrations.api.bold.co',
        ]);

        Http::fake([
            'integrations.api.bold.co/online/link/v1' => Http::response([
                'payload' => [
                    'payment_link' => 'LNK_PUBLIC',
                    'url' => 'https://checkout.bold.co/payment/LNK_PUBLIC',
                ],
                'errors' => [],
            ], 200),
        ]);

        [$order, $payment] = $this->makeCopOrderAndPayment();
        $callback = 'https://shop.example.com/orders/1/thank-you?signature=abc';

        app(BoldPaymentGateway::class)->createHostedCheckout(
            $order,
            $payment,
            new HostedCheckoutReturnDTO(
                successUrl: $callback,
                cancelUrl: 'https://shop.example.com/orders/1/thank-you?payment=cancelled',
            ),
        );

        Http::assertSent(function ($request) use ($callback): bool {
            $data = $request->data();

            return ($data['callback_url'] ?? null) === $callback;
        });
    }

    public function test_create_hosted_checkout_throws_on_bold_403(): void
    {
        $paymentsLog = \Mockery::mock(LoggerInterface::class);
        $paymentsLog->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context = []): bool {
                return $message === 'payments.gateway.bold.create_rejected'
                    && ($context['http_status'] ?? null) === 403
                    && ! array_key_exists('body', $context);
            });
        Log::shouldReceive('channel')->with('payments')->andReturn($paymentsLog);

        config([
            'ecommerce.payments.bold.api_key' => 'test-identity-key',
            'ecommerce.payments.bold.api_base' => 'https://integrations.api.bold.co',
        ]);

        Http::fake([
            'integrations.api.bold.co/online/link/v1' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        [$order, $payment] = $this->makeCopOrderAndPayment();

        try {
            app(BoldPaymentGateway::class)->createHostedCheckout(
                $order,
                $payment,
                new HostedCheckoutReturnDTO(
                    successUrl: 'https://shop.example.com/ok',
                    cancelUrl: 'https://shop.example.com/cancel',
                ),
            );
            $this->fail('Expected PaymentGatewayException');
        } catch (PaymentGatewayException $e) {
            $this->assertSame('Forbidden', $e->diagnostic);
        }
    }

    public function test_webhook_signature_accepts_empty_secret_in_test_mode(): void
    {
        // Bold docs: test-mode webhooks are signed with secret = "".
        config([
            'ecommerce.payments.bold.webhook_secret' => '',
            'ecommerce.payments.bold.secret_key' => 'should-not-be-used-in-test',
        ]);

        $payload = '{"id":"evt_test_1","type":"SALE_APPROVED","data":{}}';
        $signature = hash_hmac('sha256', base64_encode($payload), '');

        app(BoldPaymentGateway::class)->verifyWebhookSignature($payload, $signature);

        $this->addToAssertionCount(1);
    }

    public function test_webhook_signature_uses_secret_key_when_webhook_secret_unset(): void
    {
        config([
            'ecommerce.payments.bold.webhook_secret' => null,
            'ecommerce.payments.bold.secret_key' => 'prod-secret-key',
        ]);

        $payload = '{"id":"evt_prod_1","type":"SALE_APPROVED","data":{}}';
        $signature = hash_hmac('sha256', base64_encode($payload), 'prod-secret-key');

        app(BoldPaymentGateway::class)->verifyWebhookSignature($payload, $signature);

        $this->addToAssertionCount(1);
    }

    public function test_webhook_signature_rejects_wrong_secret(): void
    {
        config([
            'ecommerce.payments.bold.webhook_secret' => '',
            'ecommerce.payments.bold.secret_key' => 'prod-secret-key',
        ]);

        $payload = '{"id":"evt_bad","type":"SALE_APPROVED","data":{}}';
        // Signed as if production secret were used — must fail in test mode.
        $signature = hash_hmac('sha256', base64_encode($payload), 'prod-secret-key');

        $this->expectException(InvalidPaymentWebhookSignatureException::class);

        app(BoldPaymentGateway::class)->verifyWebhookSignature($payload, $signature);
    }

    public function test_parse_webhook_sandbox_redacted_total_zero_yields_null_amount(): void
    {
        // Real Bold test-mode shape: card/ids redacted, amount.total forced to 0.
        $payload = json_encode([
            'id' => 'fdec35d1-9046-46f2-a6b4-63aae1400430',
            'type' => 'SALE_APPROVED',
            'data' => [
                'amount' => [
                    'tip' => 0,
                    'taxes' => [],
                    'total' => 0,
                    'currency' => 'COP',
                ],
                'metadata' => [
                    'reference' => 'pay-18',
                ],
                'payment_id' => 'XXXX',
                'payment_method' => 'CARD_WEB',
            ],
        ], JSON_THROW_ON_ERROR);

        $parsed = app(BoldPaymentGateway::class)->parseWebhook($payload);

        $this->assertSame('fdec35d1-9046-46f2-a6b4-63aae1400430', $parsed->eventId);
        $this->assertSame(PaymentWebhookOutcomeEnum::Approved, $parsed->outcome);
        $this->assertSame(18, $parsed->paymentId);
        $this->assertNull($parsed->amount, 'Redacted total 0 must not be treated as a real amount');
        $this->assertSame('COP', $parsed->currency);
        $this->assertSame('CARD_WEB', $parsed->paymentMethod);
    }

    public function test_parse_webhook_positive_total_and_total_amount_are_exposed(): void
    {
        $withTotal = json_encode([
            'id' => 'evt_total',
            'type' => 'SALE_APPROVED',
            'data' => [
                'amount' => ['total' => 796_500, 'currency' => 'COP'],
                'metadata' => ['reference' => 'pay-7'],
            ],
        ], JSON_THROW_ON_ERROR);

        $parsedTotal = app(BoldPaymentGateway::class)->parseWebhook($withTotal);
        $this->assertSame(796_500, $parsedTotal->amount);
        $this->assertSame(7, $parsedTotal->paymentId);

        $withTotalAmount = json_encode([
            'id' => 'evt_total_amount',
            'type' => 'SALE_APPROVED',
            'data' => [
                'amount' => ['total_amount' => 230_000, 'currency' => 'COP'],
                'metadata' => ['reference' => 'pay-9'],
            ],
        ], JSON_THROW_ON_ERROR);

        $parsedTotalAmount = app(BoldPaymentGateway::class)->parseWebhook($withTotalAmount);
        $this->assertSame(230_000, $parsedTotalAmount->amount);
        $this->assertSame(9, $parsedTotalAmount->paymentId);
    }

    /**
     * @return array{0: Order, 1: Payment}
     */
    private function makeCopOrderAndPayment(): array
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
            'currency' => CurrencyEnum::Cop,
            'subtotal' => 100_000,
            'total' => 100_000,
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Bold,
            'currency' => CurrencyEnum::Cop,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 100_000,
        ]);

        $this->lastPaymentId = $payment->id;

        return [$order, $payment];
    }

    private ?int $lastPaymentId = null;

    private function lastPaymentId(): int
    {
        return (int) $this->lastPaymentId;
    }
}
