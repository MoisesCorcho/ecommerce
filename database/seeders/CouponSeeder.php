<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => 'BIENVENIDA10'],
            [
                'type' => CouponTypeEnum::Percentage,
                'value' => 10,
                'currency' => null,
                'min_order_amount' => null,
                'min_order_currency' => null,
                'usage_limit' => null,
                'usage_limit_per_user' => 1,
                'used_count' => 12,
                'starts_at' => now()->subDays(180),
                'expires_at' => now()->addDays(180),
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'VERANO2026'],
            [
                'type' => CouponTypeEnum::Percentage,
                'value' => 20,
                'currency' => null,
                'min_order_amount' => null,
                'min_order_currency' => null,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'used_count' => 8,
                'starts_at' => now()->subDays(30),
                'expires_at' => now()->addDays(60),
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'COP50K'],
            [
                'type' => CouponTypeEnum::Fixed,
                'value' => 50_000,
                'currency' => CurrencyEnum::Cop,
                'min_order_amount' => 200_000,
                'min_order_currency' => CurrencyEnum::Cop,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 5,
                'starts_at' => now()->subDays(60),
                'expires_at' => now()->addDays(90),
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'EUR15'],
            [
                'type' => CouponTypeEnum::Fixed,
                'value' => 1_500, // 15.00 EUR in cents
                'currency' => CurrencyEnum::Eur,
                'min_order_amount' => 5_000, // 50.00 EUR in cents
                'min_order_currency' => CurrencyEnum::Eur,
                'usage_limit' => 30,
                'usage_limit_per_user' => 1,
                'used_count' => 3,
                'starts_at' => now()->subDays(60),
                'expires_at' => now()->addDays(90),
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'EXPIRED2025'],
            [
                'type' => CouponTypeEnum::Percentage,
                'value' => 15,
                'currency' => null,
                'min_order_amount' => null,
                'min_order_currency' => null,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 10,
                'starts_at' => now()->subDays(120),
                'expires_at' => now()->subDays(30),
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'LIMITADO5'],
            [
                'type' => CouponTypeEnum::Fixed,
                'value' => 30_000,
                'currency' => CurrencyEnum::Cop,
                'min_order_amount' => null,
                'min_order_currency' => null,
                'usage_limit' => 5,
                'usage_limit_per_user' => 1,
                'used_count' => 5,
                'starts_at' => now()->subDays(90),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
            ],
        );
    }
}
