<?php

declare(strict_types=1);

namespace App\Exceptions\Reviews;

use RuntimeException;

/**
 * Thrown when create is attempted for a product the user already reviewed.
 */
class ReviewAlreadyExistsException extends RuntimeException
{
    public static function forProduct(): self
    {
        return new self(__('reviews.errors.already_exists'));
    }
}
