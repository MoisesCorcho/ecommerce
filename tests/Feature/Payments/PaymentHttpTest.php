<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Gateways\Payments\FakePaymentGateway;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class PaymentHttpTest extends TestCase
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

    public function test_owner_can_post_pay_and_redirects_to_hosted_checkout(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(5);
        $order = $this->createOrder($user, CurrencyEnum::Eur, 9_900, $variant, 1);

        $response = $this->actingAs($user)
            ->post(route('orders.pay', $order));

        $response->assertRedirect();
        $this->assertStringContainsString('https://payments.test/checkout/', $response->headers->get('Location') ?? '');
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Stripe->value,
            'status' => PaymentStatusEnum::Pending->value,
            'amount' => 9_900,
        ]);
    }

    public function test_guest_can_pay_with_signed_url(): void
    {
        $variant = $this->createVariant(3);
        $order = Order::factory()->create([
            'user_id' => null,
            'status' => OrderStatusEnum::Pending,
            'currency' => CurrencyEnum::Cop,
            'total' => 50_000,
            'subtotal' => 50_000,
            'shipping_cost' => 0,
            'discount' => 0,
            'tax_amount' => 0,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 50_000,
        ]);

        $signed = URL::temporarySignedRoute(
            'orders.pay',
            now()->addHour(),
            ['order' => $order->id],
        );

        $this->post($signed)
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Bold->value,
            'amount' => 50_000,
        ]);
    }

    public function test_foreign_user_cannot_pay(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $variant = $this->createVariant(2);
        $order = $this->createOrder($owner, CurrencyEnum::Cop, 10_000, $variant, 1);

        $this->actingAs($other)
            ->post(route('orders.pay', $order))
            ->assertForbidden();

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_guest_without_signature_cannot_pay(): void
    {
        $variant = $this->createVariant(2);
        $order = Order::factory()->create([
            'user_id' => null,
            'status' => OrderStatusEnum::Pending,
            'currency' => CurrencyEnum::Cop,
            'total' => 10_000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 10_000,
        ]);

        $this->post(route('orders.pay', $order))
            ->assertForbidden();
    }

    public function test_thank_you_shows_pay_button_for_pending_owner(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('orders.thank-you', $order))
            ->assertOk()
            ->assertSee(__('payments.actions.pay'), false)
            ->assertSee('data-pay-button', false);
    }

    public function test_thank_you_processing_message_does_not_mark_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $url = URL::temporarySignedRoute(
            'orders.thank-you',
            now()->addHour(),
            ['order' => $order->id, 'payment' => 'processing'],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee(__('payments.return.processing'), false);

        $this->assertSame(OrderStatusEnum::Pending, $order->fresh()->status);
    }

    public function test_thank_you_shows_payment_error_flash_banner(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $this->actingAs($user)
            ->withSession(['payment_error' => 'The payment provider returned an error.'])
            ->get(route('orders.thank-you', $order))
            ->assertOk()
            ->assertSee('The payment provider returned an error.', false)
            ->assertSee('data-payment-error', false);
    }

    public function test_thank_you_cancel_message_allows_retry_copy(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $url = URL::temporarySignedRoute(
            'orders.thank-you',
            now()->addHour(),
            ['order' => $order->id, 'payment' => 'cancelled'],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee(__('payments.return.cancelled'), false)
            ->assertSee(__('payments.actions.retry'), false);
    }

    public function test_webhook_stripe_endpoint_processes_with_valid_signature(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(4);
        $order = $this->createOrder($user, CurrencyEnum::Eur, 3_000, $variant, 1);
        $payment = Payment::factory()->stripe()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 3_000,
            'currency' => CurrencyEnum::Eur,
        ]);

        $payload = json_encode([
            'event_id' => 'evt_http_1',
            'event_type' => 'fake.approved',
            'outcome' => 'approved',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Stripe-Signature' => $this->fakeGateway->sign($payload),
            ],
            $payload,
        )->assertOk();

        $this->assertSame(OrderStatusEnum::Paid, $order->fresh()->status);
        $this->assertSame(3, (int) $variant->fresh()->stock);
    }

    public function test_webhook_invalid_signature_returns_400(): void
    {
        $paymentsLog = \Mockery::mock(LoggerInterface::class);
        $paymentsLog->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context = []): bool {
                return $message === 'payments.webhook.invalid_signature'
                    && ($context['provider'] ?? null) === PaymentProviderEnum::Bold->value;
            });
        Log::shouldReceive('channel')->with('payments')->andReturn($paymentsLog);

        $this->call(
            'POST',
            route('webhooks.bold'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_x-bold-signature' => 'bad',
            ],
            '{"event_id":"x","outcome":"approved"}',
        )->assertStatus(400);
    }

    private function createVariant(int $stock): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);

        return ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => $stock,
        ]);
    }

    private function createOrder(
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
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'HTTP product',
            'quantity' => $quantity,
            'unit_price' => $total,
        ]);

        return $order;
    }
}
