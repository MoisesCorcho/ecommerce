<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

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
}
