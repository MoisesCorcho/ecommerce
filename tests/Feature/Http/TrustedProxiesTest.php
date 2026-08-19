<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_a_forwarded_address_from_an_untrusted_peer_is_ignored(): void
    {
        // Several rate limiters key on the visitor's address. If any caller
        // could set it, one request could exhaust another visitor's budget --
        // or dodge its own.
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->withHeaders(['X-Forwarded-For' => '198.51.100.7'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('203.0.113.9', request()->ip());
    }

    public function test_a_forwarded_address_from_a_trusted_proxy_is_honoured(): void
    {
        // 172.64.0.0/13 is one of Cloudflare's published ranges.
        $this->withServerVariables(['REMOTE_ADDR' => '172.68.10.1'])
            ->withHeaders(['X-Forwarded-For' => '198.51.100.7'])
            ->get(route('faq'))
            ->assertOk();

        $this->assertSame('198.51.100.7', request()->ip());
    }

    public function test_the_trusted_list_is_an_allowlist_and_never_a_wildcard(): void
    {
        // The origin keeps answering on its own address, so a wildcard would
        // hand address spoofing to anyone who reaches it directly.
        $proxies = config('ecommerce.trusted_proxies');

        $this->assertIsArray($proxies);
        $this->assertNotContains('*', $proxies);
        $this->assertNotContains('**', $proxies);
    }

    public function test_every_configured_proxy_entry_is_a_usable_address_or_range(): void
    {
        foreach (config('ecommerce.trusted_proxies') as $entry) {
            [$address] = array_pad(explode('/', (string) $entry, 2), 2, null);

            $this->assertNotFalse(
                filter_var($address, FILTER_VALIDATE_IP),
                "Trusted proxy entry [{$entry}] is not a valid address or CIDR range.",
            );
        }
    }
}
