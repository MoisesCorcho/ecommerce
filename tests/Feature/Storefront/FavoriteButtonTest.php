<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoriteButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_favorite_button_renders_disabled_heart_button(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('favorite-button', ['productId' => $product->id])
            ->assertOk()
            ->assertSeeHtml('disabled')
            ->assertSeeHtml('aria-disabled="true"')
            ->assertSeeHtml('<svg');
    }

    public function test_favorite_button_displays_login_required_tooltip(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('favorite-button', ['productId' => $product->id])
            ->assertOk()
            ->assertSee(__('storefront.favorite_login_required'), false);
    }

    public function test_favorite_button_has_accessible_label(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('favorite-button', ['productId' => $product->id])
            ->assertOk()
            ->assertSeeHtml('aria-label=');
    }

    public function test_favorite_button_does_not_wire_backend_action(): void
    {
        $product = $this->createStorefrontProduct();

        Livewire::test('favorite-button', ['productId' => $product->id])
            ->assertOk()
            ->assertDontSeeHtml('wire:click');
    }

    public function test_favorite_button_renders_for_a_different_product(): void
    {
        $product = $this->createStorefrontProduct(slug: 'another-bolso');

        Livewire::test('favorite-button', ['productId' => $product->id])
            ->assertOk()
            ->assertSeeHtml('data-product-id="'.$product->id.'"');
    }

    private function createStorefrontProduct(string $slug = 'bolso-leen-test'): Product
    {
        $category = Category::factory()->create(['name' => 'Handbags']);

        return Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Bolso Leen Test',
            'slug' => $slug,
            'is_active' => true,
        ]);
    }
}
