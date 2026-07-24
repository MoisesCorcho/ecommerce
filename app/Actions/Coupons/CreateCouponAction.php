<?php

declare(strict_types=1);

namespace App\Actions\Coupons;

use App\DTOs\Coupons\UpsertCouponDTO;
use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Coupon;
use App\Services\Coupons\CouponPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateCouponAction
{
    public function __construct(
        private readonly CouponPricingService $couponPricingService,
    ) {}

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(UpsertCouponDTO $dto): Coupon
    {
        $normalizedCode = $this->couponPricingService->normalizeCode($dto->code);
        $this->assertValid($dto, $normalizedCode);

        return DB::transaction(function () use ($dto, $normalizedCode): Coupon {
            return Coupon::query()->create([
                'code' => $normalizedCode,
                'type' => $dto->type,
                'value' => $dto->value,
                'currency' => $dto->type === CouponTypeEnum::Fixed ? $dto->currency : null,
                'min_order_amount' => $dto->minOrderAmount,
                'min_order_currency' => $dto->minOrderAmount !== null ? $dto->minOrderCurrency : null,
                'usage_limit' => $dto->usageLimit,
                'usage_limit_per_user' => $dto->usageLimitPerUser,
                'used_count' => 0,
                'starts_at' => $dto->startsAt,
                'expires_at' => $dto->expiresAt,
                'is_active' => $dto->isActive,
            ]);
        });
    }

    /**
     * @throws ValidationException
     */
    private function assertValid(UpsertCouponDTO $dto, string $normalizedCode): void
    {
        $errors = [];

        if ($normalizedCode === '') {
            $errors['code'] = __('coupons.validation.code_required');
        } elseif (! preg_match('/^[A-Z0-9-]{1,32}$/', $normalizedCode)) {
            $errors['code'] = __('coupons.validation.code_format');
        } elseif (Coupon::query()->where('code', $normalizedCode)->exists()) {
            $errors['code'] = __('coupons.validation.code_unique');
        }

        if ($dto->type === CouponTypeEnum::Percentage) {
            if ($dto->value < 1 || $dto->value > 100) {
                $errors['value'] = __('coupons.validation.value_percentage_range');
            }
            if ($dto->currency !== null) {
                $errors['currency'] = __('coupons.validation.currency_must_be_null_for_percentage');
            }
        }

        if ($dto->type === CouponTypeEnum::Fixed) {
            if ($dto->value < 1) {
                $errors['value'] = __('coupons.validation.value_fixed_positive');
            }
            if ($dto->currency === null) {
                $errors['currency'] = __('coupons.validation.currency_required_for_fixed');
            }
        }

        if ($dto->minOrderAmount !== null && $dto->minOrderCurrency === null) {
            $errors['min_order_currency'] = __('coupons.validation.min_order_currency_required');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
