<?php

declare(strict_types=1);

namespace App\DTOs\Coupons;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Coupon;

/**
 * Result of validating a coupon code against a cart/order subtotal (no writes).
 */
readonly class CouponQuoteDTO
{
    public function __construct(
        public Coupon $coupon,
        public string $code,
        public int $discountAmount,
        public CurrencyEnum $currency,
    ) {}
}
