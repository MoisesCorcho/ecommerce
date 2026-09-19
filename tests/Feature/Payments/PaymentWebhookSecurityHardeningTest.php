<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Gateways\Payments\FakePaymentGateway;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $fakeGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeGateway = new FakePaymentGateway;
        $this->app->instance('payment.gateway.stripe', $this->fakeGateway);
        $this->app->instance('payment.gateway.bold', $this->fakeGateway);
    }

    private function createPendingOrderWithPayment(
        int $amount = 150000,
        CurrencyEnum $currency = CurrencyEnum::Cop,
        PaymentProviderEnum $provider = PaymentProviderEnum::Bold,
    ): array {
        $user = User::factory()->create();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        /** @var ProductVariant $variant */
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
            'currency' => $currency,
            'total' => $amount,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => $amount,
        ]);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => $provider,
            'status' => PaymentStatusEnum::Pending,
            'amount' => $amount,
            'currency' => $currency,
        ]);

        return [$order, $payment, $variant];
    }

    public function test_invalid_webhook_signature_is_rejected(): void
    {
        [$order, $payment] = $this->createPendingOrderWithPayment();

        $payload = json_encode([
            'event_id' => 'evt_fake_sig',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        $response = $this->postJson(route('webhooks.bold'), json_decode($payload, true), [
            'X-Bold-Signature' => 'invalid_signature_hash',
        ]);

        $response->assertStatus(400);

        $payment->refresh();
        $order->refresh();
        $this->assertSame(PaymentStatusEnum::Pending, $payment->status);
        $this->assertSame(OrderStatusEnum::Pending, $order->status);
    }

    public function test_amount_mismatch_attack_prevents_order_approval(): void
    {
        // Payment is for 150,000 COP
        [$order, $payment] = $this->createPendingOrderWithPayment(amount: 150000);

        // Attacker attempts payload claiming approval for only 1,000 COP
        $payload = json_encode([
            'event_id' => 'evt_attack_amt',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'amount' => 1000, // Mismatched low amount!
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);

        $signature = $this->fakeGateway->sign($payload);

        $response = $this->call('POST', route('webhooks.bold'), [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        // Payment and Order remain Pending
        $payment->refresh();
        $order->refresh();
        $this->assertSame(PaymentStatusEnum::Pending, $payment->status);
        $this->assertSame(OrderStatusEnum::Pending, $order->status);
    }

    public function test_currency_mismatch_attack_prevents_order_approval(): void
    {
        // Order is in COP
        [$order, $payment] = $this->createPendingOrderWithPayment(amount: 150000, currency: CurrencyEnum::Cop);

        // Attacker attempts payload specifying EUR
        $payload = json_encode([
            'event_id' => 'evt_attack_curr',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'amount' => 150000,
            'currency' => 'EUR', // Mismatched currency!
        ], JSON_THROW_ON_ERROR);

        $signature = $this->fakeGateway->sign($payload);

        $response = $this->call('POST', route('webhooks.bold'), [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        $payment->refresh();
        $order->refresh();
        $this->assertSame(PaymentStatusEnum::Pending, $payment->status);
        $this->assertSame(OrderStatusEnum::Pending, $order->status);
    }

    public function test_unmatched_payment_id_is_logged_and_ignored_gracefully(): void
    {
        $payload = json_encode([
            'event_id' => 'evt_non_existent',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => 999999, // Non existent ID
        ], JSON_THROW_ON_ERROR);

        $signature = $this->fakeGateway->sign($payload);

        $response = $this->call('POST', route('webhooks.bold'), [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        // Provider webhooks expect HTTP 200 ACK so they don't retry non-existent orders forever
        $response->assertStatus(200);
    }

    public function test_webhook_idempotency_prevents_double_processing_of_same_event_id(): void
    {
        [$order, $payment, $variant] = $this->createPendingOrderWithPayment(amount: 150000);

        $payload = json_encode([
            'event_id' => 'evt_unique_hash_999',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'amount' => 150000,
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);

        $signature = $this->fakeGateway->sign($payload);

        // First delivery
        $res1 = $this->call('POST', route('webhooks.bold'), [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $res1->assertStatus(200)
            ->assertSee('processed');

        $payment->refresh();
        $order->refresh();
        $variant->refresh();

        $this->assertSame(PaymentStatusEnum::Approved, $payment->status);
        $this->assertSame(OrderStatusEnum::Paid, $order->status);
        $this->assertSame(9, $variant->stock); // 10 - 1 = 9

        // Duplicate delivery (re-play attack or network retry)
        $res2 = $this->call('POST', route('webhooks.bold'), [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $res2->assertStatus(200)
            ->assertSee('duplicate');

        $variant->refresh();
        // Stock must remain 9
        $this->assertSame(9, $variant->stock);
    }
}
