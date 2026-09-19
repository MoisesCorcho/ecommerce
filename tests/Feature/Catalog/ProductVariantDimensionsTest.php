<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Products\SizeEnum;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProductVariantDimensionsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_schema_has_dimensions_on_product_variants_and_not_on_products(): void
    {
        $this->assertFalse(
            Schema::hasColumn('products', 'dimensions'),
            'Products table should not have a dimensions column.'
        );

        $this->assertTrue(
            Schema::hasColumn('product_variants', 'dimensions'),
            'Product variants table must have a dimensions column.'
        );
    }

    public function test_create_product_action_persists_dimensions_on_variant(): void
    {
        $category = Category::factory()->create();

        $dto = UpsertProductDTO::fromArray([
            'category_id' => $category->id,
            'name' => 'Mini Bucket Bag',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'MBB-MINI-BLK',
                    'color' => 'Negro',
                    'size' => 'Mini',
                    'dimensions' => '18cm x 15cm x 10cm',
                    'stock' => 10,
                    'is_active' => true,
                    'prices' => [
                        [
                            'currency' => CurrencyEnum::Cop->value,
                            'price' => 450_000,
                        ],
                    ],
                ],
            ],
        ]);

        $product = app(CreateProductAction::class)($dto);

        $this->assertNull($product->getAttribute('dimensions'));
        $variant = $product->variants->first();
        $this->assertNotNull($variant);
        $this->assertSame(SizeEnum::Mini, $variant->size);
        $this->assertSame('18cm x 15cm x 10cm', $variant->dimensions);
    }

    public function test_update_product_action_updates_dimensions_on_variant(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->for($category)->create([
            'name' => 'Initial Bag',
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'INIT-VAR',
            'size' => SizeEnum::Medium,
            'dimensions' => '20cm x 20cm x 10cm',
        ]);

        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 500_000,
        ]);

        $dto = UpsertProductDTO::fromArray([
            'category_id' => $category->id,
            'name' => 'Updated Bag',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => 'INIT-VAR',
                    'color' => 'Miel',
                    'size' => SizeEnum::Maxi,
                    'dimensions' => '35cm x 30cm x 15cm',
                    'stock' => 15,
                    'is_active' => true,
                    'prices' => [
                        [
                            'currency' => CurrencyEnum::Cop->value,
                            'price' => 600_000,
                        ],
                    ],
                ],
            ],
        ]);

        $updatedProduct = app(UpdateProductAction::class)($product, $dto);

        $freshVariant = $updatedProduct->variants()->first();
        $this->assertNotNull($freshVariant);
        $this->assertSame(SizeEnum::Maxi, $freshVariant->size);
        $this->assertSame('35cm x 30cm x 15cm', $freshVariant->dimensions);
    }

    public function test_admin_can_set_variant_dimensions_via_filament_create_and_edit(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->set('data.category_id', $category->id)
            ->set('data.name', 'Honeycomb Mini')
            ->set('data.slug', null)
            ->set('data.is_active', true)
            ->set('data.is_preorder', false)
            ->set('data.variants', [
                [
                    'sku' => 'HC-MINI-01',
                    'color' => 'Dorado',
                    'size' => SizeEnum::Mini->value,
                    'dimensions' => '22cm x 14cm x 6cm',
                    'stock' => 8,
                    'is_active' => true,
                    'prices' => [
                        [
                            'currency' => CurrencyEnum::Cop->value,
                            'price' => 750_000,
                        ],
                    ],
                ],
            ])
            ->set('data.images', [])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $product = Product::query()->where('name', 'Honeycomb Mini')->first();
        $this->assertNotNull($product);
        $variant = $product->variants->first();
        $this->assertNotNull($variant);
        $this->assertSame(SizeEnum::Mini, $variant->size);
        $this->assertSame('22cm x 14cm x 6cm', $variant->dimensions);

        // Test editing via Filament EditProduct
        $editComponent = Livewire::test(EditProduct::class, ['record' => $product->getKey()]);

        /** @var array<string, array<string, mixed>> $variants */
        $variants = $editComponent->get('data.variants');
        $this->assertIsArray($variants);
        $variantKey = array_key_first($variants);
        $this->assertNotNull($variantKey);
        $this->assertSame('22cm x 14cm x 6cm', $variants[$variantKey]['dimensions'] ?? null);

        $editComponent
            ->set("data.variants.{$variantKey}.dimensions", '25cm x 15cm x 7cm')
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $variant->refresh();
        $this->assertSame('25cm x 15cm x 7cm', $variant->dimensions);
    }
}
