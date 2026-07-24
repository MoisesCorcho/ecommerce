<?php

declare(strict_types=1);

namespace App\Support\Coupons;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limits non-blank coupon code attempts (preview / confirm) per user or IP.
 * Max 30 attempts per 60 seconds — slows campaign-code enumeration (F06 P2).
 */
final class CouponAttemptRateLimiter
{
    public const MAX_ATTEMPTS = 30;

    public const DECAY_SECONDS = 60;

    /**
     * @return bool true when the attempt is allowed; false when rate-limited
     */
    public function attempt(?int $userId, string $ip): bool
    {
        $key = $this->key($userId, $ip);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return false;
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return true;
    }

    public function clear(?int $userId, string $ip): void
    {
        RateLimiter::clear($this->key($userId, $ip));
    }

    public function key(?int $userId, string $ip): string
    {
        if ($userId !== null) {
            return 'coupons-preview:user:'.$userId;
        }

        return 'coupons-preview:ip:'.$ip;
    }
}
