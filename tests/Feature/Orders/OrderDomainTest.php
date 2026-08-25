<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Orders\ValidateCartForCheckoutAction;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Products\SizeEnum;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Exceptions\Orders\OrderAccessDeniedException;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OrderDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_checkout_returns_preview_totals(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);

        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 20_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
        );

        $this->assertSame(40_000, $preview->subtotal);
        $this->assertSame(5_000, $preview->shippingCost);
        $this->assertSame(0, $preview->discount);
        $this->assertSame(0, $preview->taxAmount);
        $this->assertSame(45_000, $preview->total);
        $this->assertCount(1, $preview->lines);
    }

    public function test_validate_checkout_formats_variant_label_with_size_enum_and_color(): void
    {
        $cart = Cart::factory()->guest()->create();
        $product = Product::factory()->create(['name' => 'Leather Bag', 'is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-BLK-MED',
            'is_active' => true,
            'stock' => 10,
            'color' => 'Black',
            'size' => SizeEnum::Medium,
        ]);
        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => 50_000]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
        );

        $this->assertCount(1, $preview->lines);
        $this->assertSame('Black / '.SizeEnum::Medium->label(), $preview->lines[0]->variantLabel);
    }

    public function test_validate_fails_when_cart_empty(): void
    {
        $cart = Cart::factory()->guest()->create();

        $this->expectException(CheckoutCartEmptyException::class);

        app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
        );
    }

    public function test_validate_fails_when_stock_insufficient(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 1, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->expectException(CheckoutCartNotReadyException::class);

        app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
        );
    }

    public function test_validate_fails_when_variant_not_eligible(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 10_000);
        $variant->update(['is_active' => false]);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->expectException(CheckoutCartNotReadyException::class);

        app(ValidateCartForCheckoutAction::class)(
            $cart->id,
            new CartOwnerDTO(sessionId: $cart->session_id),
        );
    }

    public function test_create_order_for_user_with_saved_address(): void
    {
        Config::set('ecommerce.shipping.standard_cost_cop', 3_000);

        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $address = Address::factory()->for($user)->create([
            'full_name' => 'Buyer Test',
            'phone' => '+573001112233',
            'address_line_1' => 'Calle 1',
            'city' => 'Medellín',
            'state' => 'Antioquia',
            'country' => 'CO',
        ]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = $this->createEligibleVariant(stock: 8, copPrice: 50_000, sku: 'SKU-ORDER-1');
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $stockBefore = (int) $variant->stock;

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            userId: $user->id,
            email: 'buyer@example.com',
            addressId: $address->id,
            notes: 'Dejar en portería',
        ));

        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{4,}$/', $order->order_number);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(100_000, $order->subtotal);
        $this->assertSame(3_000, $order->shipping_cost);
        $this->assertSame(0, $order->discount);
        $this->assertSame(0, $order->tax_amount);
        $this->assertSame(103_000, $order->total);
        $this->assertSame($address->id, $order->shipping_address_id);
        $this->assertSame('Buyer Test', $order->shipping_full_name);
        $this->assertSame('Dejar en portería', $order->customer_notes);
        $this->assertCount(1, $order->items);
        $this->assertSame(50_000, $order->items->first()->unit_price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame('SKU-ORDER-1', $order->items->first()->sku);
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
        $this->assertSame($stockBefore, (int) $variant->fresh()->stock);
    }

    public function test_create_order_for_guest_and_one_shot_does_not_create_address(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 4, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
            email: 'guest@example.com',
        ));

        $this->assertNull($order->user_id);
        $this->assertNull($order->shipping_address_id);
        $this->assertSame('guest@example.com', $order->email);
        $this->assertSame('Calle One Shot 123', $order->shipping_address_line_1);
        $this->assertSame(0, Address::query()->count());
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    public function test_second_create_on_empty_cart_fails(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 4, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
        ));

        $this->expectException(CheckoutCartEmptyException::class);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(1, Order::query()->count());
    }

    public function test_create_denied_for_foreign_cart(): void
    {
        $cart = Cart::factory()->guest()->create(['session_id' => 'owner-session']);
        $variant = $this->createEligibleVariant(stock: 4, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->expectException(OrderAccessDeniedException::class);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: 'other-session',
        ));
    }

    public function test_cancel_pending_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatusEnum::Pending]);

        $cancelled = app(CancelOrderAction::class)($order->id);

        $this->assertSame(OrderStatusEnum::Cancelled, $cancelled->status);
    }

    public function test_cancel_non_pending_fails(): void
    {
        $order = Order::factory()->paid()->create();

        $this->expectException(OrderCannotBeCancelledException::class);

        app(CancelOrderAction::class)($order->id);
    }

    public function test_create_fails_when_stock_insufficient_at_confirm(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);
        $variant->update(['stock' => 1]);

        $this->expectException(CheckoutCartNotReadyException::class);

        app(CreateOrderFromCartAction::class)($this->createOrderDto(
            cart: $cart,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    private function createOrderDto(
        Cart $cart,
        ?int $userId = null,
        ?string $sessionId = null,
        string $email = 'buyer@example.com',
        ?int $addressId = null,
        ?string $notes = null,
    ): CreateOrderFromCartDTO {
        return new CreateOrderFromCartDTO(
            cartId: $cart->id,
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
                addressId: $addressId,
            ),
            userId: $userId,
            sessionId: $sessionId,
            customerNotes: $notes,
        );
    }

    private function createEligibleVariant(
        int $stock,
        int $copPrice,
        ?int $eurPrice = null,
        ?string $sku = null,
    ): ProductVariant {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => $sku ?? 'SKU-'.uniqid(),
            'is_active' => true,
            'stock' => $stock,
            'color' => 'Black',
            'size' => null,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => $copPrice]);

        if ($eurPrice !== null) {
            ProductVariantPrice::factory()
                ->for($variant, 'productVariant')
                ->eur()
                ->create(['price' => $eurPrice]);
        }

        return $variant;
    }
}
