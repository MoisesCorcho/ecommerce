<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_authenticated_buyer_saves_the_selected_variant_to_wishlist_via_toggle(): void
    {
        $user = User::factory()->create();
        $product = $this->createPublishedProduct('Favorite Bag', 'favorite-bag');
        $variant = $product->variants->first();

        Livewire::actingAs($user)
            ->test('product-detail', ['slug' => 'favorite-bag'])
            ->assertSee(__('storefront.products.add_to_favorites_label'))
            ->call('toggleFavorite')
            ->assertSee(__('storefront.products.remove_from_favorites_label'));

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_authenticated_buyer_removes_the_selected_variant_from_wishlist_via_toggle(): void
    {
        $user = User::factory()->create();
        $product = $this->createPublishedProduct('Favorite Bag', 'favorite-bag');
        $variant = $product->variants->first();
        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($user)
            ->test('product-detail', ['slug' => 'favorite-bag'])
            ->assertSee(__('storefront.products.remove_from_favorites_label'))
            ->call('toggleFavorite')
            ->assertSee(__('storefront.products.add_to_favorites_label'));

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_guest_toggling_favorite_is_redirected_to_login_without_saving_anything(): void
    {
        $product = $this->createPublishedProduct('Favorite Bag', 'favorite-bag');

        Livewire::test('product-detail', ['slug' => 'favorite-bag'])
            ->call('toggleFavorite')
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_switching_the_selected_variant_reflects_its_own_independent_favorited_state(): void
    {
        $user = User::factory()->create();
        [$product, $redVariant, $blackVariant] = $this->createPublishedProductWithTwoColorVariants('Favorite Bag', 'favorite-bag');

        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_variant_id' => $redVariant->id,
        ]);

        Livewire::actingAs($user)
            ->test('product-detail', ['slug' => 'favorite-bag'])
            ->assertSet('selectedColor', $redVariant->color)
            ->assertSee(__('storefront.products.remove_from_favorites_label'))
            ->set('selectedColor', $blackVariant->color)
            ->assertSet('selectedVariantId', $blackVariant->id)
            ->assertSee(__('storefront.products.add_to_favorites_label'));

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $redVariant->id,
        ]);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $blackVariant->id,
        ]);
    }

    public function test_favorite_button_shows_imperative_label_distinct_from_past_tense_toast_text(): void
    {
        app()->setLocale('es');

        $user = User::factory()->create();
        $this->createPublishedProduct('Favorite Bag', 'favorite-bag');

        Livewire::actingAs($user)
            ->test('product-detail', ['slug' => 'favorite-bag'])
            ->assertSee(__('storefront.products.add_to_favorites_label'))
            ->assertDontSee(__('storefront.products.added_to_favorites'))
            ->call('toggleFavorite')
            ->assertSee(__('storefront.products.remove_from_favorites_label'))
            ->assertDontSee(__('storefront.products.removed_from_favorites'));
    }

    public function test_toggling_favorite_still_dispatches_the_past_tense_toast_confirmation(): void
    {
        $user = User::factory()->create();
        $this->createPublishedProduct('Favorite Bag', 'favorite-bag');

        Livewire::actingAs($user)
            ->test('product-detail', ['slug' => 'favorite-bag'])
            ->call('toggleFavorite')
            ->assertDispatched('toast', message: __('storefront.products.added_to_favorites'))
            ->call('toggleFavorite')
            ->assertDispatched('toast', message: __('storefront.products.removed_from_favorites'));
    }

    private function createPublishedProduct(string $name, string $slug): Product
    {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-V',
            'is_active' => true,
            'stock' => 5,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => 150_000,
            ]);

        return $product->fresh(['variants.prices']) ?? $product;
    }

    /**
     * @return array{0: Product, 1: ProductVariant, 2: ProductVariant}
     */
    private function createPublishedProductWithTwoColorVariants(string $name, string $slug): array
    {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $redVariant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-RED',
            'color' => 'Red',
            'is_active' => true,
            'stock' => 5,
        ]);

        $blackVariant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper($slug).'-BLACK',
            'color' => 'Black',
            'is_active' => true,
            'stock' => 5,
        ]);

        foreach ([$redVariant, $blackVariant] as $variant) {
            ProductVariantPrice::factory()
                ->for($variant, 'productVariant')
                ->create([
                    'currency' => CurrencyEnum::Cop,
                    'price' => 150_000,
                ]);
        }

        $product = $product->fresh(['variants.prices']) ?? $product;

        return [$product, $redVariant, $blackVariant];
    }
}
