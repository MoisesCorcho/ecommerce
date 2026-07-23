<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;
use Throwable;

/**
 * Thrown when the payment provider fails to create a hosted checkout session.
 */
class PaymentGatewayException extends RuntimeException
{
    public static function make(?Throwable $previous = null): self
    {
        return new self(__('payments.errors.gateway'), 0, $previous);
    }
}
