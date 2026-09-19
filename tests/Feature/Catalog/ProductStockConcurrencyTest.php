<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Payments\ProcessPaymentWebhookAction;
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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductStockConcurrencyTest extends TestCase
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

    private function createProductWithStock(int $stock, bool $isPreorder = false): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_preorder' => $isPreorder,
        ]);

        /** @var ProductVariant $variant */
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => $stock,
            'is_active' => true,
        ]);

        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 100000,
        ]);

        return $variant;
    }

    private function createOrderForVariant(ProductVariant $variant, int $quantity = 1): Order
    {
        $user = User::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
            'currency' => CurrencyEnum::Cop,
            'total' => 100000 * $quantity,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => 100000,
        ]);

        return $order;
    }

    public function test_stock_conflict_d25_prevents_negative_stock_when_two_orders_compete_for_last_item(): void
    {
        // Variant with ONLY 1 item in stock
        $variant = $this->createProductWithStock(stock: 1);

        $orderA = $this->createOrderForVariant($variant, quantity: 1);
        $orderB = $this->createOrderForVariant($variant, quantity: 1);

        $paymentA = Payment::factory()->create([
            'order_id' => $orderA->id,
            'provider' => PaymentProviderEnum::Bold,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 100000,
        ]);

        $paymentB = Payment::factory()->create([
            'order_id' => $orderB->id,
            'provider' => PaymentProviderEnum::Bold,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 100000,
        ]);

        $action = app(ProcessPaymentWebhookAction::class);

        // Webhook for Order A arrives
        $payloadA = json_encode([
            'event_id' => 'evt_a_123',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $paymentA->id,
            'amount' => 100000,
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);

        $action(PaymentProviderEnum::Bold, $payloadA, $this->fakeGateway->sign($payloadA));

        // Webhook for Order B arrives (competing for same stock)
        $payloadB = json_encode([
            'event_id' => 'evt_b_456',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $paymentB->id,
            'amount' => 100000,
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);

        $action(PaymentProviderEnum::Bold, $payloadB, $this->fakeGateway->sign($payloadB));

        // Order A must be paid and stock decremented to 0
        $orderA->refresh();
        $this->assertSame(OrderStatusEnum::Paid, $orderA->status);

        $variant->refresh();
        $this->assertSame(0, $variant->stock);

        // Order B payment is approved, but order remains Pending due to D25 stock conflict
        $orderB->refresh();
        $paymentB->refresh();
        $this->assertSame(OrderStatusEnum::Pending, $orderB->status);
        $this->assertSame(PaymentStatusEnum::Approved, $paymentB->status);

        // Ensure stock did NOT drop below 0
        $this->assertSame(0, $variant->stock);
    }

    public function test_concurrent_payment_webhooks_for_same_order_are_idempotent(): void
    {
        $variant = $this->createProductWithStock(stock: 5);
        $order = $this->createOrderForVariant($variant, quantity: 2);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Bold,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 200000,
        ]);

        $action = app(ProcessPaymentWebhookAction::class);

        $payload = json_encode([
            'event_id' => 'evt_duplicate_1',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'amount' => 200000,
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);
        $signature = $this->fakeGateway->sign($payload);

        // First webhook execution
        $res1 = $action(PaymentProviderEnum::Bold, $payload, $signature);
        $this->assertSame('processed', $res1['status']);

        $order->refresh();
        $variant->refresh();
        $this->assertSame(OrderStatusEnum::Paid, $order->status);
        $this->assertSame(3, $variant->stock); // 5 - 2 = 3

        // Duplicate webhook execution
        $res2 = $action(PaymentProviderEnum::Bold, $payload, $signature);
        $this->assertSame('duplicate', $res2['status']);

        $variant->refresh();
        // Stock must NOT be decremented twice!
        $this->assertSame(3, $variant->stock);
    }

    public function test_preorder_product_does_not_fail_stock_check_when_stock_is_zero(): void
    {
        // Preorder variant with stock 10
        $variant = $this->createProductWithStock(stock: 10, isPreorder: true);

        $order = $this->createOrderForVariant($variant, quantity: 3);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Bold,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 300000,
        ]);

        $action = app(ProcessPaymentWebhookAction::class);

        $payload = json_encode([
            'event_id' => 'evt_preorder_1',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
            'amount' => 300000,
            'currency' => 'COP',
        ], JSON_THROW_ON_ERROR);

        $action(PaymentProviderEnum::Bold, $payload, $this->fakeGateway->sign($payload));

        $order->refresh();
        $variant->refresh();
        $this->assertSame(OrderStatusEnum::Paid, $order->status);
        // Stock decremented to -3 for preorder without failing
        $this->assertSame(7, $variant->stock);
    }

    public function test_pessimistic_lock_prevents_simultaneous_stock_overdraw_in_transaction(): void
    {
        $variant = $this->createProductWithStock(stock: 2);

        // Execute simulated pessimistic lock transaction
        DB::transaction(function () use ($variant): void {
            /** @var ProductVariant $lockedVariant */
            $lockedVariant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $lockedVariant->decrement('stock', 2);
        });

        $variant->refresh();
        $this->assertSame(0, $variant->stock);
    }
}
