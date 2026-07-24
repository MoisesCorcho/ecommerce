<?php

declare(strict_types=1);

namespace App\DTOs\Coupons;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use Carbon\CarbonImmutable;
use DateTimeInterface;

readonly class UpsertCouponDTO
{
    public function __construct(
        public string $code,
        public CouponTypeEnum $type,
        public int $value,
        public ?CurrencyEnum $currency,
        public ?int $minOrderAmount,
        public ?CurrencyEnum $minOrderCurrency,
        public ?int $usageLimit,
        public ?int $usageLimitPerUser,
        public ?DateTimeInterface $startsAt,
        public ?DateTimeInterface $expiresAt,
        public bool $isActive,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $type = $data['type'] instanceof CouponTypeEnum
            ? $data['type']
            : CouponTypeEnum::from((string) $data['type']);

        $currency = self::optionalCurrency($data['currency'] ?? null);
        if ($type === CouponTypeEnum::Percentage) {
            $currency = null;
        }

        $minAmount = self::optionalPositiveInt($data['min_order_amount'] ?? null);
        $minCurrency = self::optionalCurrency($data['min_order_currency'] ?? null);
        if ($minAmount === null) {
            $minCurrency = null;
        }

        return new self(
            code: trim((string) ($data['code'] ?? '')),
            type: $type,
            value: (int) ($data['value'] ?? 0),
            currency: $currency,
            minOrderAmount: $minAmount,
            minOrderCurrency: $minCurrency,
            usageLimit: self::optionalPositiveInt($data['usage_limit'] ?? null),
            usageLimitPerUser: self::optionalPositiveInt($data['usage_limit_per_user'] ?? null),
            startsAt: self::optionalDateTime($data['starts_at'] ?? null),
            expiresAt: self::optionalDateTime($data['expires_at'] ?? null),
            isActive: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        );
    }

    private static function optionalCurrency(mixed $value): ?CurrencyEnum
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CurrencyEnum) {
            return $value;
        }

        return CurrencyEnum::from((string) $value);
    }

    private static function optionalPositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private static function optionalDateTime(mixed $value): ?DateTimeInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        return CarbonImmutable::parse((string) $value);
    }
}
