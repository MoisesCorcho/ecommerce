<?php

declare(strict_types=1);

namespace App\Support\Auth;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limits password reset requests per IP. Max 5 per 60 seconds.
 * Laravel's password broker already throttles per email (config/auth.php);
 * this covers the same IP hitting many different emails.
 */
final class PasswordResetRequestRateLimiter
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
        return 'password-request:ip:'.$ip;
    }
}
