<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponRejectionReasonEnum;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Models\Coupon;
use App\Services\Coupons\CouponPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pricing rules for CouponPricingService (R6–R8, R15, R22).
 */
class CouponPricingTest extends TestCase
{
    use RefreshDatabase;

    // ─── 5.1 Percentage floor + fixed match/mismatch ─────────────────────────

    public function test_percentage_floors_on_cop_subtotal(): void
    {
        Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'PCT10']);

        $quote = app(CouponPricingService::class)->quote(
            code: 'pct10',
            subtotal: 999,
            currency: CurrencyEnum::Cop,
        );

        // floor(999 * 10 / 100) = 99
        $this->assertSame(99, $quote->discountAmount);
        $this->assertSame('PCT10', $quote->code);
        $this->assertSame(CurrencyEnum::Cop, $quote->currency);
    }

    public function test_percentage_floors_on_eur_subtotal(): void
    {
        Coupon::factory()->percentage(10)->unlimited()->create(['code' => 'PCT10E']);

        $quote = app(CouponPricingService::class)->quote(
            code: 'PCT10E',
            subtotal: 999,
            currency: CurrencyEnum::Eur,
        );

        $this->assertSame(99, $quote->discountAmount);
        $this->assertSame(CurrencyEnum::Eur, $quote->currency);
    }

    public function test_percentage_applies_without_coupon_currency(): void
    {
        $coupon = Coupon::factory()->percentage(15)->unlimited()->create([
            'code' => 'ANYMKT',
            'currency' => null,
        ]);

        $cop = app(CouponPricingService::class)->quote('ANYMKT', 10_000, CurrencyEnum::Cop);
        $eur = app(CouponPricingService::class)->quote('ANYMKT', 2_000, CurrencyEnum::Eur);

        $this->assertSame(1_500, $cop->discountAmount);
        $this->assertSame(300, $eur->discountAmount);
        $this->assertNull($coupon->currency);
    }

    public function test_fixed_matches_currency_and_caps_at_value(): void
    {
        Coupon::factory()->fixed(CurrencyEnum::Cop)->unlimited()->create([
            'code' => 'FIX20K',
            'value' => 20_000,
        ]);

        $quote = app(CouponPricingService::class)->quote(
            code: 'FIX20K',
            subtotal: 100_000,
            currency: CurrencyEnum::Cop,
        );

        $this->assertSame(20_000, $quote->discountAmount);
    }

    public function test_fixed_currency_mismatch_is_rejected(): void
    {
        Coupon::factory()->fixed(CurrencyEnum::Cop)->unlimited()->create([
            'code' => 'COPONLY',
            'value' => 10_000,
        ]);

        try {
            app(CouponPricingService::class)->quote(
                code: 'COPONLY',
                subtotal: 100_000,
                currency: CurrencyEnum::Eur,
            );
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::CurrencyMismatch, $e->reason);
        }
    }

    public function test_fixed_eur_mismatch_on_cop_cart_is_rejected(): void
    {
        Coupon::factory()->fixed(CurrencyEnum::Eur)->unlimited()->create([
            'code' => 'EURONLY',
            'value' => 500,
        ]);

        $this->expectException(InvalidCouponException::class);

        app(CouponPricingService::class)->quote(
            code: 'EURONLY',
            subtotal: 50_000,
            currency: CurrencyEnum::Cop,
        );
    }

    // ─── 5.2 Min order, inactive, dates, cap, shipping untouched ─────────────

    public function test_min_order_not_met_is_rejected(): void
    {
        Coupon::factory()
            ->percentage(10)
            ->unlimited()
            ->minOrder(50_000, CurrencyEnum::Cop)
            ->create(['code' => 'MIN50']);

        try {
            app(CouponPricingService::class)->quote(
                code: 'MIN50',
                subtotal: 49_999,
                currency: CurrencyEnum::Cop,
            );
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::MinNotMet, $e->reason);
        }
    }

    public function test_min_order_met_applies_discount(): void
    {
        Coupon::factory()
            ->percentage(10)
            ->unlimited()
            ->minOrder(50_000, CurrencyEnum::Cop)
            ->create(['code' => 'MINOK']);

        $quote = app(CouponPricingService::class)->quote(
            code: 'MINOK',
            subtotal: 50_000,
            currency: CurrencyEnum::Cop,
        );

        $this->assertSame(5_000, $quote->discountAmount);
    }

    public function test_min_order_currency_mismatch_is_rejected(): void
    {
        Coupon::factory()
            ->percentage(10)
            ->unlimited()
            ->minOrder(50_000, CurrencyEnum::Cop)
            ->create(['code' => 'MINCOP']);

        try {
            app(CouponPricingService::class)->quote(
                code: 'MINCOP',
                subtotal: 100_000,
                currency: CurrencyEnum::Eur,
            );
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::CurrencyMismatch, $e->reason);
        }
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        Coupon::factory()->percentage(10)->unlimited()->inactive()->create(['code' => 'OFF']);

        try {
            app(CouponPricingService::class)->quote('OFF', 10_000, CurrencyEnum::Cop);
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::Inactive, $e->reason);
        }
    }

    public function test_expired_coupon_is_rejected(): void
    {
        Coupon::factory()->percentage(10)->unlimited()->expired()->create(['code' => 'OLD']);

        try {
            app(CouponPricingService::class)->quote('OLD', 10_000, CurrencyEnum::Cop);
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::Expired, $e->reason);
        }
    }

    public function test_not_started_coupon_is_rejected(): void
    {
        Coupon::factory()->percentage(10)->unlimited()->notStarted()->create(['code' => 'SOON']);

        try {
            app(CouponPricingService::class)->quote('SOON', 10_000, CurrencyEnum::Cop);
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::NotStarted, $e->reason);
        }
    }

    public function test_discount_is_capped_to_subtotal(): void
    {
        Coupon::factory()->fixed(CurrencyEnum::Cop)->unlimited()->create([
            'code' => 'HUGE',
            'value' => 999_999,
        ]);

        $quote = app(CouponPricingService::class)->quote(
            code: 'HUGE',
            subtotal: 30_000,
            currency: CurrencyEnum::Cop,
        );

        $this->assertSame(30_000, $quote->discountAmount);
    }

    public function test_one_hundred_percent_discount_equals_subtotal_not_beyond(): void
    {
        Coupon::factory()->percentage(100)->unlimited()->create(['code' => 'FREE100']);

        $quote = app(CouponPricingService::class)->quote(
            code: 'FREE100',
            subtotal: 45_000,
            currency: CurrencyEnum::Cop,
        );

        // Shipping is applied outside pricing service; discount never includes shipping (D16).
        $this->assertSame(45_000, $quote->discountAmount);
    }

    public function test_not_found_code_is_rejected(): void
    {
        try {
            app(CouponPricingService::class)->quote('NOPE', 10_000, CurrencyEnum::Cop);
            $this->fail('Expected InvalidCouponException');
        } catch (InvalidCouponException $e) {
            $this->assertSame(CouponRejectionReasonEnum::NotFound, $e->reason);
        }
    }
}
