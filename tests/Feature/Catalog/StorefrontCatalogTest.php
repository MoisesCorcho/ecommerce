<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Config::set('ecommerce.default_currency', 'COP');
    }

    private function createPublishedProduct(
        string $name,
        string $slug,
        CurrencyEnum $currency = CurrencyEnum::Cop,
        int $price = 100_000,
        bool $productActive = true,
        bool $variantActive = true,
    ): Product {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $productActive,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-V',
            'is_active' => $variantActive,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => $currency,
                'price' => $price,
            ]);

        return $product->fresh(['variants.prices', 'images']);
    }

    public function test_list_shows_only_products_publishable_in_default_currency(): void
    {
        $visible = $this->createPublishedProduct('Visible COP', 'visible-cop', CurrencyEnum::Cop, 250_000);
        $this->createPublishedProduct('Inactive', 'inactive-prod', CurrencyEnum::Cop, 100_000, productActive: false);
        $this->createPublishedProduct('Only EUR', 'only-eur', CurrencyEnum::Eur, 9900);
        $this->createPublishedProduct('Inactive Variant', 'inactive-variant', CurrencyEnum::Cop, 100_000, variantActive: false);

        $noPrice = Product::factory()->create([
            'name' => 'No Price',
            'slug' => 'no-price',
            'is_active' => true,
        ]);
        ProductVariant::factory()->for($noPrice)->create([
            'sku' => 'NOPRICE-V',
            'is_active' => true,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Visible COP')
            ->assertSee('250.000')
            ->assertDontSee('Inactive')
            ->assertDontSee('Only EUR')
            ->assertDontSee('Inactive Variant')
            ->assertDontSee('No Price');

        $this->assertTrue(
            Product::query()->publishedForStorefront(CurrencyEnum::Cop)->whereKey($visible->id)->exists()
        );
    }

    public function test_default_currency_config_affects_listing(): void
    {
        $this->createPublishedProduct('EUR Bag', 'eur-bag', CurrencyEnum::Eur, 12_500);
        $this->createPublishedProduct('COP Bag', 'cop-bag', CurrencyEnum::Cop, 200_000);

        Config::set('ecommerce.default_currency', 'EUR');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('EUR Bag')
            ->assertSee('12.500')
            ->assertDontSee('COP Bag');
    }

    public function test_detail_shows_product_and_priced_variants_as_integers(): void
    {
        $product = $this->createPublishedProduct('Detail Bag', 'detail-bag', CurrencyEnum::Cop, 799_000);

        $eurOnlyVariant = ProductVariant::factory()->for($product)->create([
            'sku' => 'DETAIL-EUR',
            'color' => 'Azul',
            'is_active' => true,
        ]);
        ProductVariantPrice::factory()->for($eurOnlyVariant, 'productVariant')->create([
            'currency' => CurrencyEnum::Eur,
            'price' => 8900,
        ]);

        $response = $this->get(route('products.show', 'detail-bag'));

        $response->assertOk()
            ->assertSee('Detail Bag')
            ->assertSee('799.000')
            ->assertSee('data-price="799000"', false)
            ->assertSee('data-currency="COP"', false)
            ->assertDontSee('DETAIL-EUR')
            ->assertDontSee('Azul');
    }

    public function test_detail_404_for_missing_slug(): void
    {
        $this->get(route('products.show', 'does-not-exist'))
            ->assertNotFound();
    }

    public function test_detail_404_for_inactive_product(): void
    {
        $this->createPublishedProduct('Hidden', 'hidden-prod', CurrencyEnum::Cop, 100_000, productActive: false);

        $this->get(route('products.show', 'hidden-prod'))
            ->assertNotFound();
    }

    public function test_detail_404_when_no_price_in_default_currency(): void
    {
        $this->createPublishedProduct('Eur Only Detail', 'eur-only-detail', CurrencyEnum::Eur, 5000);

        $this->get(route('products.show', 'eur-only-detail'))
            ->assertNotFound();
    }

    public function test_livewire_catalog_index_component_renders(): void
    {
        $this->createPublishedProduct('LW Product', 'lw-product', CurrencyEnum::Cop, 50_000);

        Livewire::test('catalog-list')
            ->assertOk()
            ->assertSee('LW Product')
            ->assertSee('50.000');
    }
}
