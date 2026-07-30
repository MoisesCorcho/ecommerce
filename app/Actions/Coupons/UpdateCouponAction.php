<?php

declare(strict_types=1);

namespace App\Actions\Coupons;

use App\DTOs\Coupons\UpsertCouponDTO;
use App\Enums\Coupons\CouponTypeEnum;
use App\Exceptions\Coupons\CouponImmutableFieldsException;
use App\Models\Coupon;
use App\Services\Coupons\CouponPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateCouponAction
{
    public function __construct(
        private readonly CouponPricingService $couponPricingService,
    ) {}

    /**
     * @throws CouponImmutableFieldsException
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(Coupon $coupon, UpsertCouponDTO $dto): Coupon
    {
        $normalizedCode = $this->couponPricingService->normalizeCode($dto->code);
        $this->assertValid($coupon, $dto, $normalizedCode);

        $hasRedemptions = $coupon->redemptions()->exists();

        if ($hasRedemptions && $this->changesImmutableFields($coupon, $dto)) {
            throw CouponImmutableFieldsException::make();
        }

        return DB::transaction(function () use ($coupon, $dto, $normalizedCode, $hasRedemptions): Coupon {
            $payload = [
                'code' => $normalizedCode,
                'min_order_amount' => $dto->minOrderAmount,
                'min_order_currency' => $dto->minOrderAmount !== null ? $dto->minOrderCurrency : null,
                'usage_limit' => $dto->usageLimit,
                'usage_limit_per_user' => $dto->usageLimitPerUser,
                'starts_at' => $dto->startsAt,
                'expires_at' => $dto->expiresAt,
                'is_active' => $dto->isActive,
            ];

            if (! $hasRedemptions) {
                $payload['type'] = $dto->type;
                $payload['value'] = $dto->value;
                $payload['currency'] = $dto->type === CouponTypeEnum::Fixed ? $dto->currency : null;
            }

            $coupon->update($payload);

            return $coupon->fresh() ?? $coupon;
        });
    }

    private function changesImmutableFields(Coupon $coupon, UpsertCouponDTO $dto): bool
    {
        if ($coupon->type !== $dto->type) {
            return true;
        }

        if ((int) $coupon->value !== $dto->value) {
            return true;
        }

        $existingCurrency = $coupon->currency?->value;
        $newCurrency = $dto->type === CouponTypeEnum::Fixed
            ? $dto->currency?->value
            : null;

        return $existingCurrency !== $newCurrency;
    }

    /**
     * @throws ValidationException
     */
    private function assertValid(Coupon $coupon, UpsertCouponDTO $dto, string $normalizedCode): void
    {
        $errors = [];

        if ($normalizedCode === '') {
            $errors['code'] = __('coupons.validation.code_required');
        } elseif (! preg_match('/^[A-Z0-9-]{1,32}$/', $normalizedCode)) {
            $errors['code'] = __('coupons.validation.code_format');
        } elseif (
            Coupon::query()
                ->where('code', $normalizedCode)
                ->whereKeyNot($coupon->getKey())
                ->exists()
        ) {
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
