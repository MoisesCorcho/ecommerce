<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when checkout is attempted with an empty cart.
 */
class CheckoutCartEmptyException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('orders.errors.cart_empty'));
    }
}
