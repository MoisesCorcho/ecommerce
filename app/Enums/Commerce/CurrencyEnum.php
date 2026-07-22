<?php

declare(strict_types=1);

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;

/**
 * Market currencies. Stored values are ISO 4217 codes.
 * COP: integer pesos (no minor units). EUR: integer cents (minor units).
 */
enum CurrencyEnum: string implements HasLabel
{
    case Cop = 'COP';
    case Eur = 'EUR';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Cop => __('enums.currency.COP'),
            self::Eur => __('enums.currency.EUR'),
        };
    }

    /**
     * Default payment provider for this market currency.
     */
    public function paymentProvider(): PaymentProviderEnum
    {
        return match ($this) {
            self::Cop => PaymentProviderEnum::Bold,
            self::Eur => PaymentProviderEnum::Stripe,
        };
    }
}
