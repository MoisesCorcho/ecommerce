<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Thrown when the actor cannot pay or access payment for the order.
 */
class PaymentAccessDeniedException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('payments.errors.access_denied'));
    }
}
