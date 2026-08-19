<?php

declare(strict_types=1);

namespace App\Enums\Commerce;

use App\Enums\Payments\PaymentProviderEnum;
use Filament\Support\Contracts\HasLabel;

/**
 * Market currencies. Stored values are ISO 4217 codes.
 *
 * Amounts are always stored as integers in the currency's smallest unit:
 * COP has no minor unit (1 = one peso), EUR and USD are cents.
 */
enum CurrencyEnum: string implements HasLabel
{
    case Cop = 'COP';
    case Eur = 'EUR';
    case Usd = 'USD';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return __('enums.currency.'.$this->value);
    }

    /**
     * Default payment provider for this market currency.
     */
    public function paymentProvider(): PaymentProviderEnum
    {
        return match ($this) {
            self::Cop => PaymentProviderEnum::Bold,
            self::Eur, self::Usd => PaymentProviderEnum::Stripe,
        };
    }

    /**
     * How many integer units make up one unit of the currency.
     *
     * Drives both formatting and the decimal precision shown to customers.
     */
    public function minorUnits(): int
    {
        return match ($this) {
            self::Cop => 1,
            self::Eur, self::Usd => 100,
        };
    }

    /**
     * Currency symbol shown next to amounts.
     *
     * USD is prefixed rather than a bare "$" because COP already uses that
     * sign: an unqualified "$ 120.000" would be ambiguous to a shopper who
     * can be seeing either market.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::Cop => '$',
            self::Eur => '€',
            self::Usd => 'US$',
        };
    }

    /**
     * Format an integer amount expressed in this currency's smallest unit.
     */
    public function format(int $amount, bool $withSymbol = true): string
    {
        $minorUnits = $this->minorUnits();
        $decimals = $minorUnits === 1 ? 0 : 2;

        $formatted = number_format($amount / $minorUnits, $decimals, ',', '.');

        return $withSymbol ? $this->symbol().' '.$formatted : $formatted;
    }
}
