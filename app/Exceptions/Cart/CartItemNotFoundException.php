<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use RuntimeException;

/**
 * Thrown when a cart line cannot be resolved for the given cart/variant.
 */
class CartItemNotFoundException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('cart.errors.item_not_found'));
    }
}
