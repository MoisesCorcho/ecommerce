<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when the actor cannot access or mutate the order/cart for checkout.
 */
class OrderAccessDeniedException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('orders.errors.access_denied'));
    }

    public static function cart(): self
    {
        return new self(__('orders.errors.cart_access_denied'));
    }
}
