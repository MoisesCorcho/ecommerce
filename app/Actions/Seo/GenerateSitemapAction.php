<?php

declare(strict_types=1);

namespace App\Actions\Seo;

use App\Enums\Blog\PostStatusEnum;
use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class GenerateSitemapAction
{
    /**
     * Compile the XML sitemap string, caching it for 1 hour.
     */
    public function __invoke(): string
    {
        return Cache::remember('seo.sitemap', 3600, function (): string {
            return $this->buildXml();
        });
    }

    private function buildXml(): string
    {
        $urls = [];

        // 1. Static high-value routes
        $nowIso = now()->toAtomString();
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => $nowIso,
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];
        $urls[] = [
            'loc' => route('products.index'),
            'lastmod' => $nowIso,
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        $urls[] = [
            'loc' => route('blog.index'),
            'lastmod' => $nowIso,
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];
        $urls[] = [
            'loc' => url('/about-us'),
            'lastmod' => $nowIso,
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];
        $urls[] = [
            'loc' => route('contact'),
            'lastmod' => $nowIso,
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];
        $urls[] = [
            'loc' => url('/faq'),
            'lastmod' => $nowIso,
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];

        // 2. Active published products for storefront (COP is primary market)
        $products = Product::query()
            ->publishedForStorefront(CurrencyEnum::Cop)
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('products.show', $product->slug),
                'lastmod' => $product->updated_at?->toAtomString() ?? $nowIso,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 3. Published blog articles
        $posts = Post::query()
            ->where('status', PostStatusEnum::Published)
            ->where('published_at', '<=', now())
            ->select(['id', 'slug', 'updated_at', 'published_at'])
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at?->toAtomString() ?? $post->published_at?->toAtomString() ?? $nowIso,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 4. Categories that have active products
        $categories = Category::query()
            ->whereHas('products', fn ($q) => $q->active())
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('products.index', ['category' => $category->slug]),
                'lastmod' => $category->updated_at?->toAtomString() ?? $nowIso,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // Assemble XML document
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $entry) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
            $xml .= '        <lastmod>'.$entry['lastmod']."</lastmod>\n";
            $xml .= '        <changefreq>'.$entry['changefreq']."</changefreq>\n";
            $xml .= '        <priority>'.$entry['priority']."</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
