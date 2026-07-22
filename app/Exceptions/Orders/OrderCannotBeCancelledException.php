<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when cancel is requested for a non-pending order.
 */
class OrderCannotBeCancelledException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('orders.errors.cannot_cancel'));
    }
}
