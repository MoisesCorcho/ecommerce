<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Orders\ValidateCartForCheckoutAction;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderThresholdDiscountTest extends TestCase
{
    use RefreshDatabase;

    private ValidateCartForCheckoutAction $validateAction;

    private CreateOrderFromCartAction $createOrderAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validateAction = app(ValidateCartForCheckoutAction::class);
        $this->createOrderAction = app(CreateOrderFromCartAction::class);
    }

    public function test_validate_cart_for_checkout_calculates_threshold_discount(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // 2 items of 150 EUR = 300 EUR (30.000 cents) -> 10% threshold discount = 3.000 cents
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Eur,
            'price' => 15_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $owner = new CartOwnerDTO(userId: $user->id, sessionId: null);
        $preview = ($this->validateAction)((int) $cart->id, $owner);

        $this->assertSame(30_000, $preview->subtotal);
        $this->assertSame(3_000, $preview->thresholdDiscount);
        $this->assertSame(0, $preview->discount);
        $this->assertSame(27_000 + $preview->shippingCost, $preview->total);
    }

    public function test_validate_cart_for_checkout_cascades_coupon_discount_over_threshold_net(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // Subtotal: 300 EUR (30.000 cents) -> Threshold 10% = 3.000 cents (Net = 27.000 cents)
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Eur,
            'price' => 15_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Coupon: 20% discount
        $coupon = Coupon::factory()->create([
            'code' => 'SAVE20',
            'type' => CouponTypeEnum::Percentage,
            'value' => 20,
            'is_active' => true,
        ]);

        $owner = new CartOwnerDTO(userId: $user->id, sessionId: null);
        $preview = ($this->validateAction)((int) $cart->id, $owner, 'SAVE20');

        $this->assertSame(30_000, $preview->subtotal);
        $this->assertSame(3_000, $preview->thresholdDiscount);
        // 20% on remaining 27.000 cents = 5.400 cents
        $this->assertSame(5_400, $preview->discount);
        // Total = 30.000 - 3.000 - 5.400 = 21.600 cents + shipping
        $this->assertSame(21_600 + $preview->shippingCost, $preview->total);
    }

    public function test_create_order_from_cart_freezes_threshold_discount_in_order_record(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Cop,
        ]);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // Price: 750.000 COP, quantity 2 = 1.500.000 COP -> Threshold 10% = 150.000 COP
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Cop,
            'price' => 750_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $dto = new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            contact: new CheckoutContactDTO(
                firstName: 'Ana',
                lastName: 'Gómez',
                email: 'ana@example.com',
                phone: '+573001112233',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Ana Gómez',
                phone: '+573001112233',
                addressLine1: 'Calle 100 #15-20',
                addressLine2: 'Apto 301',
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110111',
                addressId: null,
            ),
            userId: $user->id,
        );

        $order = ($this->createOrderAction)($dto);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 1_500_000,
            'threshold_discount' => 150_000,
            'discount' => 0,
            'total' => 1_350_000 + $order->shipping_cost,
        ]);

        $this->assertSame(1_500_000, $order->subtotal);
        $this->assertSame(150_000, $order->threshold_discount);
        $this->assertSame(0, $order->discount);
        $this->assertSame(1_350_000 + $order->shipping_cost, $order->total);
    }
}
