<?php

declare(strict_types=1);

namespace App\Support\Contact;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limits contact-form submissions per IP. Max 3 per 600 seconds.
 *
 * Deliberately longer/stricter than the 60s limiters used for cheap idempotent
 * actions (login, register): every accepted submission lands in a human inbox,
 * so abuse here is durable spam rather than a cheap retryable action.
 */
final class ContactFormRateLimiter
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 600;

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
        return 'contact-form:ip:'.$ip;
    }
}
