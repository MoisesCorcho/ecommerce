<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Support\Cart\CartSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Checkout Livewire entrypoint with couponCode (R3, R4, D44).
 */
class CouponCheckoutLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_with_coupon_code_applies_discount_and_consumes(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);

        Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'LIVE10']);

        $sessionId = 'guest-coupon-checkout';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        Livewire::test('checkout-page')
            ->assertSet('preview.subtotal', 100_000)
            ->assertSet('preview.discount', 0)
            ->set('couponCode', 'live10')
            ->call('applyCoupon')
            ->assertSet('preview.discount', 10_000)
            ->assertSet('preview.shippingCost', 5_000)
            ->assertSet('preview.total', 95_000)
            ->set('firstName', 'Grace')
            ->set('lastName', 'Hopper')
            ->set('email', 'grace@example.com')
            ->set('phone', '+573001234567')
            ->set('addressMode', 'one_shot')
            ->set('shippingFullName', 'Grace Hopper')
            ->set('shippingPhone', '+573001234567')
            ->set('shippingAddressLine1', 'Av 1 #2-3')
            ->set('shippingCity', 'Cali')
            ->set('shippingState', 'Valle')
            ->set('shippingCountry', 'CO')
            ->call('confirm')
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertSame(10_000, $order->discount);
        $this->assertSame(95_000, $order->total);
        $this->assertNotNull($order->coupon_id);

        $this->assertDatabaseCount('coupon_redemptions', 1);
        $redemption = CouponRedemption::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($redemption);
        $this->assertSame('LIVE10', $redemption->code);
        $this->assertNull($redemption->user_id);

        $this->assertSame(1, Coupon::query()->where('code', 'LIVE10')->value('used_count'));
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
        // Preview must not have consumed before confirm — used_count ends at 1 only.
        $this->assertSame(5, (int) $variant->fresh()->stock);
    }

    public function test_checkout_invalid_coupon_on_confirm_keeps_cart_and_shows_error(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        Coupon::factory()->percentage(10)->unlimited()->inactive()->create(['code' => 'BADLIVE']);

        $sessionId = 'guest-bad-coupon';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 30_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('couponCode', 'BADLIVE')
            ->set('firstName', 'Bad')
            ->set('lastName', 'Code')
            ->set('email', 'bad@example.com')
            ->set('phone', '+573009998877')
            ->set('addressMode', 'one_shot')
            ->set('shippingFullName', 'Bad Code')
            ->set('shippingPhone', '+573009998877')
            ->set('shippingAddressLine1', 'Calle X')
            ->set('shippingCity', 'Bogotá')
            ->set('shippingState', 'Cundinamarca')
            ->set('shippingCountry', 'CO')
            ->call('confirm')
            ->assertSet('errorMessage', __('coupons.errors.invalid'))
            ->assertNoRedirect();

        $this->assertSame(0, Order::query()->count());
        $this->assertDatabaseCount('coupon_redemptions', 0);
        $this->assertSame(0, Coupon::query()->where('code', 'BADLIVE')->value('used_count'));
        $this->assertSame(1, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    public function test_apply_coupon_on_preview_does_not_write_redemption(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        Coupon::factory()->percentage(25)->unlimited()->create(['code' => 'PREVIEWLW']);

        $sessionId = 'guest-preview-only';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 80_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('couponCode', 'PREVIEWLW')
            ->call('applyCoupon')
            ->assertSet('preview.discount', 20_000)
            ->assertSet('preview.total', 60_000);

        $this->assertDatabaseCount('coupon_redemptions', 0);
        $this->assertSame(0, Coupon::query()->where('code', 'PREVIEWLW')->value('used_count'));
        $this->assertSame(0, Order::query()->count());
    }

    private function createEligibleVariant(int $stock, int $copPrice): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => $copPrice]);

        return $variant;
    }
}
