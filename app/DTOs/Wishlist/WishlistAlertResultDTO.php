<?php

declare(strict_types=1);

namespace App\DTOs\Wishlist;

readonly class WishlistAlertResultDTO
{
    public function __construct(
        public int $priceDropsSent,
        public int $lowStockSent,
        public int $skippedCooldown,
        public int $skippedExcluded,
    ) {}

    public function totalSent(): int
    {
        return $this->priceDropsSent + $this->lowStockSent;
    }
}
