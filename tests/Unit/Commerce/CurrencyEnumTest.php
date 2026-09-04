<?php

declare(strict_types=1);

namespace Tests\Unit\Commerce;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Payments\PaymentProviderEnum;
use Tests\TestCase;

class CurrencyEnumTest extends TestCase
{
    public function test_minor_unit_currencies_are_formatted_with_two_decimals(): void
    {
        // 4999 cents is 49.99, not 4.999. Getting this wrong understates or
        // overstates every price on the storefront by a factor of a hundred.
        $this->assertSame('49,99', CurrencyEnum::Usd->format(4_999, withSymbol: false));
        $this->assertSame('49,99', CurrencyEnum::Eur->format(4_999, withSymbol: false));
    }

    public function test_pesos_have_no_minor_unit(): void
    {
        $this->assertSame('120.000', CurrencyEnum::Cop->format(120_000, withSymbol: false));
    }

    public function test_dollars_and_pesos_are_not_shown_with_a_bare_dollar_sign(): void
    {
        // Both USD and COP prefix "$" ("US$", "COP$") to avoid ambiguity between markets.
        $this->assertSame('US$ 49,99', CurrencyEnum::Usd->format(4_999));
        $this->assertSame('COP$ 120.000', CurrencyEnum::Cop->format(120_000));
        $this->assertSame('€ 49,99', CurrencyEnum::Eur->format(4_999));
    }

    public function test_every_currency_resolves_a_payment_provider(): void
    {
        $this->assertSame(PaymentProviderEnum::Bold, CurrencyEnum::Cop->paymentProvider());
        $this->assertSame(PaymentProviderEnum::Stripe, CurrencyEnum::Eur->paymentProvider());
        $this->assertSame(PaymentProviderEnum::Stripe, CurrencyEnum::Usd->paymentProvider());
    }

    public function test_every_currency_resolves_a_label_and_a_symbol(): void
    {
        // Guards against a new case slipping in without its translation or
        // symbol, which would surface as a raw key in front of a customer.
        foreach (CurrencyEnum::cases() as $currency) {
            $this->assertNotSame('', $currency->symbol());
            $this->assertStringNotContainsString('enums.currency', $currency->label());
        }
    }

    public function test_minor_units_are_declared_for_every_currency(): void
    {
        foreach (CurrencyEnum::cases() as $currency) {
            $this->assertContains($currency->minorUnits(), [1, 100]);
        }
    }
}
