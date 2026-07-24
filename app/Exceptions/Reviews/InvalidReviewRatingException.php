<?php

declare(strict_types=1);

namespace App\Exceptions\Reviews;

use RuntimeException;

/**
 * Thrown when rating is outside the allowed 1–5 integer range.
 */
class InvalidReviewRatingException extends RuntimeException
{
    public static function outOfRange(): self
    {
        return new self(__('reviews.errors.invalid_rating'));
    }
}
