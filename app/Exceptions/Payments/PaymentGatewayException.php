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
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?string $diagnostic = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function make(?Throwable $previous = null, ?string $diagnostic = null): self
    {
        return new self(__('payments.errors.gateway'), 0, $previous, $diagnostic);
    }
}
