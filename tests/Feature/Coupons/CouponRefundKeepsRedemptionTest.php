<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Payments\ProcessPaymentWebhookAction;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Gateways\Payments\FakePaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * R10 / D38: refund does not release coupon redemption.
 */
class CouponRefundKeepsRedemptionTest extends TestCase
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

    public function test_refund_path_keeps_redemption_and_used_count(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $user = User::factory()->create();
        $coupon = Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'KEEP10']);

        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 100_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)(new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            contact: new CheckoutContactDTO(
                firstName: 'Ada',
                lastName: 'Lovelace',
                email: $user->email,
                phone: '+573001112233',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Ada Lovelace',
                phone: '+573001112233',
                addressLine1: 'Calle 1',
                addressLine2: null,
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110111',
            ),
            userId: (int) $user->id,
            couponCode: 'KEEP10',
        ));

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 1);

        // Simulate paid order + approved payment (stock already decremented as F05 would).
        $order->update([
            'status' => OrderStatusEnum::Paid,
            'paid_at' => now(),
        ]);
        $variant->update(['stock' => 4]);

        $payment = Payment::factory()->approved()->create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'currency' => CurrencyEnum::Cop,
            'provider' => PaymentProviderEnum::Bold,
        ]);

        $payload = json_encode([
            'event_id' => 'evt_coupon_refund',
            'event_type' => 'fake.refunded',
            'outcome' => 'refunded',
            'payment_id' => $payment->id,
        ], JSON_THROW_ON_ERROR);

        app(ProcessPaymentWebhookAction::class)(
            PaymentProviderEnum::Bold,
            $payload,
            $this->fakeGateway->sign($payload),
        );

        $this->assertSame(PaymentStatusEnum::Refunded, $payment->fresh()->status);
        $this->assertSame(OrderStatusEnum::Refunded, $order->fresh()->status);

        // R10: redemption remains; used_count not decremented.
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 1);
        $this->assertTrue(
            CouponRedemption::query()->where('order_id', $order->id)->where('code', 'KEEP10')->exists()
        );
        $this->assertSame(4, (int) $variant->fresh()->stock);
    }

    private function createEligibleVariant(int $stock, int $copPrice): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'SKU-R'.uniqid(),
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
