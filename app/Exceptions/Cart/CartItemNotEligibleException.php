<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use RuntimeException;

/**
 * Thrown when a variant cannot be sold into the cart (inactive product/variant or missing price).
 */
class CartItemNotEligibleException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('cart.errors.not_eligible'));
    }
}
