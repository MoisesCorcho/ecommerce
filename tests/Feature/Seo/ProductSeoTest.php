<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('es');
    }

    public function test_product_detail_renders_seo_title_description_and_canonical(): void
    {
        $category = Category::factory()->create(['name' => 'Bolsos']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Honey Bag Deluxe',
            'slug' => 'honey-bag-deluxe',
            'description' => 'Un bolso exclusivo confeccionado a mano en Colombia con cuero curtido vegetal de alta resistencia.',
            'material' => 'Cuero vacuno',
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Cop,
            'price' => 350000,
        ]);

        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'path' => 'products/honey-bag.jpg',
            'is_primary' => true,
        ]);

        $response = $this->get(route('products.show', $product->slug));
        $response->assertOk();
        $content = $response->getContent();

        // 1. Title format
        $this->assertStringContainsString('<title>Honey Bag Deluxe | LEEN — Accesorios Artesanales</title>', $content);

        // 2. Canonical tag
        $this->assertStringContainsString('<link rel="canonical" href="'.route('products.show', $product->slug).'">', $content);

        // 3. Meta description
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Un bolso exclusivo confeccionado a mano en Colombia', $content);

        // 4. OpenGraph Product tags
        $this->assertStringContainsString('<meta property="og:type" content="product">', $content);
        $this->assertStringContainsString('<meta property="og:image"', $content);

        // 5. Schema.org Product & Offer
        $this->assertStringContainsString('"@type": "Product"', $content);
        $this->assertStringContainsString('"name": "Honey Bag Deluxe"', $content);
        $this->assertStringContainsString('"@type": "Brand"', $content);
        $this->assertStringContainsString('"@type": "Offer"', $content);
        $this->assertStringContainsString('"priceCurrency": "COP"', $content);

        // 6. Schema.org BreadcrumbList
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $content);

        // 7. Contextual image alt text
        $this->assertStringContainsString('alt="Honey Bag Deluxe', $content);
        $this->assertStringContainsString('LEEN', $content);
    }
}
