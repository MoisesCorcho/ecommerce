<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when cancel is requested for an order that cannot be cancelled.
 */
class OrderCannotBeCancelledException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('orders.errors.cannot_cancel'));
    }

    /**
     * Pending order with an approved/refunded payment (e.g. D25 stock conflict).
     * Cancelling would release the coupon while money was already captured.
     */
    public static function becausePaymentCaptured(): self
    {
        return new self(__('orders.errors.cannot_cancel_payment_captured'));
    }
}
