<?php

declare(strict_types=1);

namespace App\Enums\Commerce;

use App\Enums\Payments\PaymentProviderEnum;
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

    /**
     * Format integer amount according to currency rules.
     * COP: integer pesos (no minor units). EUR: integer cents (minor units).
     */
    public function format(int $amount, bool $withSymbol = true): string
    {
        if ($this === self::Eur) {
            $formatted = number_format($amount / 100, 2, ',', '.');

            return $withSymbol ? '€ '.$formatted : $formatted;
        }

        $formatted = number_format($amount, 0, ',', '.');

        return $withSymbol ? '$ '.$formatted : $formatted;
    }
}
