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
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\UnsupportedShippingDestinationException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ShippingZonesCheckoutDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_preview_calculates_cali_rate_cop(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            cartId: (int) $cart->id,
            owner: new CartOwnerDTO(sessionId: $cart->session_id),
            couponCode: null,
            shippingCountry: 'CO',
            shippingCity: 'Cali',
        );

        $this->assertSame(50_000, $preview->subtotal);
        $this->assertSame(10_000, $preview->shippingCost);
        $this->assertSame(60_000, $preview->total);
    }

    public function test_checkout_preview_calculates_national_rate_cop(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            cartId: (int) $cart->id,
            owner: new CartOwnerDTO(sessionId: $cart->session_id),
            couponCode: null,
            shippingCountry: 'CO',
            shippingCity: 'Bogotá',
        );

        $this->assertSame(50_000, $preview->subtotal);
        $this->assertSame(15_000, $preview->shippingCost);
        $this->assertSame(65_000, $preview->total);
    }

    public function test_checkout_preview_calculates_europe_rate_eur(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Eur]);
        $variant = $this->createVariant(CurrencyEnum::Eur, 4_000); // 40.00 EUR
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $preview = app(ValidateCartForCheckoutAction::class)(
            cartId: (int) $cart->id,
            owner: new CartOwnerDTO(sessionId: $cart->session_id),
            couponCode: null,
            shippingCountry: 'ES',
            shippingCity: 'Madrid',
        );

        $this->assertSame(4_000, $preview->subtotal);
        $this->assertSame(3_000, $preview->shippingCost); // 30.00 EUR
        $this->assertSame(7_000, $preview->total);
    }

    public function test_create_order_freezes_cali_shipping_snapshot_and_cost(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createVariant(CurrencyEnum::Cop, 100_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)(new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            userId: null,
            sessionId: $cart->session_id,
            contact: new CheckoutContactDTO(
                firstName: 'Ana',
                lastName: 'Gómez',
                email: 'ana@example.com',
                phone: '+57 300 123 4567',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Ana Gómez',
                phone: '+57 300 123 4567',
                addressLine1: 'Av 6N # 20-30',
                addressLine2: 'Apto 401',
                city: 'Cali',
                state: 'Valle del Cauca',
                country: 'CO',
                postalCode: '760001',
            ),
        ));

        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertSame(100_000, $order->subtotal);
        $this->assertSame(10_000, $order->shipping_cost);
        $this->assertSame(110_000, $order->total);
        $this->assertSame('Cali', $order->shipping_city);
        $this->assertSame('CO', $order->shipping_country);
    }

    public function test_create_order_freezes_national_shipping_snapshot_and_cost(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createVariant(CurrencyEnum::Cop, 100_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = app(CreateOrderFromCartAction::class)(new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            userId: null,
            sessionId: $cart->session_id,
            contact: new CheckoutContactDTO(
                firstName: 'Carlos',
                lastName: 'Pérez',
                email: 'carlos@example.com',
                phone: '+57 310 987 6543',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Carlos Pérez',
                phone: '+57 310 987 6543',
                addressLine1: 'Carrera 7 # 72-41',
                addressLine2: null,
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110221',
            ),
        ));

        $this->assertSame(100_000, $order->subtotal);
        $this->assertSame(15_000, $order->shipping_cost);
        $this->assertSame(115_000, $order->total);
        $this->assertSame('Bogotá', $order->shipping_city);
        $this->assertSame('CO', $order->shipping_country);
    }

    public function test_create_order_aborts_when_destination_unsupported_and_unlisted_blocked(): void
    {
        Config::set('ecommerce.shipping.zones.allow_unlisted', false);

        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Usd]);
        $variant = $this->createVariant(CurrencyEnum::Usd, 5_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->expectException(UnsupportedShippingDestinationException::class);

        app(CreateOrderFromCartAction::class)(new CreateOrderFromCartDTO(
            cartId: (int) $cart->id,
            userId: null,
            sessionId: $cart->session_id,
            contact: new CheckoutContactDTO(
                firstName: 'Kenji',
                lastName: 'Sato',
                email: 'kenji@example.com',
                phone: '+81 90 1234 5678',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'Kenji Sato',
                phone: '+81 90 1234 5678',
                addressLine1: 'Shibuya 1-1',
                addressLine2: null,
                city: 'Tokyo',
                state: 'Tokyo',
                country: 'JP',
                postalCode: '150-0002',
            ),
        ));
    }

    private function createVariant(CurrencyEnum $currency, int $price): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => 50,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->state([
                'currency' => $currency,
                'price' => $price,
            ])
            ->create();

        return $variant;
    }
}
