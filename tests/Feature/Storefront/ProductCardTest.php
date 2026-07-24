<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_product_card_renders_image_with_4_to_5_aspect_ratio_and_sharp_edges(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('aspect-[4/5]')
            ->assertDontSeeHtml('rounded');
    }

    public function test_product_card_renders_category_eyebrow_with_uppercase_tracking(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('tracking-wider')
            ->assertSee($product->category->name, false);
    }

    public function test_product_card_renders_product_name_with_headline_style(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('font-headline-sm text-xl')
            ->assertSee($product->name, false);
    }

    public function test_product_card_renders_integer_price_with_headline_sm(): void
    {
        $product = $this->createStorefrontProduct(price: 150_000);
        $formattedPrice = number_format(150_000, 0, ',', '.');

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('font-headline-sm text-2xl')
            ->assertSee($formattedPrice, false);
    }

    public function test_product_card_renders_link_to_product_detail(): void
    {
        $product = $this->createStorefrontProduct();
        $detailUrl = route('products.show', $product->slug);

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSee($detailUrl, false);
    }

    public function test_product_card_applies_hover_zoom_on_image(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('duration-700')
            ->assertSeeHtml('group-hover:scale-');
    }

    public function test_product_card_provides_alt_text_on_image(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('alt=')
            ->assertSee($product->name, false);
    }

    public function test_product_card_nests_add_to_cart_button(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSee(__('storefront.add_to_cart'), false)
            ->assertSeeHtml("wire:click=\"\$dispatch('add-to-cart'");
    }

    public function test_product_card_nests_favorite_button(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('data-favorite-button')
            ->assertSee(__('storefront.favorite_login_required'), false);
    }

    public function test_product_card_renders_placeholder_when_no_image(): void
    {
        $product = $this->createStorefrontProduct(withImage: false);

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSee(__('storefront.no_image'), false)
            ->assertDontSeeHtml('<img');
    }

    public function test_product_card_uses_group_class_for_hover_context(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('product-card', ['product' => $product, 'currency' => 'COP'])
            ->assertOk()
            ->assertSeeHtml('group');
    }

    /**
     * Create a storefront-ready Product with eager-loaded relations.
     *
     * @param  bool  $withImage  Whether to attach a primary image.
     */
    private function createStorefrontProduct(
        string $name = 'Bolso Leen Test',
        string $slug = 'bolso-leen-test',
        int $stock = 10,
        int $price = 150_000,
        bool $withImage = true,
    ): Product {
        $category = Category::factory()->create(['name' => 'Handbags']);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper(str_replace('-', '', $slug)).'-V',
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => $price,
            ]);

        if ($withImage) {
            ProductImage::factory()->for($product)->primary()->create([
                'path' => 'products/test-image.jpg',
            ]);
        }

        $currency = CurrencyEnum::Cop;

        return $product->fresh([
            'category',
            'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
            'variants' => fn ($q) => $q->active()->withPriceIn($currency)->with([
                'prices' => fn ($pq) => $pq->where('currency', $currency->value),
            ]),
        ]) ?? $product;
    }
}
