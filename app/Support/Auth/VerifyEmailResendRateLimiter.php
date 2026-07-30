<?php

declare(strict_types=1);

namespace App\Support\Auth;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limits verification email resends per user. Max 3 per 60 seconds.
 */
final class VerifyEmailResendRateLimiter
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 60;

    public function attempt(int $userId): bool
    {
        $key = $this->key($userId);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return false;
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return true;
    }

    public function key(int $userId): string
    {
        return 'verify-email-resend:user:'.$userId;
    }
}
