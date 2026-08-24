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
            ->assertSee(CurrencyEnum::Eur->format(12_500))
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
            ->assertSee('COP')
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

    public function test_price_filter_and_clear_filters_work(): void
    {
        Livewire::test('catalog-list')
            ->call('setPriceFilter', 900_000, 1_000_000)
            ->assertSet('minPrice', 900_000)
            ->assertSet('maxPrice', 1_000_000)
            ->call('clearFilters')
            ->assertSet('minPrice', null)
            ->assertSet('maxPrice', null);
    }

    public function test_size_filter_filters_products_by_variant_size(): void
    {
        $productMini = $this->createPublishedProduct('Mini Bag', 'mini-bag', CurrencyEnum::Cop, 300_000);
        $variantMini = $productMini->variants->first();
        $variantMini->update(['size' => 'Mini']);

        $productMaxi = $this->createPublishedProduct('Maxi Bag', 'maxi-bag', CurrencyEnum::Cop, 600_000);
        $variantMaxi = $productMaxi->variants->first();
        $variantMaxi->update(['size' => 'Maxi']);

        $productMedium = $this->createPublishedProduct('Medium Bag', 'medium-bag', CurrencyEnum::Cop, 450_000);
        $variantMedium = $productMedium->variants->first();
        $variantMedium->update(['size' => 'Medium']);

        Livewire::test('catalog-list')
            ->set('size', ['Mini'])
            ->assertViewHas('products', function ($products) {
                return $products->contains('name', 'Mini Bag')
                    && ! $products->contains('name', 'Maxi Bag')
                    && ! $products->contains('name', 'Medium Bag');
            })
            ->set('size', ['Mini', 'Maxi'])
            ->assertViewHas('products', function ($products) {
                return $products->contains('name', 'Mini Bag')
                    && $products->contains('name', 'Maxi Bag')
                    && ! $products->contains('name', 'Medium Bag');
            });

        $this->get(route('products.index', ['size' => ['Mini']]))
            ->assertOk()
            ->assertSee('Mini Bag')
            ->assertDontSee('Maxi Bag')
            ->assertDontSee('Medium Bag');
    }

    public function test_toggle_size_and_clear_filters_for_size(): void
    {
        Livewire::test('catalog-list')
            ->call('toggleSize', 'Mini')
            ->assertSet('size', ['Mini'])
            ->call('toggleSize', 'Maxi')
            ->assertSet('size', ['Mini', 'Maxi'])
            ->call('toggleSize', 'Mini')
            ->assertSet('size', ['Maxi'])
            ->call('clearFilters')
            ->assertSet('size', []);
    }

    public function test_size_facets_in_view_contains_available_sizes(): void
    {
        $productMini = $this->createPublishedProduct('Mini Bag', 'mini-bag', CurrencyEnum::Cop, 300_000);
        $productMini->variants->first()->update(['size' => 'Mini']);

        $productMaxi = $this->createPublishedProduct('Maxi Bag', 'maxi-bag', CurrencyEnum::Cop, 600_000);
        $productMaxi->variants->first()->update(['size' => 'Maxi']);

        Livewire::test('catalog-list')
            ->assertViewHas('sizes', function (array $sizes) {
                return in_array('Mini', $sizes, true) && in_array('Maxi', $sizes, true);
            })
            ->assertSeeHtml('wire:model.live="size"')
            ->assertSee('Mini')
            ->assertSee('Maxi');
    }
}
