<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('es');
    }

    public function test_catalog_default_renders_shop_seo_title_and_h1(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Tienda | LEEN — Catálogo Completo</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('products.index').'">', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Descubre la colección completa de bolsos', $content);
        $this->assertStringContainsString('<h1 class="font-chillax', $content);
        $this->assertStringContainsString('Tienda', $content);
    }

    public function test_catalog_with_category_filter_renders_dynamic_title_and_h1(): void
    {
        $category = Category::factory()->create([
            'name' => 'Bolsos de Mano',
            'slug' => 'bolsos-de-mano',
        ]);

        $response = $this->get(route('products.index', ['category' => $category->slug]));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Bolsos de Mano | Colección LEEN</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('products.index', ['category' => $category->slug]).'">', $content);
        $this->assertStringContainsString('<h1 class="font-chillax', $content);
        $this->assertStringContainsString('Bolsos de Mano', $content);
    }
}
