<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Market currencies. Stored values are ISO 4217 codes.
 * COP: integer pesos (no minor units). EUR: integer cents (minor units).
 */
enum CurrencyEnum: string
{
    case Cop = 'COP';
    case Eur = 'EUR';

    public function label(): string
    {
        return match ($this) {
            self::Cop => 'Peso colombiano',
            self::Eur => 'Euro',
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
