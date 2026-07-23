<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Thrown when payment cannot be started (order not pending / already paid).
 */
class OrderNotPayableException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('payments.errors.not_payable'));
    }

    public static function alreadyPaid(): self
    {
        return new self(__('payments.errors.already_paid'));
    }
}
