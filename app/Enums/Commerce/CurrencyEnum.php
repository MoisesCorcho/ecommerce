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

    /**
     * Currencies enabled for public customer browsing and checkout.
     *
     * @return list<self>
     */
    public static function storefrontCases(): array
    {
        return [self::Cop, self::Eur];
    }

    public function isAvailableInStorefront(): bool
    {
        return in_array($this, self::storefrontCases(), true);
    }

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
     * Both USD and COP prefix the "$" sign ("US$", "COP$") to prevent
     * ambiguity between US dollars and Colombian pesos across markets.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::Cop => 'COP$',
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

    /**
     * Minimum cart subtotal required to unlock the automatic volume discount.
     */
    public function thresholdDiscountMinAmount(): int
    {
        if (! (bool) config('ecommerce.cart_threshold_discount.enabled', true)) {
            return 0;
        }

        return (int) config("ecommerce.cart_threshold_discount.min_amounts.{$this->value}", 0);
    }

    /**
     * Percentage of automatic volume discount applied on qualification.
     */
    public function thresholdDiscountPercentage(): int
    {
        if (! (bool) config('ecommerce.cart_threshold_discount.enabled', true)) {
            return 0;
        }

        return (int) config('ecommerce.cart_threshold_discount.percentage', 10);
    }

    /**
     * Check if a given subtotal meets or exceeds the threshold for this currency.
     */
    public function isThresholdDiscountEligible(int $subtotal): bool
    {
        $min = $this->thresholdDiscountMinAmount();

        return $min > 0 && $subtotal >= $min;
    }

    /**
     * Calculate the integer discount amount for a given subtotal.
     */
    public function calculateThresholdDiscount(int $subtotal): int
    {
        if (! $this->isThresholdDiscountEligible($subtotal)) {
            return 0;
        }

        $percentage = $this->thresholdDiscountPercentage();

        return (int) floor(($subtotal * $percentage) / 100);
    }

    /**
     * Minimum chargeable amount for this currency by its payment gateway.
     * Amounts below this cannot be processed by Stripe (EUR/USD) or Bold (COP)
     * and are absorbed during checkout.
     */
    public function minimumChargeableAmount(): int
    {
        return (int) config("ecommerce.payments.min_chargeable_amounts.{$this->value}", match ($this) {
            self::Cop => 1_000,
            self::Eur, self::Usd => 50,
        });
    }
}
