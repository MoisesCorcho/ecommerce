<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function test_robots_txt_returns_200_and_contains_disallow_rules(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /cart', $content);
        $this->assertStringContainsString('Disallow: /checkout', $content);
        $this->assertStringContainsString('Disallow: /profile', $content);
        $this->assertStringContainsString('Disallow: /orders', $content);
        $this->assertStringContainsString('Disallow: /login', $content);
        $this->assertStringContainsString('Disallow: /register', $content);
        $this->assertStringContainsString('Disallow: /forgot-password', $content);
        $this->assertStringContainsString('Disallow: /api', $content);
        $this->assertStringContainsString('Sitemap:', $content);
        $this->assertStringContainsString('/sitemap.xml', $content);
    }
}
