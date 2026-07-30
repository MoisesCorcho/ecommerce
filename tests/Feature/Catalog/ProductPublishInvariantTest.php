<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Exceptions\Products\ProductCannotBePublishedException;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class ProductPublishInvariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_publish_without_active_variant_with_price(): void
    {
        $this->expectException(ProductCannotBePublishedException::class);

        app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Incomplete',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'INC-1',
                    'stock' => 1,
                    'is_active' => true,
                    'prices' => [],
                ],
            ],
        ]));

        $this->assertDatabaseMissing('products', ['name' => 'Incomplete', 'is_active' => true]);
    }

    public function test_cannot_publish_with_only_inactive_variant(): void
    {
        $this->expectException(ProductCannotBePublishedException::class);

        app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Inactive Variant',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'INA-1',
                    'stock' => 1,
                    'is_active' => false,
                    'prices' => [
                        ['currency' => 'COP', 'price' => 1000],
                    ],
                ],
            ],
        ]));
    }

    public function test_can_publish_with_active_variant_and_any_currency_price(): void
    {
        $product = app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Complete EUR',
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'EUR-1',
                    'stock' => 1,
                    'is_active' => true,
                    'prices' => [
                        ['currency' => 'EUR', 'price' => 9900],
                    ],
                ],
            ],
        ]));

        $this->assertTrue($product->is_active);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    }

    public function test_update_rejects_activation_without_sellable_variant(): void
    {
        $product = app(CreateProductAction::class)(UpsertProductDTO::fromArray([
            'name' => 'Draft',
            'is_active' => false,
            'is_preorder' => false,
            'variants' => [
                [
                    'sku' => 'DFT-1',
                    'stock' => 0,
                    'is_active' => false,
                    'prices' => [],
                ],
            ],
        ]));

        $this->expectException(ProductCannotBePublishedException::class);

        app(UpdateProductAction::class)($product, UpsertProductDTO::fromArray([
            'name' => 'Draft',
            'slug' => $product->slug,
            'is_active' => true,
            'is_preorder' => false,
            'variants' => [
                [
                    'id' => $product->variants->first()->id,
                    'sku' => 'DFT-1',
                    'stock' => 0,
                    'is_active' => false,
                    'prices' => [],
                ],
            ],
        ]));

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_filament_surfaces_publish_error(): void
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('data.name', 'No Price')
            ->set('data.is_active', true)
            ->set('data.is_preorder', false)
            ->set('data.variants', [
                [
                    'sku' => 'NP-1',
                    'stock' => 1,
                    'is_active' => true,
                    'prices' => [],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['is_active']);

        $this->assertDatabaseMissing('products', ['name' => 'No Price']);
    }
}
