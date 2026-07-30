<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use RuntimeException;

/**
 * Thrown when the requested line quantity exceeds available stock.
 */
class InsufficientCartStockException extends RuntimeException
{
    public static function make(int $maxAllowed): self
    {
        return new self(__('cart.errors.insufficient_stock', ['max' => $maxAllowed]));
    }
}
