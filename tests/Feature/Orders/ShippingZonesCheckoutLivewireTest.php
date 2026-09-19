<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Cart\CartSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class ShippingZonesCheckoutLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_shipping_city_to_cali_updates_totals_reactively(): void
    {
        $sessionId = 'checkout-livewire-cali';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Cop,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->assertSet('preview.subtotal', 50_000)
            ->assertSet('preview.shippingCost', 15_000)
            ->set('shippingCountry', 'CO')
            ->set('shippingCity', 'Cali')
            ->assertSet('preview.shippingCost', 10_000)
            ->assertSet('preview.total', 60_000);
    }

    public function test_changing_shipping_city_to_bogota_updates_totals_reactively(): void
    {
        $sessionId = 'checkout-livewire-bogota';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Cop,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'CO')
            ->set('shippingCity', 'Cali')
            ->assertSet('preview.shippingCost', 10_000)
            ->set('shippingCity', 'Bogotá')
            ->assertSet('preview.shippingCost', 15_000)
            ->assertSet('preview.total', 65_000);
    }

    public function test_saved_address_in_cali_updates_preview_automatically(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create([
            'country' => 'CO',
            'city' => 'Cali',
            'is_default' => true,
        ]);

        $cart = Cart::factory()->for($user)->create([
            'currency' => CurrencyEnum::Cop,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Cop, 60_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test('checkout-page')
            ->assertSet('addressMode', 'saved')
            ->assertSet('shippingAddressId', $address->id)
            ->assertSet('preview.shippingCost', 10_000)
            ->assertSet('preview.total', 70_000);
    }

    public function test_unsupported_destination_country_displays_error(): void
    {
        Config::set('ecommerce.shipping.zones.allow_unlisted', false);

        $sessionId = 'checkout-unsupported-country';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Usd,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Usd, 2_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'JP')
            ->assertHasErrors('shippingCountry')
            ->assertSeeHtml('disabled');
    }

    public function test_cop_cart_with_argentina_destination_shows_unsupported_destination_error(): void
    {
        $sessionId = 'checkout-cop-argentina';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Cop,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'AR')
            ->assertHasErrors('shippingCountry')
            ->assertSeeHtml(__('orders.errors.unsupported_destination', ['country' => 'AR']))
            ->assertDontSeeHtml('USD');
    }

    public function test_cop_cart_with_spain_destination_shows_eur_requirement_message(): void
    {
        $sessionId = 'checkout-cop-spain';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Cop,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Cop, 50_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'ES')
            ->assertHasErrors('shippingCountry')
            ->assertSeeHtml('EUR');
    }

    public function test_eur_cart_with_colombian_destination_shows_cop_requirement_message(): void
    {
        $sessionId = 'checkout-eur-colombia';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Eur,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Eur, 2_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'CO')
            ->assertHasErrors('shippingCountry')
            ->assertSeeHtml('COP');
    }

    public function test_changing_shipping_country_to_spain_in_eur_cart_applies_europe_rate(): void
    {
        $sessionId = 'checkout-livewire-spain-eur';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create([
            'session_id' => $sessionId,
            'currency' => CurrencyEnum::Eur,
        ]);

        $variant = $this->createVariant(CurrencyEnum::Eur, 5_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('shippingCountry', 'ES')
            ->assertSet('preview.shippingCost', 3_000)
            ->assertSet('preview.total', 8_000);
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
