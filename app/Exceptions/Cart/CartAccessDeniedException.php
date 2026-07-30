<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use RuntimeException;

/**
 * Thrown when the caller is not the owner of the cart (wrong user or guest session).
 */
class CartAccessDeniedException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('cart.errors.access_denied'));
    }
}
