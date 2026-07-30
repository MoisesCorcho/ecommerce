<?php

declare(strict_types=1);

namespace App\Exceptions\Cart;

use RuntimeException;

/**
 * Thrown when quantity is negative, exceeds the hard line cap (99), or is otherwise invalid.
 */
class CartQuantityNotAllowedException extends RuntimeException
{
    public static function exceedsMax(int $max = 99): self
    {
        return new self(__('cart.errors.quantity_max', ['max' => $max]));
    }

    public static function invalid(): self
    {
        return new self(__('cart.errors.quantity_invalid'));
    }
}
