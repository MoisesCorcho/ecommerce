<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Domain signal (D25): payment approved but order cannot be marked paid (insufficient stock).
 */
class OrderPaidStockConflictException extends RuntimeException
{
    public static function make(int $orderId, int $paymentId): self
    {
        return new self(
            __('payments.errors.stock_conflict')." [order={$orderId}, payment={$paymentId}]"
        );
    }
}
