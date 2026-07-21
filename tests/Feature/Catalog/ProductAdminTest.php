<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class ProductAdminTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function validProductForm(Category $category, string $sku = 'SKU-001'): array
    {
        return [
            'category_id' => $category->id,
            'name' => 'Honey Bag',
            'slug' => null,
            'description' => 'Bolso de cuero',
            'material' => 'Cuero',
            'dimensions' => '30x20',
            'is_preorder' => false,
            'is_active' => true,
            'variants' => [
                [
                    'sku' => $sku,
                    'color' => 'Negro',
                    'size' => null,
                    'stock' => 5,
                    'is_active' => true,
                    'prices' => [
                        [
                            'currency' => CurrencyEnum::Cop->value,
                            'price' => 799_000,
                            'compare_at_price' => 899_000,
                        ],
                    ],
                ],
            ],
            'images' => [],
        ];
    }

    public function test_admin_can_create_product_with_variant_and_price_via_filament(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm($this->validProductForm($category, 'LHB-HONEY-1'))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $product = Product::query()->where('name', 'Honey Bag')->first();
        $this->assertNotNull($product);
        $this->assertSame('honey-bag', $product->slug);
        $this->assertTrue($product->is_active);
        $this->assertCount(1, $product->variants);
        $this->assertSame(799_000, $product->variants->first()->prices->first()->price);
        $this->assertSame(CurrencyEnum::Cop, $product->variants->first()->prices->first()->currency);

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords(collect([$product]));
    }

    public function test_create_product_action_persists_graph_and_auto_slug(): void
    {
        $category = Category::factory()->create();

        $product = app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'category_id' => $category->id,
            'name' => 'Travel Tote',
            'slug' => '',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'TOTE-1',
                    'stock' => 2,
                    'is_active' => true,
                    'prices' => [
                        ['currency' => 'EUR', 'price' => 12900],
                    ],
                ],
            ],
        ]));

        $this->assertSame('travel-tote', $product->slug);
        $this->assertSame(12_900, $product->variants->first()->prices->first()->price);
        $this->assertTrue(is_int($product->variants->first()->prices->first()->price));
    }

    public function test_update_product_syncs_variants_prices_and_images(): void
    {
        $product = app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Base Bag',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'BASE-1',
                    'stock' => 1,
                    'is_active' => true,
                    'prices' => [
                        ['currency' => 'COP', 'price' => 100_000],
                    ],
                ],
            ],
            'images' => [
                ['path' => 'products/old.jpg', 'sort_order' => 0, 'is_primary' => true],
            ],
        ]));

        $variantId = $product->variants->first()->id;
        $priceId = $product->variants->first()->prices->first()->id;

        $updated = app(UpdateProductAction::class)($product, UpsertProductDTO::fromArray([
            'name' => 'Base Bag Updated',
            'slug' => 'base-bag-updated',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'id' => $variantId,
                    'sku' => 'BASE-1',
                    'stock' => 10,
                    'is_active' => true,
                    'color' => 'Rojo',
                    'prices' => [
                        [
                            'id' => $priceId,
                            'currency' => 'COP',
                            'price' => 150_000,
                        ],
                        [
                            'currency' => 'EUR',
                            'price' => 4500,
                        ],
                    ],
                ],
                [
                    'sku' => 'BASE-2',
                    'stock' => 3,
                    'is_active' => true,
                    'prices' => [
                        ['currency' => 'COP', 'price' => 160_000],
                    ],
                ],
            ],
            'images' => [
                ['path' => 'products/new.jpg', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'products/second.jpg', 'sort_order' => 1, 'is_primary' => false],
            ],
        ]));

        $this->assertSame('Base Bag Updated', $updated->name);
        $this->assertCount(2, $updated->variants);
        $this->assertSame(150_000, $updated->variants->firstWhere('sku', 'BASE-1')->prices->firstWhere('currency', CurrencyEnum::Cop)->price);
        $this->assertCount(2, $updated->images);
        $this->assertSame(1, $updated->images->where('is_primary', true)->count());
        $this->assertSame('products/new.jpg', $updated->images->firstWhere('is_primary', true)->path);
        $this->assertDatabaseMissing('product_images', ['path' => 'products/old.jpg']);
    }

    public function test_only_one_primary_image_is_kept(): void
    {
        $product = app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Img Bag',
            'is_active' => false,
            'is_preorder' => false,
            'variants' => [],
            'images' => [
                ['path' => 'products/a.jpg', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'products/b.jpg', 'sort_order' => 1, 'is_primary' => true],
            ],
        ]));

        $this->assertSame(1, $product->images->where('is_primary', true)->count());
    }

    public function test_non_integer_price_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpsertProductDTO::fromArray([
            'name' => 'Bad Price',
            'is_active' => false,
            'variants' => [
                [
                    'sku' => 'BAD-1',
                    'is_active' => true,
                    'stock' => 1,
                    'prices' => [
                        ['currency' => 'COP', 'price' => '12.5'],
                    ],
                ],
            ],
        ]);
    }

    public function test_unsupported_currency_is_rejected(): void
    {
        $this->expectException(\ValueError::class);

        UpsertProductDTO::fromArray([
            'name' => 'Bad Currency',
            'is_active' => false,
            'variants' => [
                [
                    'sku' => 'BAD-CUR',
                    'is_active' => true,
                    'stock' => 1,
                    'prices' => [
                        ['currency' => 'USD', 'price' => 100],
                    ],
                ],
            ],
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        ProductVariant::factory()->create(['sku' => 'DUP-SKU']);

        $this->expectException(ValidationException::class);

        app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Dup',
            'is_active' => false,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'DUP-SKU',
                    'stock' => 1,
                    'is_active' => true,
                    'prices' => [
                        ['currency' => 'COP', 'price' => 1000],
                    ],
                ],
            ],
        ]));
    }

    public function test_product_name_is_required_in_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => null,
                'is_active' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_product_slug_syncs_live_from_name_until_manually_edited(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Bolso Honey',
                'is_active' => false,
            ])
            ->assertFormSet([
                'slug' => 'bolso-honey',
            ])
            ->fillForm([
                'name' => 'Bolso Honey XL',
            ])
            ->assertFormSet([
                'slug' => 'bolso-honey-xl',
            ])
            ->fillForm([
                'slug' => 'slug-personalizado',
            ])
            ->fillForm([
                'name' => 'Otro Nombre',
            ])
            ->assertFormSet([
                'slug' => 'slug-personalizado',
            ]);
    }

    public function test_only_one_product_image_can_be_primary_in_form(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Con Imágenes',
                'is_active' => false,
                'variants' => [],
                'images' => [
                    [
                        'path' => 'products/a.jpg',
                        'sort_order' => 0,
                        'is_primary' => true,
                    ],
                    [
                        'path' => 'products/b.jpg',
                        'sort_order' => 1,
                        'is_primary' => false,
                    ],
                ],
            ]);

        /** @var array<string, array<string, mixed>> $images */
        $images = $component->get('data.images');
        $this->assertIsArray($images);
        $keys = array_keys($images);
        $this->assertCount(2, $keys);

        $firstKey = $keys[0];
        $secondKey = $keys[1];

        // fillFormDataForTesting triggers Filament afterStateUpdated (relative paths).
        $component
            ->call('fillFormDataForTesting', [
                "data.images.{$secondKey}.is_primary" => true,
            ], 'data');

        $this->assertFalse((bool) $component->get("data.images.{$firstKey}.is_primary"));
        $this->assertTrue((bool) $component->get("data.images.{$secondKey}.is_primary"));

        // Flip primary back to the first image.
        $component
            ->call('fillFormDataForTesting', [
                "data.images.{$firstKey}.is_primary" => true,
            ], 'data');

        $this->assertTrue((bool) $component->get("data.images.{$firstKey}.is_primary"));
        $this->assertFalse((bool) $component->get("data.images.{$secondKey}.is_primary"));
    }
}
