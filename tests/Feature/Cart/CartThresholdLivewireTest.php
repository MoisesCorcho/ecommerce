<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartThresholdLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_cart_page_shows_progress_banner_when_below_threshold(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $this->createProductWithPrice(10_000, CurrencyEnum::Eur);
        $variant = $product->variants->first();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2, // 200 EUR (20.000 cents) vs 300 EUR threshold
        ]);

        Livewire::test('cart-page')
            ->assertSee(__('cart.threshold.progress', ['amount' => CurrencyEnum::Eur->format(10_000)]))
            ->assertSee('67%')
            ->assertDontSee(__('cart.threshold.unlocked'))
            ->assertDontSee(__('cart.summary.threshold_discount'));
    }

    public function test_cart_page_shows_unlocked_banner_and_discount_row_when_threshold_met(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $this->createProductWithPrice(15_000, CurrencyEnum::Eur);
        $variant = $product->variants->first();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2, // 300 EUR (30.000 cents) -> 10% threshold met
        ]);

        Livewire::test('cart-page')
            ->assertSee(__('cart.threshold.unlocked'))
            ->assertSee(__('cart.summary.threshold_discount'))
            ->assertSee(CurrencyEnum::Eur->format(30_000))
            ->assertSee('−'.CurrencyEnum::Eur->format(3_000))
            ->assertSee(CurrencyEnum::Eur->format(27_000));
    }

    public function test_cart_page_reactively_unlocks_discount_when_quantity_is_increased(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $this->createProductWithPrice(15_000, CurrencyEnum::Eur, stock: 10);
        $variant = $product->variants->first();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1, // 150 EUR (below 300 EUR threshold)
        ]);

        Livewire::test('cart-page')
            ->assertSee(__('cart.threshold.progress', ['amount' => CurrencyEnum::Eur->format(15_000)]))
            ->assertDontSee(__('cart.threshold.unlocked'))
            ->set('quantities.'.$variant->id, 2)
            ->call('updateLine', $variant->id)
            ->assertSee(__('cart.threshold.unlocked'))
            ->assertSee(__('cart.summary.threshold_discount'))
            ->assertSee('−'.CurrencyEnum::Eur->format(3_000));
    }

    public function test_checkout_page_displays_threshold_discount_in_order_summary(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $this->createProductWithPrice(15_000, CurrencyEnum::Eur);
        $variant = $product->variants->first();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency' => CurrencyEnum::Eur,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2, // 300 EUR
        ]);

        Livewire::test('checkout-page')
            ->assertSee(__('orders.fields.threshold_discount'))
            ->assertSee('−'.CurrencyEnum::Eur->format(3_000))
            ->assertSeeHtml('text-success')
            ->assertDontSeeHtml('text-terracotta');
    }

    private function createProductWithPrice(int $price, CurrencyEnum $currency, int $stock = 10): Product
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'stock' => $stock,
            'is_active' => true,
        ]);

        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => $currency,
            'price' => $price,
        ]);

        return $product->fresh(['variants.prices']);
    }
}
