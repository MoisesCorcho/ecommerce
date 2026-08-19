<?php

declare(strict_types=1);

namespace App\Support\Commerce;

use App\Enums\Commerce\CurrencyEnum;

/**
 * Maps an ISO 3166-1 alpha-2 country to the market currency it should shop in.
 */
final class CountryCurrencyMap
{
    /**
     * Countries that settle in euro: the twenty euro-area members plus the
     * four microstates that use it under monetary agreement.
     *
     * @var list<string>
     */
    private const EUR_COUNTRIES = [
        'AD', 'AT', 'BE', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GR',
        'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MC', 'MT', 'NL', 'PT',
        'SI', 'SK', 'SM', 'VA',
    ];

    /**
     * Resolve a country to its market currency.
     *
     * Returns null when the country is absent or not a plausible country code,
     * so the caller can fall back instead of guessing. Cloudflare reports "XX"
     * for unknown clients and "T1" for Tor exits — neither is a country.
     */
    public static function resolve(?string $country): ?CurrencyEnum
    {
        if ($country === null) {
            return null;
        }

        $code = strtoupper(trim($country));

        if (preg_match('/^[A-Z]{2}$/', $code) !== 1 || in_array($code, ['XX', 'T1'], true)) {
            return null;
        }

        if ($code === 'CO') {
            return CurrencyEnum::Cop;
        }

        if (in_array($code, self::EUR_COUNTRIES, true)) {
            return CurrencyEnum::Eur;
        }

        return CurrencyEnum::Usd;
    }
}
