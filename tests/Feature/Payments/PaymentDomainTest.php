<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use App\Actions\Payments\StartOrderPaymentAction;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\OrderNotPayableException;
use App\Exceptions\Payments\PaymentStockUnavailableException;
use App\Gateways\Payments\FakePaymentGateway;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PaymentDomainTest extends TestCase
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

    public function test_start_pay_user_order_eur_uses_stripe_and_amount_total(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 5);
        $order = $this->createPendingOrder(
            user: $user,
            currency: CurrencyEnum::Eur,
            total: 12_500,
            variant: $variant,
            quantity: 1,
        );

        $result = app(StartOrderPaymentAction::class)($order->id);

        $this->assertSame(PaymentProviderEnum::Stripe, $result->payment->provider);
        $this->assertSame(PaymentStatusEnum::Pending, $result->payment->status);
        $this->assertSame(12_500, $result->payment->amount);
        $this->assertSame(CurrencyEnum::Eur, $result->payment->currency);
        $this->assertNotNull($result->payment->external_id);
        $this->assertStringContainsString('https://payments.test/checkout/', $result->redirectUrl);
        $this->assertSame(OrderStatusEnum::Pending, $order->fresh()->status);
        $this->assertSame(5, (int) $variant->fresh()->stock);
        $this->assertCount(1, $this->fakeGateway->createdSessions);
    }

    public function test_start_pay_cop_uses_bold(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 3);
        $order = $this->createPendingOrder(
            user: $user,
            currency: CurrencyEnum::Cop,
            total: 100_000,
            variant: $variant,
            quantity: 1,
        );

        $result = app(StartOrderPaymentAction::class)($order->id);

        $this->assertSame(PaymentProviderEnum::Bold, $result->payment->provider);
        $this->assertSame(100_000, $result->payment->amount);
    }

    public function test_start_pay_rejects_non_pending_and_paid(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 2);
        $paid = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 1);
        $paid->update(['status' => OrderStatusEnum::Paid, 'paid_at' => now()]);

        $this->expectException(OrderNotPayableException::class);
        app(StartOrderPaymentAction::class)($paid->id);
    }

    public function test_start_pay_rejects_insufficient_stock_without_gateway_call(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 1);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 2);

        try {
            app(StartOrderPaymentAction::class)($order->id);
            $this->fail('Expected PaymentStockUnavailableException');
        } catch (PaymentStockUnavailableException) {
            $this->assertSame(0, Payment::query()->count());
            $this->assertCount(0, $this->fakeGateway->createdSessions);
        }
    }

    public function test_retry_creates_second_payment_while_pending(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 4);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 20_000, $variant, 1);

        app(StartOrderPaymentAction::class)($order->id);
        app(StartOrderPaymentAction::class)($order->id);

        $this->assertSame(2, Payment::query()->where('order_id', $order->id)->count());
        $this->assertCount(2, $this->fakeGateway->createdSessions);
    }

    public function test_webhook_approved_marks_paid_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 10);
        $order = $this->createPendingOrder($user, CurrencyEnum::Eur, 5_000, $variant, 3);
        $payment = Payment::factory()->stripe()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 5_000,
            'currency' => CurrencyEnum::Eur,
            'external_id' => 'fake_sess_1',
        ]);

        $payload = json_encode([
            'event_id' => 'evt_approved_1',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'external_id' => 'fake_sess_1',
            'payment_method' => 'card',
        ], JSON_THROW_ON_ERROR);

        $result = app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Stripe,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame('processed', $result['status']);
        $this->assertSame(PaymentStatusEnum::Approved, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(OrderStatusEnum::Paid, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame(7, (int) $variant->fresh()->stock);
        $this->assertNotNull(PaymentWebhookEvent::query()->where('event_id', 'evt_approved_1')->value('processed_at'));
    }

    public function test_webhook_redelivery_is_idempotent(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 5);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 2);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 10_000,
            'external_id' => 'fake_sess_2',
        ]);

        $payload = json_encode([
            'event_id' => 'evt_idem_1',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);
        $signature = $this->fakeGateway->sign($payload);
        $action = app(ProcessPaymentWebhookAction::class);

        $action(PaymentProviderEnum::Bold, $payload, $signature);
        $second = $action(PaymentProviderEnum::Bold, $payload, $signature);

        $this->assertSame('duplicate', $second['status']);
        $this->assertSame(1, PaymentWebhookEvent::query()->where('event_id', 'evt_idem_1')->count());
        $this->assertSame(3, (int) $variant->fresh()->stock);
        $this->assertSame(OrderStatusEnum::Paid, $order->fresh()->status);
    }

    public function test_webhook_approved_stock_fail_d25(): void
    {
        Log::spy();

        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 1);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 5);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 10_000,
        ]);

        // Bypass start revalidation: stock already insufficient at approve time.
        $payload = json_encode([
            'event_id' => 'evt_d25',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Bold,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame(PaymentStatusEnum::Approved, $payment->fresh()->status);
        $this->assertSame(OrderStatusEnum::Pending, $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertSame(1, (int) $variant->fresh()->stock);
        Log::shouldHaveReceived('error')->withArgs(fn (string $message): bool => $message === 'payments.webhook.stock_conflict_d25');
    }

    public function test_webhook_approved_on_cancelled_order_does_not_pay(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 4);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 1);
        $order->update(['status' => OrderStatusEnum::Cancelled]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 10_000,
        ]);

        $payload = json_encode([
            'event_id' => 'evt_cancelled',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Bold,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame(PaymentStatusEnum::Approved, $payment->fresh()->status);
        $this->assertSame(OrderStatusEnum::Cancelled, $order->fresh()->status);
        $this->assertSame(4, (int) $variant->fresh()->stock);
    }

    public function test_webhook_declined_keeps_order_pending(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 3);
        $order = $this->createPendingOrder($user, CurrencyEnum::Eur, 8_000, $variant, 1);
        $payment = Payment::factory()->stripe()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 8_000,
            'currency' => CurrencyEnum::Eur,
        ]);

        $payload = json_encode([
            'event_id' => 'evt_declined',
            'event_type' => 'fake.declined',
            'outcome' => 'declined',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Stripe,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame(PaymentStatusEnum::Declined, $payment->fresh()->status);
        $this->assertSame(OrderStatusEnum::Pending, $order->fresh()->status);
        $this->assertSame(3, (int) $variant->fresh()->stock);
    }

    public function test_webhook_refunded_marks_order_without_restoring_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 10);
        $order = $this->createPendingOrder($user, CurrencyEnum::Eur, 5_000, $variant, 2);
        $order->update(['status' => OrderStatusEnum::Paid, 'paid_at' => now()]);
        $variant->update(['stock' => 8]);
        $payment = Payment::factory()->stripe()->approved()->create([
            'order_id' => $order->id,
            'amount' => 5_000,
            'currency' => CurrencyEnum::Eur,
        ]);

        $payload = json_encode([
            'event_id' => 'evt_refund',
            'event_type' => 'fake.refunded',
            'outcome' => 'refunded',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Stripe,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame(PaymentStatusEnum::Refunded, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->refunded_at);
        $this->assertSame(OrderStatusEnum::Refunded, $order->fresh()->status);
        $this->assertSame(8, (int) $variant->fresh()->stock);
    }

    public function test_webhook_invalid_signature_throws(): void
    {
        $this->expectException(InvalidPaymentWebhookSignatureException::class);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Stripe,
            '{"event_id":"x","outcome":"approved"}',
            'not-a-valid-signature',
        );
    }

    public function test_already_paid_order_rejects_new_start(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 5);
        $order = $this->createPendingOrder($user, CurrencyEnum::Cop, 10_000, $variant, 1);
        $order->update(['status' => OrderStatusEnum::Paid, 'paid_at' => now()]);

        $this->expectException(OrderNotPayableException::class);
        app(StartOrderPaymentAction::class)($order->id);
    }

    private function createVariant(int $stock): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);

        return ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => $stock,
        ]);
    }

    private function createPendingOrder(
        User $user,
        CurrencyEnum $currency,
        int $total,
        ProductVariant $variant,
        int $quantity,
    ): Order {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
            'currency' => $currency,
            'subtotal' => $total,
            'shipping_cost' => 0,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => $total,
            'paid_at' => null,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test product',
            'variant_label' => 'Default',
            'sku' => $variant->sku,
            'unit_price' => intdiv($total, max(1, $quantity)),
            'quantity' => $quantity,
        ]);

        return $order->fresh(['items']) ?? $order;
    }
}
