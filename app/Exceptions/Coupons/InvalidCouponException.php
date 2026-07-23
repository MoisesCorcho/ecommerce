<?php

declare(strict_types=1);

namespace App\Exceptions\Coupons;

use App\Enums\Coupons\CouponRejectionReasonEnum;
use RuntimeException;

/**
 * Thrown when a coupon code cannot be applied (preview or confirm).
 * Storefront entrypoints should map to {@see self::storefrontMessage()}.
 */
class InvalidCouponException extends RuntimeException
{
    public function __construct(
        public readonly CouponRejectionReasonEnum $reason,
        ?string $message = null,
    ) {
        parent::__construct($message ?? $reason->message());
    }

    public static function notFound(): self
    {
        return new self(CouponRejectionReasonEnum::NotFound);
    }

    public static function inactive(): self
    {
        return new self(CouponRejectionReasonEnum::Inactive);
    }

    public static function notStarted(): self
    {
        return new self(CouponRejectionReasonEnum::NotStarted);
    }

    public static function expired(): self
    {
        return new self(CouponRejectionReasonEnum::Expired);
    }

    public static function currencyMismatch(): self
    {
        return new self(CouponRejectionReasonEnum::CurrencyMismatch);
    }

    public static function minNotMet(): self
    {
        return new self(CouponRejectionReasonEnum::MinNotMet);
    }

    public static function usageExhausted(): self
    {
        return new self(CouponRejectionReasonEnum::UsageExhausted);
    }

    public static function perUserExhausted(): self
    {
        return new self(CouponRejectionReasonEnum::PerUserExhausted);
    }

    /**
     * Generic buyer-facing message (D45 / R14).
     */
    public static function storefrontMessage(): string
    {
        return __('coupons.errors.invalid');
    }

    public function storefrontSafeMessage(): string
    {
        return self::storefrontMessage();
    }
}
