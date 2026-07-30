<?php

declare(strict_types=1);

namespace App\Enums\Coupons;

use Filament\Support\Contracts\HasLabel;

enum CouponTypeEnum: string implements HasLabel
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Percentage => __('enums.coupon_type.percentage'),
            self::Fixed => __('enums.coupon_type.fixed'),
        };
    }

    /**
     * Fixed coupons require a currency; percentage coupons apply to any market.
     */
    public function requiresCurrency(): bool
    {
        return $this === self::Fixed;
    }
}
