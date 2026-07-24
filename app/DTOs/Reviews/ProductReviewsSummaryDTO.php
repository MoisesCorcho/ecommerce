<?php

declare(strict_types=1);

namespace App\DTOs\Reviews;

readonly class ProductReviewsSummaryDTO
{
    public function __construct(
        public int $reviewsCount,
        public ?float $averageRating,
    ) {}
}
