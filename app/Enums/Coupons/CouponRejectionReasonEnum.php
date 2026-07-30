<?php

declare(strict_types=1);

namespace App\Enums\Coupons;

/**
 * Internal reasons for coupon rejection. Storefront maps all to a generic message (D45).
 */
enum CouponRejectionReasonEnum: string
{
    case NotFound = 'not_found';
    case Inactive = 'inactive';
    case NotStarted = 'not_started';
    case Expired = 'expired';
    case CurrencyMismatch = 'currency_mismatch';
    case MinNotMet = 'min_not_met';
    case UsageExhausted = 'usage_exhausted';
    case PerUserExhausted = 'per_user_exhausted';
    case ImmutableFields = 'immutable_fields';

    public function message(): string
    {
        return match ($this) {
            self::NotFound => __('coupons.errors.not_found'),
            self::Inactive => __('coupons.errors.inactive'),
            self::NotStarted => __('coupons.errors.not_started'),
            self::Expired => __('coupons.errors.expired'),
            self::CurrencyMismatch => __('coupons.errors.currency_mismatch'),
            self::MinNotMet => __('coupons.errors.min_not_met'),
            self::UsageExhausted => __('coupons.errors.usage_exhausted'),
            self::PerUserExhausted => __('coupons.errors.per_user_exhausted'),
            self::ImmutableFields => __('coupons.errors.immutable_fields'),
        };
    }
}
