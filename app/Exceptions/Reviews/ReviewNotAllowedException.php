<?php

declare(strict_types=1);

namespace App\Exceptions\Reviews;

use RuntimeException;

/**
 * Thrown when the actor cannot create or keep a verified review for a product.
 */
class ReviewNotAllowedException extends RuntimeException
{
    public static function notEligible(): self
    {
        return new self(__('reviews.errors.not_eligible'));
    }

    public static function unauthenticated(): self
    {
        return new self(__('reviews.errors.unauthenticated'));
    }
}
