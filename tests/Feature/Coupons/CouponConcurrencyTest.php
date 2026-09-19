<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderFromCartAction;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponRejectionReasonEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function createCartWithProduct(User $user): Cart
    {
        $product = Product::factory()->create(['is_active' => true]);
        /** @var ProductVariant $variant */
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'is_active' => true,
        ]);
        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 50000,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Cop,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        return $cart;
    }

    private function makeDTO(Cart $cart, User $user, ?string $couponCode): CreateOrderFromCartDTO
    {
        return new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            contact: new CheckoutContactDTO(
                firstName: 'Test',
                lastName: 'User',
                email: $user->email,
                phone: '+573001234567',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Test User',
                phone: '+573001234567',
                addressLine1: 'Carrera 7 #123',
                addressLine2: null,
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110111',
                addressId: null,
            ),
            userId: (int) $user->id,
            couponCode: $couponCode,
        );
    }

    public function test_coupon_max_uses_prevents_over_redemption(): void
    {
        // Coupon with usage_limit = 1
        $coupon = Coupon::factory()->create([
            'code' => 'LIMITED1',
            'type' => CouponTypeEnum::Percentage,
            'value' => 10,
            'usage_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $user1 = User::factory()->create();
        $cart1 = $this->createCartWithProduct($user1);

        $user2 = User::factory()->create();
        $cart2 = $this->createCartWithProduct($user2);

        $action = app(CreateOrderFromCartAction::class);

        // User 1 claims the coupon during checkout
        $dto1 = $this->makeDTO($cart1, $user1, 'LIMITED1');
        $order1 = $action($dto1);
        $this->assertNotNull($order1);

        $coupon->refresh();
        $this->assertSame(1, $coupon->used_count);

        // User 2 attempts to claim the same coupon (now at limit)
        $dto2 = $this->makeDTO($cart2, $user2, 'LIMITED1');

        try {
            $action($dto2);
            $this->fail('Expected InvalidCouponException was not thrown.');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::UsageExhausted, $e->reason);
        }

        // Verify coupon used_count remains 1
        $coupon->refresh();
        $this->assertSame(1, $coupon->used_count);
    }

    public function test_coupon_max_uses_per_user_prevents_duplicate_redemption_for_same_user(): void
    {
        // Coupon with usage_limit_per_user = 1 and usage_limit = 10
        $coupon = Coupon::factory()->create([
            'code' => 'PERUSER1',
            'type' => CouponTypeEnum::Percentage,
            'value' => 15,
            'usage_limit' => 10,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cart1 = $this->createCartWithProduct($user);

        $action = app(CreateOrderFromCartAction::class);

        // First checkout by user succeeds
        $dto1 = $this->makeDTO($cart1, $user, 'PERUSER1');
        $order1 = $action($dto1);
        $this->assertNotNull($order1);

        // Second checkout by same user with another cart
        $cart2 = $this->createCartWithProduct($user);
        $dto2 = $this->makeDTO($cart2, $user, 'PERUSER1');

        try {
            $action($dto2);
            $this->fail('Expected InvalidCouponException was not thrown.');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::PerUserExhausted, $e->reason);
        }

        $coupon->refresh();
        $this->assertSame(1, $coupon->used_count);
    }

    public function test_cancel_order_releases_coupon_redemption_and_decrements_used_count(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'RECOVER1',
            'type' => CouponTypeEnum::Percentage,
            'value' => 20,
            'usage_limit' => 5,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cart = $this->createCartWithProduct($user);

        $createAction = app(CreateOrderFromCartAction::class);

        $order = $createAction($this->makeDTO($cart, $user, 'RECOVER1'));

        $coupon->refresh();
        $this->assertSame(1, $coupon->used_count);
        $this->assertNotNull($order->couponRedemption);

        // Cancel order
        $cancelAction = app(CancelOrderAction::class);
        $cancelAction($order->id);

        $order->refresh();
        $coupon->refresh();

        $this->assertSame(OrderStatusEnum::Cancelled, $order->status);
        $this->assertSame(0, $coupon->used_count);
        $this->assertNull($order->fresh()->couponRedemption);
    }
}
