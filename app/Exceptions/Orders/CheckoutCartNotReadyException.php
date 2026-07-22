<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use RuntimeException;

/**
 * Thrown when one or more cart lines fail stock or eligibility revalidation.
 */
class CheckoutCartNotReadyException extends RuntimeException
{
    public static function make(?string $message = null): self
    {
        return new self($message ?? __('orders.errors.cart_not_ready'));
    }

    public static function insufficientStock(string $product, int $max): self
    {
        return new self(__('orders.errors.insufficient_stock', [
            'product' => $product,
            'max' => $max,
        ]));
    }

    public static function notEligible(string $product): self
    {
        return new self(__('orders.errors.not_eligible', [
            'product' => $product,
        ]));
    }
}
