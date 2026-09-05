<?php

declare(strict_types=1);

namespace App\Support\Commerce;

use App\Enums\Commerce\CurrencyEnum;

/**
 * The market currency in force for the current request.
 *
 * Resolved once by the SetCurrency middleware and read from here by every
 * storefront surface, so the catalog, the product page, the wishlist and the
 * cart can never disagree about which currency the visitor is shopping in.
 */
final class CurrentCurrency
{
    public const SESSION_KEY = 'currency';

    public static function get(): CurrencyEnum
    {
        $stored = session(self::SESSION_KEY);

        if (is_string($stored)) {
            $currency = CurrencyEnum::tryFrom($stored);

            if ($currency instanceof CurrencyEnum && $currency->isAvailableInStorefront()) {
                return $currency;
            }
        }

        return self::default();
    }

    public static function default(): CurrencyEnum
    {
        $configured = CurrencyEnum::tryFrom((string) config('ecommerce.default_currency'));

        if ($configured instanceof CurrencyEnum && $configured->isAvailableInStorefront()) {
            return $configured;
        }

        return CurrencyEnum::storefrontCases()[0] ?? CurrencyEnum::Cop;
    }
}
