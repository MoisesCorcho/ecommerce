<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use App\Enums\Commerce\CurrencyEnum;
use RuntimeException;

/**
 * Thrown when a shipping destination country or city is not supported by shipping zones.
 */
class UnsupportedShippingDestinationException extends RuntimeException
{
    public static function forCountry(?string $countryCode = null): self
    {
        return new self(__('orders.errors.unsupported_destination', [
            'country' => $countryCode ?? '—',
        ]));
    }

    public static function currencyMismatch(string $countryCode, CurrencyEnum $expectedCurrency): self
    {
        return new self(__('orders.errors.currency_mismatch_destination', [
            'country' => $countryCode,
            'currency' => $expectedCurrency->label(),
            'code' => $expectedCurrency->value,
        ]));
    }
}
