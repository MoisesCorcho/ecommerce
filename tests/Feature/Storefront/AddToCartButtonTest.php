<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddToCartButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_add_to_cart_button_renders_with_label(): void
    {
        $variant = $this->createVariantWithStock(10);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSee(__('storefront.add_to_cart'), false)
            ->assertSeeHtml('data-add-to-cart');
    }

    public function test_add_to_cart_creates_cart_item_and_sets_success_status(): void
    {
        $variant = $this->createVariantWithStock(10, 15_000);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', __('storefront.added_to_cart'))
            ->assertDispatched('cart-updated');

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_to_cart_uses_default_quantity_of_one(): void
    {
        $variant = $this->createVariantWithStock(5);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', __('storefront.added_to_cart'));

        $this->assertSame(1, (int) CartItem::query()
            ->where('product_variant_id', $variant->id)
            ->value('quantity'));
    }

    public function test_add_to_cart_rejects_insufficient_stock_with_error_message(): void
    {
        $variant = $this->createVariantWithStock(1, 5_000);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->set('quantity', 5)
            ->call('addToCart')
            ->assertSet('statusMessage', null)
            ->assertNotSet('errorMessage', null);

        $this->assertSame(0, CartItem::query()->where('product_variant_id', $variant->id)->count());
    }

    public function test_add_to_cart_renders_success_message_with_view_cart_link_after_adding(): void
    {
        $variant = $this->createVariantWithStock(10);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->call('addToCart')
            ->assertSee(__('storefront.added_to_cart'), false)
            ->assertSee(__('storefront.view_cart'), false)
            ->assertSee(route('cart.page'), false);
    }

    public function test_add_to_cart_renders_error_message_on_failure(): void
    {
        $variant = $this->createVariantWithStock(1, 5_000);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->set('quantity', 5)
            ->call('addToCart')
            ->assertSeeHtml('data-add-error');
    }

    public function test_add_to_cart_button_has_brand_styling(): void
    {
        $variant = $this->createVariantWithStock(10);

        Livewire::test('add-to-cart-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSeeHtml('bg-intense-cocoa')
            ->assertSeeHtml('text-silk-cream');
    }

    private function createVariantWithStock(int $stock, int $price = 15_000): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);

        $variant = ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => $price,
            ]);

        return $variant;
    }
}
