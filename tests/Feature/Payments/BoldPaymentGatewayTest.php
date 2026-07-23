<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\DTOs\Payments\HostedCheckoutReturnDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Gateways\Payments\BoldPaymentGateway;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        config([
            'ecommerce.payments.bold.api_key' => 'test-identity-key',
            'ecommerce.payments.bold.api_base' => 'https://integrations.api.bold.co',
        ]);

        Http::fake([
            'integrations.api.bold.co/online/link/v1' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        [$order, $payment] = $this->makeCopOrderAndPayment();

        $this->expectException(PaymentGatewayException::class);

        app(BoldPaymentGateway::class)->createHostedCheckout(
            $order,
            $payment,
            new HostedCheckoutReturnDTO(
                successUrl: 'https://shop.example.com/ok',
                cancelUrl: 'https://shop.example.com/cancel',
            ),
        );
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
