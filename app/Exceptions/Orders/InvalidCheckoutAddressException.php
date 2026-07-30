<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when a saved address id is missing or not owned by the buyer.
 */
class InvalidCheckoutAddressException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('orders.errors.invalid_address'));
    }
}
