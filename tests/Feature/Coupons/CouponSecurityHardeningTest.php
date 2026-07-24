<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Payments\StartOrderPaymentAction;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Gateways\Payments\FakePaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Coupons\CouponAttemptRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * F06 security hardening P2: pay amount = discounted total, cancel×approved payment, rate-limit.
 */
class CouponSecurityHardeningTest extends TestCase
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

    public function test_start_pay_uses_post_discount_order_total(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);

        Coupon::factory()->percentage(20)->unlimited()->create(['code' => 'PAY20']);

        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 100_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            userId: (int) $user->id,
            email: $user->email,
            couponCode: 'PAY20',
        ));

        // subtotal 100_000 - 20% = 80_000 + shipping 5_000 = 85_000
        $this->assertSame(100_000, (int) $order->subtotal);
        $this->assertSame(20_000, (int) $order->discount);
        $this->assertSame(5_000, (int) $order->shipping_cost);
        $this->assertSame(85_000, (int) $order->total);

        $result = app(StartOrderPaymentAction::class)((int) $order->id);

        $this->assertSame(85_000, (int) $result->payment->amount);
        $this->assertSame((int) $order->total, (int) $result->payment->amount);
        $this->assertSame(CurrencyEnum::Cop, $result->payment->currency);
        $this->assertSame(PaymentStatusEnum::Pending, $result->payment->status);
        $this->assertNotSame((int) $order->subtotal, (int) $result->payment->amount);
    }

    public function test_cancel_pending_blocked_when_payment_is_approved(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()->percentage(10)->unlimited()->create([
            'code' => 'D25HOLD',
            'used_count' => 0,
        ]);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: 'D25HOLD',
        ));

        $this->assertSame(1, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 1);

        // D25-like: payment approved while order still pending.
        Payment::factory()->approved()->create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        try {
            app(CancelOrderAction::class)((int) $order->id);
            $this->fail('Expected OrderCannotBeCancelledException');
        } catch (OrderCannotBeCancelledException $e) {
            $this->assertStringContainsString(
                __('orders.errors.cannot_cancel_payment_captured'),
                $e->getMessage(),
            );
        }

        $this->assertSame(OrderStatusEnum::Pending, $order->fresh()->status);
        $this->assertSame(1, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 1);
    }

    public function test_cancel_pending_still_allowed_with_only_pending_payment(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()->percentage(10)->unlimited()->create([
            'code' => 'PENDPAY',
            'used_count' => 0,
        ]);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 40_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: 'PENDPAY',
        ));

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        app(CancelOrderAction::class)((int) $order->id);

        $this->assertSame(OrderStatusEnum::Cancelled, $order->fresh()->status);
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    public function test_cancel_pending_blocked_when_payment_is_refunded(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'REFBLOCK']);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 30_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: 'REFBLOCK',
        ));

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Refunded,
            'amount' => $order->total,
            'currency' => $order->currency,
            'refunded_at' => now(),
        ]);

        $this->expectException(OrderCannotBeCancelledException::class);

        app(CancelOrderAction::class)((int) $order->id);
    }

    public function test_coupon_attempt_rate_limiter_blocks_after_max_attempts(): void
    {
        $limiter = app(CouponAttemptRateLimiter::class);
        $ip = '203.0.113.77';

        $limiter->clear(userId: null, ip: $ip);

        for ($i = 0; $i < CouponAttemptRateLimiter::MAX_ATTEMPTS; $i++) {
            $this->assertTrue(
                $limiter->attempt(userId: null, ip: $ip),
                "Attempt {$i} should be allowed",
            );
        }

        $this->assertFalse($limiter->attempt(userId: null, ip: $ip));

        // Authenticated users use a separate bucket.
        $this->assertTrue($limiter->attempt(userId: 42, ip: $ip));

        $limiter->clear(userId: null, ip: $ip);
        $limiter->clear(userId: 42, ip: $ip);
    }

    private function createOrderDto(
        Cart $cart,
        ?int $userId = null,
        ?string $sessionId = null,
        string $email = 'buyer@example.com',
        ?string $couponCode = null,
    ): CreateOrderFromCartDTO {
        return new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            contact: new CheckoutContactDTO(
                firstName: 'Ada',
                lastName: 'Lovelace',
                email: $email,
                phone: '+573009998877',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Ada Lovelace',
                phone: '+573009998877',
                addressLine1: 'Calle One Shot 123',
                addressLine2: null,
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110111',
            ),
            userId: $userId,
            sessionId: $sessionId,
            couponCode: $couponCode,
        );
    }

    private function createEligibleVariant(int $stock, int $copPrice, string $sku = 'SKU-S'): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => $sku.uniqid(),
            'stock' => $stock,
            'is_active' => true,
        ]);
        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => $copPrice]);

        return $variant;
    }
}
