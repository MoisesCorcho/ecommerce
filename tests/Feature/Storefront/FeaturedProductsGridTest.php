<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use ReflectionProperty;
use Tests\TestCase;

class FeaturedProductsGridTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    protected function tearDown(): void
    {
        // MySQL DDL (ALTER TABLE) implicitly commits the RefreshDatabase transaction,
        // desynchronizing Laravel's transaction counter from the real PDO state.
        // When the is_featured column exists, manually purge test data then drop the column,
        // resetting the counter and re-opening a clean transaction so RefreshDatabase's
        // rollback does not error on a missing savepoint.
        if (Schema::hasColumn('products', 'is_featured')) {
            DB::table('product_images')->delete();
            DB::table('product_variant_prices')->delete();
            DB::table('product_variants')->delete();
            DB::table('products')->delete();
            DB::table('categories')->delete();

            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('is_featured');
            });

            $this->resetTransactionCount();
            DB::beginTransaction();
        }

        parent::tearDown();
    }

    public function test_featured_products_grid_hidden_when_is_featured_column_absent(): void
    {
        // Current schema state: no is_featured column → empty collection → section hidden (R18).
        $this->assertFalse(Schema::hasColumn('products', 'is_featured'));

        Livewire::test('featured-products-grid')
            ->assertOk()
            ->assertDontSee(__('storefront.home.featured'), false);
    }

    public function test_featured_products_grid_renders_featured_product_card_when_column_exists(): void
    {
        $this->addFeaturedColumn();

        $product = $this->createStorefrontProduct(name: 'Featured Tote', slug: 'featured-tote');
        $this->markFeatured($product);

        Livewire::test('featured-products-grid')
            ->assertOk()
            ->assertSee(__('storefront.home.featured'), false)
            ->assertSee('Featured Tote', false);
    }

    public function test_featured_products_grid_hidden_when_no_featured_products(): void
    {
        $this->addFeaturedColumn();

        // A published product exists but is NOT featured → section hidden (R18).
        $this->createStorefrontProduct(name: 'Regular Tote', slug: 'regular-tote');

        Livewire::test('featured-products-grid')
            ->assertOk()
            ->assertDontSee(__('storefront.home.featured'), false)
            ->assertDontSee('Regular Tote', false);
    }

    public function test_featured_products_grid_excludes_non_featured_products(): void
    {
        $this->addFeaturedColumn();

        $featured = $this->createStorefrontProduct(name: 'Featured Clutch', slug: 'featured-clutch');
        $this->markFeatured($featured);

        $this->createStorefrontProduct(name: 'Regular Clutch', slug: 'regular-clutch');

        Livewire::test('featured-products-grid')
            ->assertOk()
            ->assertSee('Featured Clutch', false)
            ->assertDontSee('Regular Clutch', false);
    }

    public function test_featured_products_grid_excludes_inactive_products(): void
    {
        $this->addFeaturedColumn();

        // Featured but inactive → excluded by publishedForStorefront scope (R17).
        $inactive = Product::factory()->create([
            'name' => 'Inactive Featured',
            'slug' => 'inactive-featured',
            'is_active' => false,
        ]);
        $this->markFeatured($inactive);

        Livewire::test('featured-products-grid')
            ->assertOk()
            ->assertDontSee('Inactive Featured', false);
    }

    /**
     * Add the is_featured column to simulate the backend companion migration.
     */
    private function addFeaturedColumn(): void
    {
        if (! Schema::hasColumn('products', 'is_featured')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->boolean('is_featured')->default(false);
            });
        }

        // DDL implicitly committed the RefreshDatabase transaction. Reset Laravel's
        // transaction counter to match reality, then start a clean transaction so
        // test data is visible to the component and rolled back by tearDown.
        $this->resetTransactionCount();
        DB::beginTransaction();
    }

    /**
     * Reset Laravel's internal transaction counter to zero via reflection.
     *
     * MySQL DDL statements trigger an implicit COMMIT, but Laravel's Connection
     * does not know this — its $transactions counter stays out of sync. This
     * realigns the counter with the real PDO transaction state.
     */
    private function resetTransactionCount(): void
    {
        $connection = DB::connection();
        $property = new ReflectionProperty($connection, 'transactions');
        $property->setAccessible(true);
        $property->setValue($connection, 0);
    }

    private function markFeatured(Product $product): void
    {
        DB::table('products')->where('id', $product->id)->update(['is_featured' => true]);
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
