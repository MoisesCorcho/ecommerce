<?php

declare(strict_types=1);

namespace App\Support\Auth;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limits registration attempts per IP. Max 5 per 60 seconds.
 */
final class RegisterAttemptRateLimiter
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 60;

    public function attempt(string $ip): bool
    {
        $key = $this->key($ip);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return false;
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return true;
    }

    public function key(string $ip): string
    {
        return 'register:ip:'.$ip;
    }
}
