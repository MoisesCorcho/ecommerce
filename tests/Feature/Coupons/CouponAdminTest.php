<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Actions\Coupons\CreateCouponAction;
use App\Actions\Coupons\UpdateCouponAction;
use App\DTOs\Coupons\UpsertCouponDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Exceptions\Coupons\CouponImmutableFieldsException;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Admin domain Actions + Filament essential paths (R1, R2, R12, R18, R19).
 */
class CouponAdminTest extends TestCase
{
    use RefreshDatabase;

    // ─── 5.11 Admin create / immutable ───────────────────────────────────────

    public function test_admin_creates_percentage_coupon(): void
    {
        $coupon = app(CreateCouponAction::class)(UpsertCouponDTO::fromArray([
            'code' => 'pct-new',
            'type' => CouponTypeEnum::Percentage->value,
            'value' => 15,
            'currency' => null,
            'is_active' => true,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
        ]));

        $this->assertSame('PCT-NEW', $coupon->code);
        $this->assertSame(CouponTypeEnum::Percentage, $coupon->type);
        $this->assertSame(15, $coupon->value);
        $this->assertNull($coupon->currency);
        $this->assertTrue($coupon->is_active);
        $this->assertSame(0, $coupon->used_count);
    }

    public function test_admin_creates_fixed_coupon_with_currency(): void
    {
        $coupon = app(CreateCouponAction::class)(UpsertCouponDTO::fromArray([
            'code' => 'FIX10K',
            'type' => CouponTypeEnum::Fixed->value,
            'value' => 10_000,
            'currency' => CurrencyEnum::Cop->value,
            'is_active' => true,
        ]));

        $this->assertSame(CouponTypeEnum::Fixed, $coupon->type);
        $this->assertSame(10_000, $coupon->value);
        $this->assertSame(CurrencyEnum::Cop, $coupon->currency);
    }

    public function test_admin_create_rejects_fixed_without_currency(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateCouponAction::class)(UpsertCouponDTO::fromArray([
            'code' => 'BADFIXED',
            'type' => CouponTypeEnum::Fixed->value,
            'value' => 5000,
            'currency' => null,
            'is_active' => true,
        ]));
    }

    public function test_admin_create_rejects_percentage_out_of_range(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateCouponAction::class)(UpsertCouponDTO::fromArray([
            'code' => 'BADPCT',
            'type' => CouponTypeEnum::Percentage->value,
            'value' => 101,
            'currency' => null,
            'is_active' => true,
        ]));
    }

    public function test_admin_cannot_change_immutable_fields_after_redemption(): void
    {
        $coupon = Coupon::factory()->percentage(10)->create(['code' => 'IMMUT']);
        CouponRedemption::factory()->create([
            'coupon_id' => $coupon->id,
            'code' => 'IMMUT',
            'discount_amount' => 1000,
        ]);

        $this->expectException(CouponImmutableFieldsException::class);

        app(UpdateCouponAction::class)($coupon, UpsertCouponDTO::fromArray([
            'code' => 'IMMUT',
            'type' => CouponTypeEnum::Fixed->value,
            'value' => 5000,
            'currency' => CurrencyEnum::Cop->value,
            'is_active' => true,
        ]));
    }

    public function test_admin_can_toggle_active_after_redemption(): void
    {
        $coupon = Coupon::factory()->percentage(10)->create([
            'code' => 'TOGGLE',
            'is_active' => true,
        ]);
        CouponRedemption::factory()->create([
            'coupon_id' => $coupon->id,
            'code' => 'TOGGLE',
            'discount_amount' => 1000,
        ]);

        $updated = app(UpdateCouponAction::class)($coupon, UpsertCouponDTO::fromArray([
            'code' => 'TOGGLE',
            'type' => CouponTypeEnum::Percentage->value,
            'value' => 10,
            'currency' => null,
            'is_active' => false,
            'usage_limit' => 50,
        ]));

        $this->assertFalse($updated->is_active);
        $this->assertSame(50, $updated->usage_limit);
        $this->assertSame(CouponTypeEnum::Percentage, $updated->type);
        $this->assertSame(10, $updated->value);
    }

    // ─── 5.12 Filament list/create ───────────────────────────────────────────

    public function test_admin_can_list_coupons_via_filament(): void
    {
        $this->actingAsAdmin();

        $coupon = Coupon::factory()->percentage(10)->create(['code' => 'LISTME']);

        Livewire::test(ListCoupons::class)
            ->assertCanSeeTableRecords([$coupon])
            ->searchTable('LISTME')
            ->assertCanSeeTableRecords([$coupon]);
    }

    public function test_admin_can_create_percentage_coupon_via_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCoupon::class)
            ->set('data.code', 'FILPCT')
            ->set('data.type', CouponTypeEnum::Percentage->value)
            ->set('data.value', 12)
            ->set('data.currency', null)
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Coupon::class, [
            'code' => 'FILPCT',
            'type' => CouponTypeEnum::Percentage->value,
            'value' => 12,
        ]);

        Livewire::test(ListCoupons::class)
            ->assertCanSeeTableRecords(Coupon::query()->where('code', 'FILPCT')->get());
    }

    public function test_admin_can_create_fixed_coupon_via_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCoupon::class)
            ->set('data.code', 'FILFIX')
            ->set('data.type', CouponTypeEnum::Fixed->value)
            ->set('data.value', 15_000)
            ->set('data.currency', CurrencyEnum::Cop->value)
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Coupon::class, [
            'code' => 'FILFIX',
            'type' => CouponTypeEnum::Fixed->value,
            'value' => 15_000,
            'currency' => CurrencyEnum::Cop->value,
        ]);
    }

    public function test_filament_create_requires_code(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCoupon::class)
            ->set('data.code', null)
            ->set('data.type', CouponTypeEnum::Percentage->value)
            ->set('data.value', 10)
            ->call('create')
            ->assertHasFormErrors(['code' => 'required']);
    }

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        return $user;
    }
}
