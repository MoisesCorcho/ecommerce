<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_product_detail_adds_variant_to_cart(): void
    {
        $product = $this->createPublishedProduct('Bolso Test', 'bolso-test', stock: 8, price: 15_000);
        $variant = $product->variants->first();
        $this->assertNotNull($variant);

        Livewire::test('product-detail', ['slug' => 'bolso-test'])
            ->assertSet('selectedVariantId', $variant->id)
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertSet('statusMessage', __('storefront.added_to_cart'))
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_cart_page_updates_removes_and_clears_lines(): void
    {
        $product = $this->createPublishedProduct('Cartera', 'cartera-ui', stock: 10, price: 20_000);
        $variant = $product->variants->first();
        $this->assertNotNull($variant);

        Livewire::test('product-detail', ['slug' => 'cartera-ui'])
            ->set('quantity', 2)
            ->call('addToCart');

        Livewire::test('cart-page')
            ->assertSee('Cartera')
            ->assertSee(__('cart.summary.total'))
            ->set('quantities.'.$variant->id, 3)
            ->call('updateLine', $variant->id)
            ->assertSet('statusMessage', __('cart.status.quantity_updated'))
            ->assertSee('60.000');

        $this->assertSame(3, (int) CartItem::query()->where('product_variant_id', $variant->id)->value('quantity'));

        Livewire::test('cart-page')
            ->call('removeLine', $variant->id)
            ->assertSet('statusMessage', __('cart.status.line_removed'))
            ->assertSee(__('cart.empty.title'));

        Livewire::test('product-detail', ['slug' => 'cartera-ui'])
            ->set('quantity', 1)
            ->call('addToCart');

        Livewire::test('cart-page')
            ->call('clearCart')
            ->assertSet('statusMessage', __('cart.status.cart_cleared'))
            ->assertSee(__('cart.empty.title'));

        $this->assertSame(0, CartItem::query()->count());
    }

    public function test_change_quantity_clamps_and_delegates_to_update_line(): void
    {
        $product = $this->createPublishedProduct('Mochila', 'mochila-test', stock: 5, price: 10_000);
        $variant = $product->variants->first();
        $this->assertNotNull($variant);

        Livewire::test('product-detail', ['slug' => 'mochila-test'])
            ->set('quantity', 1)
            ->call('addToCart');

        Livewire::test('cart-page')
            ->call('changeQuantity', $variant->id, 3);

        $this->assertSame(3, (int) CartItem::query()->where('product_variant_id', $variant->id)->value('quantity'));

        Livewire::test('cart-page')
            ->call('changeQuantity', $variant->id, 99);

        $this->assertSame(5, (int) CartItem::query()->where('product_variant_id', $variant->id)->value('quantity'));
    }

    public function test_cart_page_route_renders(): void
    {
        $this->get(route('cart.page'))
            ->assertOk()
            ->assertSee(__('cart.page.title'))
            ->assertSee(__('cart.empty.title'));
    }

    public function test_add_to_cart_rejects_insufficient_stock_with_message(): void
    {
        $product = $this->createPublishedProduct('Stock bajo', 'stock-bajo', stock: 1, price: 5_000);
        $variant = $product->variants->first();
        $this->assertNotNull($variant);

        Livewire::test('product-detail', ['slug' => 'stock-bajo'])
            ->set('quantity', 5)
            ->call('addToCart')
            ->assertSet('statusMessage', null)
            ->assertNotSet('errorMessage', null);

        $this->assertSame(0, CartItem::query()->count());
    }

    private function createPublishedProduct(
        string $name,
        string $slug,
        int $stock,
        int $price,
    ): Product {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-V',
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => $price,
            ]);

        return $product->fresh(['variants.prices']) ?? $product;
    }
}
