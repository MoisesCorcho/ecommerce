<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Orders\ValidateCartForCheckoutAction;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponRejectionReasonEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Services\Coupons\CouponPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Checkout preview/create/cancel coupon integration (R3–R5, R9, R16–R17, R20, R23).
 */
class CouponCheckoutDomainTest extends TestCase
{
    use RefreshDatabase;

    // ─── 5.3 Preview does not consume ────────────────────────────────────────

    public function test_preview_with_valid_coupon_returns_discount_without_redemption(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);

        Coupon::factory()->percentage(20)->unlimited()->create(['code' => 'PREVIEW20']);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
            'preview20',
        );

        $this->assertSame(100_000, $preview->subtotal);
        $this->assertSame(20_000, $preview->discount);
        $this->assertSame(5_000, $preview->shippingCost);
        $this->assertSame(0, $preview->taxAmount);
        $this->assertSame(85_000, $preview->total);
        $this->assertDatabaseCount('coupon_redemptions', 0);
        $this->assertSame(0, Coupon::query()->where('code', 'PREVIEW20')->value('used_count'));
        $this->assertSame(0, Order::query()->count());
    }

    public function test_preview_with_one_hundred_percent_still_charges_shipping(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 7_500);

        Coupon::factory()->percentage(100)->unlimited()->create(['code' => 'SHIPONLY']);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 40_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
            'SHIPONLY',
        );

        $this->assertSame(40_000, $preview->discount);
        $this->assertSame(7_500, $preview->shippingCost);
        $this->assertSame(7_500, $preview->total);
    }

    // ─── 5.4 Create order user + guest ───────────────────────────────────────

    public function test_create_order_for_user_with_coupon_writes_redemption_and_used_count(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);

        $user = User::factory()->create();
        $coupon = Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'ORDER10']);

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
            couponCode: 'order10',
        ));

        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(10_000, $order->discount);
        $this->assertSame(100_000, $order->subtotal);
        $this->assertSame(5_000, $order->shipping_cost);
        $this->assertSame(95_000, $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());

        $redemption = CouponRedemption::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($redemption);
        $this->assertSame('ORDER10', $redemption->code);
        $this->assertSame($user->id, $redemption->user_id);
        $this->assertSame(10_000, $redemption->discount_amount);
        $this->assertSame(CurrencyEnum::Cop, $redemption->currency);
        $this->assertSame($coupon->id, $redemption->coupon_id);
    }

    public function test_create_order_for_guest_with_coupon_has_null_user_on_redemption(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()->fixed(CurrencyEnum::Cop)->unlimited()->create([
            'code' => 'GUEST5K',
            'value' => 5_000,
        ]);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 25_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            email: 'guest@example.com',
            couponCode: 'GUEST5K',
        ));

        $this->assertNull($order->user_id);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(5_000, $order->discount);
        $this->assertSame(20_000, $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);

        $redemption = CouponRedemption::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($redemption);
        $this->assertNull($redemption->user_id);
        $this->assertSame('GUEST5K', $redemption->code);
    }

    // ─── 5.5 Create without code ─────────────────────────────────────────────

    public function test_create_order_without_coupon_keeps_discount_zero_and_no_redemption(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 20_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            email: 'guest@example.com',
        ));

        $this->assertNull($order->coupon_id);
        $this->assertSame(0, $order->discount);
        $this->assertSame(20_000, $order->total);
        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    public function test_create_order_with_blank_coupon_code_is_treated_as_no_coupon(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 15_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: '   ',
        ));

        $this->assertNull($order->coupon_id);
        $this->assertSame(0, $order->discount);
        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    // ─── 5.6 Global usage limit + lock path ──────────────────────────────────

    public function test_global_usage_limit_exhausted_rejects_confirm(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->withUsageLimit(1)
            ->create([
                'code' => 'ONCE',
                'usage_limit_per_user' => null,
                'used_count' => 0,
            ]);

        $firstCart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 50_000);
        CartItem::factory()->for($firstCart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $firstOrder = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $firstCart,
            sessionId: $firstCart->session_id,
            couponCode: 'ONCE',
        ));

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertNotNull($firstOrder->coupon_id);

        $secondCart = Cart::factory()->guest()->create();
        CartItem::factory()->for($secondCart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        try {
            app(CreateOrderFromCartAction::class)($this->createOrderDto(
                cart: $secondCart,
                sessionId: $secondCart->session_id,
                couponCode: 'ONCE',
            ));
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::UsageExhausted, $e->reason);
        }

        $this->assertSame(1, Order::query()->count());
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(1, CartItem::query()->where('cart_id', $secondCart->id)->count());
    }

    public function test_quote_with_for_update_inside_transaction_sees_exhausted_limit(): void
    {
        $coupon = Coupon::factory()
            ->percentage(10)
            ->withUsageLimit(1)
            ->create([
                'code' => 'LOCK1',
                'usage_limit_per_user' => null,
                'used_count' => 1,
            ]);

        DB::transaction(function () use ($coupon): void {
            try {
                app(CouponPricingService::class)->quote(
                    code: 'LOCK1',
                    subtotal: 10_000,
                    currency: CurrencyEnum::Cop,
                    forUpdate: true,
                );
                $this->fail('Expected InvalidCouponException under lock');
            } catch (InvalidCouponException $e) {
                $this->assertSame(CouponRejectionReasonEnum::UsageExhausted, $e->reason);
            }

            $this->assertSame(1, $coupon->fresh()->used_count);
        });
    }

    // ─── 5.7 Per-user limit ──────────────────────────────────────────────────

    public function test_per_user_limit_blocks_authenticated_user(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $user = User::factory()->create();
        $coupon = Coupon::factory()
            ->percentage(10)
            ->unlimited()
            ->withPerUserLimit(1)
            ->create(['code' => 'PERUSER1']);

        $firstCart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 40_000);
        CartItem::factory()->for($firstCart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $firstCart,
            userId: (int) $user->id,
            email: $user->email,
            couponCode: 'PERUSER1',
        ));

        $this->assertSame(1, $coupon->fresh()->used_count);

        $secondCart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        CartItem::factory()->for($secondCart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        try {
            app(CreateOrderFromCartAction::class)($this->createOrderDto(
                cart: $secondCart,
                userId: (int) $user->id,
                email: $user->email,
                couponCode: 'PERUSER1',
            ));
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::PerUserExhausted, $e->reason);
        }

        $this->assertSame(1, CouponRedemption::query()->where('coupon_id', $coupon->id)->count());
        $this->assertSame(1, CartItem::query()->where('cart_id', $secondCart->id)->count());
    }

    public function test_guest_does_not_use_per_user_limit(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->withUsageLimit(5)
            ->withPerUserLimit(1)
            ->create(['code' => 'GUESTOK']);

        $variant = $this->createEligibleVariant(stock: 10, copPrice: 30_000);

        foreach (['a', 'b'] as $suffix) {
            $cart = Cart::factory()->guest()->create();
            CartItem::factory()->for($cart)->create([
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ]);

            $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
                cart: $cart,
                sessionId: $cart->session_id,
                email: "guest-{$suffix}@example.com",
                couponCode: 'GUESTOK',
            ));

            $this->assertSame($coupon->id, $order->coupon_id);
            $redemption = CouponRedemption::query()->where('order_id', $order->id)->first();
            $this->assertNotNull($redemption);
            $this->assertNull($redemption->user_id);
        }

        $this->assertSame(2, $coupon->fresh()->used_count);
        $this->assertSame(2, CouponRedemption::query()->where('coupon_id', $coupon->id)->count());
    }

    // ─── 5.8 Invalid coupon on confirm: no order, cart intact, no consume ────

    public function test_confirm_with_invalid_coupon_does_not_create_order_or_clear_cart(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        Coupon::factory()->percentage(10)->unlimited()->inactive()->create(['code' => 'DEAD']);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 4, copPrice: 22_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        try {
            app(CreateOrderFromCartAction::class)($this->createOrderDto(
                cart: $cart,
                sessionId: $cart->session_id,
                couponCode: 'DEAD',
            ));
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::Inactive, $e->reason);
        }

        $this->assertSame(0, Order::query()->count());
        $this->assertDatabaseCount('coupon_redemptions', 0);
        $this->assertSame(0, Coupon::query()->where('code', 'DEAD')->value('used_count'));
        $this->assertSame(1, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    public function test_confirm_with_unknown_coupon_does_not_consume(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->expectException(InvalidCouponException::class);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: 'MISSING',
        ));
    }

    // ─── 5.9 Cancel pending releases redemption ──────────────────────────────

    public function test_cancel_pending_releases_redemption_and_decrements_used_count(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()->percentage(50)->unlimited()->create([
            'code' => 'CANCEL50',
            'used_count' => 0,
        ]);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 40_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            couponCode: 'CANCEL50',
        ));

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 1);

        app(CancelOrderAction::class)((int) $order->id);

        $this->assertSame(OrderStatusEnum::Cancelled, $order->fresh()->status);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('coupon_redemptions', 0);
    }

    public function test_cancel_releases_usage_so_coupon_can_be_reused(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 0);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->withUsageLimit(1)
            ->create([
                'code' => 'REUSE1',
                'usage_limit_per_user' => null,
            ]);

        $variant = $this->createEligibleVariant(stock: 5, copPrice: 50_000);

        $cart1 = Cart::factory()->guest()->create();
        CartItem::factory()->for($cart1)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart1,
            sessionId: $cart1->session_id,
            couponCode: 'REUSE1',
        ));

        app(CancelOrderAction::class)((int) $order->id);
        $this->assertSame(0, $coupon->fresh()->used_count);

        $cart2 = Cart::factory()->guest()->create();
        CartItem::factory()->for($cart2)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $second = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart2,
            sessionId: $cart2->session_id,
            couponCode: 'REUSE1',
        ));

        $this->assertSame($coupon->id, $second->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
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

    private function createEligibleVariant(int $stock, int $copPrice, string $sku = 'SKU-C'): ProductVariant
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
