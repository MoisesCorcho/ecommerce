<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Enums\Blog\PostStatusEnum;
use App\Enums\Commerce\CurrencyEnum;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_200_and_xml_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
    }

    public function test_sitemap_includes_static_public_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>'.url('/').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.route('products.index').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/about-us').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.route('blog.index').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.route('contact').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/faq').'</loc>', $content);
    }

    public function test_sitemap_includes_published_products_and_excludes_inactive(): void
    {
        Cache::forget('seo.sitemap');

        // Active published product with price in COP
        $published = Product::factory()->create([
            'name' => 'Published Honey Bag',
            'slug' => 'published-honey-bag',
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $published->id,
            'is_active' => true,
        ]);
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Cop,
            'price' => 150000,
        ]);

        // Inactive product
        $inactive = Product::factory()->create([
            'name' => 'Inactive Bag',
            'slug' => 'inactive-bag',
            'is_active' => false,
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>'.route('products.show', $published->slug).'</loc>', $content);
        $this->assertStringNotContainsString('<loc>'.route('products.show', $inactive->slug).'</loc>', $content);
    }

    public function test_sitemap_includes_published_blog_posts_and_excludes_drafts(): void
    {
        Cache::forget('seo.sitemap');
        $author = User::factory()->create();

        $publishedPost = Post::factory()->create([
            'author_id' => $author->id,
            'title' => 'Artisanal Design 101',
            'slug' => 'artisanal-design-101',
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ]);

        $draftPost = Post::factory()->create([
            'author_id' => $author->id,
            'title' => 'Draft Story',
            'slug' => 'draft-story',
            'status' => PostStatusEnum::Draft,
            'published_at' => null,
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>'.route('blog.show', $publishedPost->slug).'</loc>', $content);
        $this->assertStringNotContainsString('<loc>'.route('blog.show', $draftPost->slug).'</loc>', $content);
    }

    public function test_sitemap_uses_cache_and_refreshes_when_cleared(): void
    {
        Cache::forget('seo.sitemap');

        $this->assertFalse(Cache::has('seo.sitemap'));

        $this->get('/sitemap.xml')->assertOk();

        $this->assertTrue(Cache::has('seo.sitemap'));
    }
}
