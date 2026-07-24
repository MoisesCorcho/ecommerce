<?php

declare(strict_types=1);

namespace App\Exceptions\Reviews;

use RuntimeException;

/**
 * Thrown when the actor is not allowed to mutate another user's review.
 */
class ReviewForbiddenException extends RuntimeException
{
    public static function notOwner(): self
    {
        return new self(__('reviews.errors.forbidden'));
    }
}
