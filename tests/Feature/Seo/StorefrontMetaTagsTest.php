<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use Tests\TestCase;

class StorefrontMetaTagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('es');
    }

    public function test_home_renders_canonical_and_default_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();

        // Canonical
        $this->assertStringContainsString('<link rel="canonical"', $content);

        // Title and Description
        $this->assertStringContainsString('<title>LEEN | Bolsos y Accesorios Artesanales Hechos a Mano</title>', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Bolsos y accesorios contemporáneos de lujo consciente', $content);

        // OpenGraph
        $this->assertStringContainsString('<meta property="og:site_name" content="LEEN">', $content);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $content);
        $this->assertStringContainsString('<meta property="og:title"', $content);
        $this->assertStringContainsString('<meta property="og:description"', $content);
        $this->assertStringContainsString('<meta property="og:url"', $content);
        $this->assertStringContainsString('<meta property="og:image"', $content);

        // Twitter Card
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
        $this->assertStringContainsString('<meta name="twitter:title"', $content);
        $this->assertStringContainsString('<meta name="twitter:description"', $content);
        $this->assertStringContainsString('<meta name="twitter:image"', $content);

        // JSON-LD Organization & WebSite
        $this->assertStringContainsString('"@type": "Organization"', $content);
        $this->assertStringContainsString('"name": "LEEN"', $content);
        $this->assertStringContainsString('"@type": "WebSite"', $content);
    }
}
