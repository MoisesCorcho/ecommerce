<?php

declare(strict_types=1);

namespace App\Services\Coupons;

use App\DTOs\Coupons\CouponQuoteDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use Carbon\CarbonInterface;

/**
 * Resolve, validate and price a coupon against a subtotal. No writes (D48).
 */
class CouponPricingService
{
    /**
     * Normalize buyer/admin codes: trim + uppercase (D26).
     */
    public function normalizeCode(string $code): string
    {
        return mb_strtoupper(trim($code), 'UTF-8');
    }

    public function isBlank(?string $code): bool
    {
        if ($code === null) {
            return true;
        }

        return trim($code) === '';
    }

    /**
     * Validate applicability and compute discount (floor %; cap to subtotal).
     *
     * @param  bool  $forUpdate  When true, lock the coupon row for concurrent limit checks (create path).
     *
     * @throws InvalidCouponException
     */
    public function quote(
        string $code,
        int $subtotal,
        CurrencyEnum $currency,
        ?int $userId = null,
        ?CarbonInterface $now = null,
        bool $forUpdate = false,
        ?int $discountableSubtotal = null,
    ): CouponQuoteDTO {
        $normalized = $this->normalizeCode($code);

        if ($normalized === '') {
            throw InvalidCouponException::notFound();
        }

        $query = Coupon::query()->where('code', $normalized);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        /** @var Coupon|null $coupon */
        $coupon = $query->first();

        if ($coupon === null) {
            throw InvalidCouponException::notFound();
        }

        $this->assertApplicable($coupon, $subtotal, $currency, $userId, $now ?? now());

        $effectiveSubtotal = $discountableSubtotal !== null ? max(0, $discountableSubtotal) : $subtotal;
        $discount = $this->calculateDiscount($coupon, $effectiveSubtotal, $currency);

        return new CouponQuoteDTO(
            coupon: $coupon,
            code: $normalized,
            discountAmount: $discount,
            currency: $currency,
        );
    }

    /**
     * @throws InvalidCouponException
     */
    public function assertApplicable(
        Coupon $coupon,
        int $subtotal,
        CurrencyEnum $currency,
        ?int $userId = null,
        ?CarbonInterface $now = null,
    ): void {
        $now = $now ?? now();

        if (! $coupon->is_active) {
            throw InvalidCouponException::inactive();
        }

        if ($coupon->starts_at !== null && $now->lt($coupon->starts_at)) {
            throw InvalidCouponException::notStarted();
        }

        if ($coupon->expires_at !== null && $now->gt($coupon->expires_at)) {
            throw InvalidCouponException::expired();
        }

        if ($coupon->type === CouponTypeEnum::Fixed) {
            if ($coupon->currency === null || $coupon->currency !== $currency) {
                throw InvalidCouponException::currencyMismatch();
            }
        }

        if ($coupon->min_order_amount !== null) {
            if ($coupon->min_order_currency === null || $coupon->min_order_currency !== $currency) {
                throw InvalidCouponException::currencyMismatch();
            }

            if ($subtotal < $coupon->min_order_amount) {
                throw InvalidCouponException::minNotMet();
            }
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw InvalidCouponException::usageExhausted();
        }

        if ($userId !== null && $coupon->usage_limit_per_user !== null) {
            $userRedemptions = CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->count();

            if ($userRedemptions >= $coupon->usage_limit_per_user) {
                throw InvalidCouponException::perUserExhausted();
            }
        }
    }

    /**
     * Floor percentage; fixed value in minor units; never exceed subtotal; shipping untouched.
     *
     * @throws InvalidCouponException
     */
    public function calculateDiscount(Coupon $coupon, int $subtotal, CurrencyEnum $currency): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        $raw = match ($coupon->type) {
            CouponTypeEnum::Percentage => intdiv($subtotal * (int) $coupon->value, 100),
            CouponTypeEnum::Fixed => (int) $coupon->value,
        };

        if ($coupon->type === CouponTypeEnum::Fixed
            && ($coupon->currency === null || $coupon->currency !== $currency)
        ) {
            throw InvalidCouponException::currencyMismatch();
        }

        return max(0, min($raw, $subtotal));
    }
}
