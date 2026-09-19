<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Commerce\CountryCurrencyMap;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencySwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('app.locale', 'en');
        config()->set('ecommerce.default_currency', 'COP');
        config()->set('ecommerce.currency_preference.country_header', 'CF-IPCountry');
    }

    public function test_switching_currency_stores_the_preference_and_redirects_to_origin(): void
    {
        $this->from(route('faq'))
            ->post(route('currency.update'), ['currency' => 'EUR'])
            ->assertRedirect(route('faq'))
            ->assertCookie('currency', 'EUR');

        $this->assertSame('EUR', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_switching_to_usd_is_rejected_because_it_is_not_available_in_storefront(): void
    {
        $this->from(route('faq'))
            ->post(route('currency.update'), ['currency' => 'USD'])
            ->assertRedirect(route('faq'))
            ->assertSessionHasErrors('currency');

        $this->assertNotSame('USD', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_the_preference_persists_across_requests(): void
    {
        $this->post(route('currency.update'), ['currency' => 'EUR']);

        $this->get(route('faq'))->assertOk();

        $this->assertSame('EUR', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_an_unsupported_currency_is_rejected_and_leaves_the_preference_untouched(): void
    {
        $this->withSession([CurrentCurrency::SESSION_KEY => 'EUR'])
            ->from(route('faq'))
            ->post(route('currency.update'), ['currency' => 'GBP'])
            ->assertSessionHasErrors('currency');

        $this->assertSame('EUR', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_a_request_without_a_currency_field_is_rejected(): void
    {
        $this->from(route('faq'))
            ->post(route('currency.update'), [])
            ->assertSessionHasErrors('currency');
    }

    public function test_the_cookie_beats_geography(): void
    {
        $this->withCookie('currency', 'EUR')
            ->withHeaders(['CF-IPCountry' => 'US'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('EUR', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_the_country_header_selects_the_market_currency(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'ES'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('EUR', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_a_country_mapping_to_usd_falls_back_to_default_currency(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'US'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('COP', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_a_colombian_visitor_gets_pesos(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'CO'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('COP', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_without_cookie_or_country_header_the_default_currency_is_used(): void
    {
        $this->get(route('faq'))->assertOk();

        $this->assertSame('COP', session(CurrentCurrency::SESSION_KEY));
    }

    public function test_an_unusable_country_code_falls_back_to_the_default(): void
    {
        // Cloudflare reports XX for unknown clients and T1 for Tor exits.
        foreach (['XX', 'T1', 'ZZZ', '', '12'] as $country) {
            $this->flushSession();

            $this->withHeaders(['CF-IPCountry' => $country])
                ->get(route('faq'))
                ->assertOk();

            $this->assertSame(
                'COP',
                session(CurrentCurrency::SESSION_KEY),
                "Country [{$country}] should not resolve to a currency.",
            );
        }
    }

    public function test_the_navbar_renders_the_switcher_with_only_storefront_currencies(): void
    {
        $response = $this->get(route('faq'))->assertOk();

        $response->assertSee('COP', false);
        $response->assertSee('EUR', false);
        $response->assertDontSee('USD', false);

        $response->assertSee(route('currency.update'), false);
    }

    public function test_a_cart_line_without_a_price_blocks_the_whole_switch(): void
    {
        // A storefront in euros with a cart still in pesos would let a
        // shopper read one price and be charged another.
        $variant = ProductVariant::factory()->create(['is_active' => true, 'stock' => 5]);
        ProductVariantPrice::factory()->cop()->create(['product_variant_id' => $variant->id]);

        $user = User::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Cop,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->from(route('faq'))
            ->post(route('currency.update'), ['currency' => 'EUR'])
            ->assertRedirect(route('faq'))
            ->assertSessionHasErrors('currency');

        $this->assertNotSame('EUR', session(CurrentCurrency::SESSION_KEY));
        $this->assertSame(CurrencyEnum::Cop, $cart->fresh()->currency);
    }

    public function test_the_country_map_covers_the_markets_filtering_by_storefront(): void
    {
        $this->assertSame(CurrencyEnum::Cop, CountryCurrencyMap::resolve('CO'));
        $this->assertSame(CurrencyEnum::Eur, CountryCurrencyMap::resolve('ES'));
        $this->assertSame(CurrencyEnum::Eur, CountryCurrencyMap::resolve('de'));
        $this->assertNull(CountryCurrencyMap::resolve('US'));
        $this->assertNull(CountryCurrencyMap::resolve('JP'));
        $this->assertNull(CountryCurrencyMap::resolve(null));
        $this->assertNull(CountryCurrencyMap::resolve('XX'));
    }
}
