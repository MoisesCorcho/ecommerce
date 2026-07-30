<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use App\Enums\Commerce\CurrencyEnum;
use RuntimeException;

/**
 * Thrown when currency change is blocked because at least one line lacks a price in the target currency.
 */
class CartCurrencyChangeBlockedException extends RuntimeException
{
    public static function make(CurrencyEnum $currency): self
    {
        return new self(__('cart.errors.currency_blocked', [
            'currency' => $currency->value,
        ]));
    }
}
