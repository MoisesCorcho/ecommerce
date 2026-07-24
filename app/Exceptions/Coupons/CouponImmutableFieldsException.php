<?php

declare(strict_types=1);

namespace App\Exceptions\Coupons;

use RuntimeException;

/**
 * Thrown when admin tries to change type/value/currency after redemptions exist (R19).
 */
class CouponImmutableFieldsException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('coupons.errors.immutable_fields'));
    }
}
