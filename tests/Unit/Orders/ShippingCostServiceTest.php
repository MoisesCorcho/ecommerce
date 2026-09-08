<?php

declare(strict_types=1);

namespace Tests\Unit\Orders;

use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Orders\UnsupportedShippingDestinationException;
use App\Services\Orders\ShippingCostService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ShippingCostServiceTest extends TestCase
{
    private ShippingCostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingCostService;
    }

    public function test_cali_returns_local_rate_cop(): void
    {
        // Exact match
        $this->assertSame(10_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', 'Cali'));

        // Case insensitivity and whitespace
        $this->assertSame(10_000, $this->service->calculate(CurrencyEnum::Cop, 'co', '  CALI  '));

        // Known alias
        $this->assertSame(10_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', 'Santiago de Cali'));
    }

    public function test_other_colombian_cities_return_national_rate_cop(): void
    {
        $this->assertSame(15_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', 'Bogotá'));
        $this->assertSame(15_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', 'Medellín'));
        $this->assertSame(15_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', 'Barranquilla'));
        $this->assertSame(15_000, $this->service->calculate(CurrencyEnum::Cop, 'CO', null));
    }

    public function test_european_countries_return_europe_rate_eur(): void
    {
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Eur, 'ES', 'Madrid'));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Eur, 'FR', 'París'));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Eur, 'DE', 'Berlín'));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Eur, 'IT', 'Roma'));
    }

    public function test_americas_countries_return_americas_rate_usd(): void
    {
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Usd, 'US', 'Miami'));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Usd, 'MX', 'Ciudad de México'));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Usd, 'PA', 'Panamá'));
    }

    public function test_unlisted_country_throws_exception_when_unlisted_disallowed(): void
    {
        Config::set('ecommerce.shipping.zones.allow_unlisted', false);

        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->service->calculate(CurrencyEnum::Usd, 'JP', 'Tokyo');
    }

    public function test_unlisted_country_returns_fallback_when_unlisted_allowed(): void
    {
        Config::set('ecommerce.shipping.zones.allow_unlisted', true);
        Config::set('ecommerce.shipping.zones.unlisted_fallback_cost_usd', 4_500);

        $this->assertSame(4_500, $this->service->calculate(CurrencyEnum::Usd, 'JP', 'Tokyo'));
    }

    public function test_default_cost_for_currency_without_destination(): void
    {
        $this->assertSame(15_000, $this->service->calculate(CurrencyEnum::Cop));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Eur));
        $this->assertSame(3_000, $this->service->calculate(CurrencyEnum::Usd));
    }

    public function test_backwards_compatibility_standard_cost_method(): void
    {
        // When config override is set
        Config::set('ecommerce.shipping.standard_cost_cop', 5_000);
        $this->assertSame(5_000, $this->service->standardCost(CurrencyEnum::Cop));

        Config::set('ecommerce.shipping.standard_cost_eur', 2_500);
        $this->assertSame(2_500, $this->service->standardCost(CurrencyEnum::Eur));
    }

    public function test_currency_mismatch_throws_unsupported_destination_exception(): void
    {
        // Argentina (AR) is in americas (USD), so COP cart must be rejected
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->service->calculate(CurrencyEnum::Cop, 'AR', 'Mar del Plata');
    }

    public function test_cop_cart_rejects_european_destinations(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->service->calculate(CurrencyEnum::Cop, 'ES', 'Madrid');
    }

    public function test_eur_cart_rejects_colombian_destinations(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->service->calculate(CurrencyEnum::Eur, 'CO', 'Cali');
    }

    public function test_currency_mismatch_throws_descriptive_exception_for_colombia_in_eur_cart(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->expectExceptionMessage('COP');

        $this->service->calculate(CurrencyEnum::Eur, 'CO', 'Cali');
    }

    public function test_currency_mismatch_throws_descriptive_exception_for_spain_in_cop_cart(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->expectExceptionMessage('EUR');

        $this->service->calculate(CurrencyEnum::Cop, 'ES', 'Madrid');
    }

    public function test_destination_with_inactive_storefront_currency_throws_generic_unsupported_destination_exception(): void
    {
        // USD is inactive in storefront (CurrencyEnum::storefrontCases() = [COP, EUR]).
        // Destinations in Americas zone (like AR or US) must not advise switching to USD.
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->expectExceptionMessage(__('orders.errors.unsupported_destination', ['country' => 'AR']));

        $this->service->calculate(CurrencyEnum::Cop, 'AR', 'Mar del Plata');
    }

    public function test_destination_with_inactive_storefront_currency_us_throws_generic_unsupported_destination_exception(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->expectExceptionMessage(__('orders.errors.unsupported_destination', ['country' => 'US']));

        $this->service->calculate(CurrencyEnum::Cop, 'US', 'Miami');
    }

    public function test_unlisted_destination_without_coverage_throws_generic_exception(): void
    {
        $this->expectException(UnsupportedShippingDestinationException::class);
        $this->expectExceptionMessage(__('orders.errors.unsupported_destination', ['country' => 'JP']));

        $this->service->calculate(CurrencyEnum::Cop, 'JP', 'Tokyo');
    }
}
