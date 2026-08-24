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

class ProductQuickViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Config::set('ecommerce.default_currency', 'COP');
    }

    private function createPublishedProduct(
        string $name = 'Handbag Leen',
        string $slug = 'handbag-leen',
        CurrencyEnum $currency = CurrencyEnum::Cop,
        int $price = 150_000,
        int $stock = 10,
        ?string $color = 'Camel',
        ?string $size = 'medium',
    ): Product {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
            'description' => 'Hermoso bolso artesanal.',
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-V1',
            'is_active' => true,
            'stock' => $stock,
            'color' => $color,
            'size' => $size,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => $currency,
                'price' => $price,
            ]);

        return $product->fresh(['variants.prices', 'images']);
    }

    public function test_quick_view_modal_is_hidden_by_default(): void
    {
        Livewire::test('product-quick-view')
            ->assertSet('showModal', false)
            ->assertSet('productId', null);
    }

    public function test_quick_view_modal_opens_when_open_quick_view_event_is_dispatched(): void
    {
        app()->setLocale('es');
        $product = $this->createPublishedProduct('Cartera Miel', 'cartera-miel', CurrencyEnum::Cop, 220_000);

        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: $product->id)
            ->assertSet('showModal', true)
            ->assertSet('productId', $product->id)
            ->assertSee('Cartera Miel')
            ->assertSee('220.000')
            ->assertSee('Camel')
            ->assertSee('Mediano');
    }

    public function test_quick_view_modal_renders_discounted_price_and_badge(): void
    {
        $product = Product::factory()->create([
            'name' => 'Bolso Oferta',
            'slug' => 'bolso-oferta',
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'BO-01',
            'is_active' => true,
            'stock' => 5,
        ]);

        ProductVariantPrice::factory()->for($variant, 'productVariant')->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 500_000,
            'compare_at_price' => 800_000,
        ]);

        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: $product->id)
            ->assertSee('500.000')
            ->assertSee('800.000')
            ->assertSee('-38%');
    }

    public function test_quick_view_modal_does_not_open_for_invalid_product_id(): void
    {
        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: 99999)
            ->assertSet('showModal', false)
            ->assertSet('productId', null);
    }

    public function test_close_modal_resets_state(): void
    {
        $product = $this->createPublishedProduct();

        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: $product->id)
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('productId', null);
    }

    public function test_can_add_item_to_cart_from_quick_view_modal(): void
    {
        $product = $this->createPublishedProduct('Mochila Honey', 'mochila-honey', CurrencyEnum::Cop, 180_000, stock: 5);

        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: $product->id)
            ->call('addToCart')
            ->assertDispatched('cart-updated')
            ->assertDispatched('toast', message: __('storefront.added_to_cart'));
    }

    public function test_buy_now_adds_to_cart_and_redirects_to_cart_page(): void
    {
        $product = $this->createPublishedProduct('Bolso Express', 'bolso-express', CurrencyEnum::Cop, 250_000, stock: 3);

        Livewire::test('product-quick-view')
            ->dispatch('open-quick-view', productId: $product->id)
            ->call('buyNow')
            ->assertDispatched('cart-updated')
            ->assertRedirect(route('cart.page'));
    }
}
