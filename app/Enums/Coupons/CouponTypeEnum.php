<?php

declare(strict_types=1);

namespace App\Enums\Coupons;

enum CouponTypeEnum: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Porcentaje',
            self::Fixed => 'Monto fijo',
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
