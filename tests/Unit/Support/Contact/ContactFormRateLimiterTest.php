<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Contact;

use App\Support\Contact\ContactFormRateLimiter;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactFormRateLimiterTest extends TestCase
{
    public function test_attempt_allows_the_first_three_attempts_and_blocks_the_fourth_within_the_window(): void
    {
        $limiter = new ContactFormRateLimiter;
        $ip = '198.51.100.10';

        RateLimiter::clear($limiter->key($ip));

        for ($i = 0; $i < ContactFormRateLimiter::MAX_ATTEMPTS; $i++) {
            $this->assertTrue($limiter->attempt($ip), "Attempt {$i} should be allowed");
        }

        $this->assertFalse($limiter->attempt($ip));

        RateLimiter::clear($limiter->key($ip));
    }

    public function test_key_is_scoped_per_ip(): void
    {
        $limiter = new ContactFormRateLimiter;

        $this->assertSame('contact-form:ip:203.0.113.5', $limiter->key('203.0.113.5'));
        $this->assertSame('contact-form:ip:203.0.113.6', $limiter->key('203.0.113.6'));
    }
}
