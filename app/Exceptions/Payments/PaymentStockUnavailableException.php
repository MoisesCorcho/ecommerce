<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Thrown when stock revalidation fails before starting hosted checkout.
 */
class PaymentStockUnavailableException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('payments.errors.stock_unavailable'));
    }
}
