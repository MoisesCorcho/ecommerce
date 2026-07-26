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

class FavoriteButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_authenticated_buyer_toggle_saves_the_variant_when_not_previously_favorited(): void
    {
        $user = User::factory()->create();
        $variant = $this->createStorefrontVariant();

        Livewire::actingAs($user)
            ->test('favorite-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSee(__('storefront.products.add_to_favorites_label'))
            ->call('toggle')
            ->assertSee(__('storefront.products.remove_from_favorites_label'));

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_authenticated_buyer_toggle_removes_the_variant_when_already_favorited(): void
    {
        $user = User::factory()->create();
        $variant = $this->createStorefrontVariant();
        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($user)
            ->test('favorite-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSee(__('storefront.products.remove_from_favorites_label'))
            ->call('toggle')
            ->assertSee(__('storefront.products.add_to_favorites_label'));

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_guest_toggle_is_redirected_to_login_without_saving_anything(): void
    {
        $variant = $this->createStorefrontVariant();

        Livewire::test('favorite-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->call('toggle')
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_favorite_button_keeps_data_product_variant_id_attribute(): void
    {
        $variant = $this->createStorefrontVariant(slug: 'another-bolso');

        Livewire::test('favorite-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSeeHtml('data-product-variant-id="'.$variant->id.'"');
    }

    public function test_favorite_button_has_accessible_label(): void
    {
        $variant = $this->createStorefrontVariant();

        Livewire::test('favorite-button', ['productVariantId' => $variant->id])
            ->assertOk()
            ->assertSeeHtml('aria-label=');
    }

    public function test_favorite_button_shows_imperative_label_distinct_from_past_tense_toast_text(): void
    {
        app()->setLocale('es');

        $user = User::factory()->create();
        $variant = $this->createStorefrontVariant();

        Livewire::actingAs($user)
            ->test('favorite-button', ['productVariantId' => $variant->id])
            ->assertSee(__('storefront.products.add_to_favorites_label'))
            ->assertDontSee(__('storefront.products.added_to_favorites'))
            ->call('toggle')
            ->assertSee(__('storefront.products.remove_from_favorites_label'))
            ->assertDontSee(__('storefront.products.removed_from_favorites'));
    }

    public function test_toggling_favorite_still_dispatches_the_past_tense_toast_confirmation(): void
    {
        $user = User::factory()->create();
        $variant = $this->createStorefrontVariant();

        Livewire::actingAs($user)
            ->test('favorite-button', ['productVariantId' => $variant->id])
            ->call('toggle')
            ->assertDispatched('toast', message: __('storefront.products.added_to_favorites'))
            ->call('toggle')
            ->assertDispatched('toast', message: __('storefront.products.removed_from_favorites'));
    }

    private function createStorefrontVariant(string $slug = 'bolso-leen-test'): ProductVariant
    {
        $product = Product::factory()->create([
            'name' => 'Bolso Leen Test',
            'slug' => $slug,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper(str_replace('-', '', $slug)).'-V',
            'is_active' => true,
            'stock' => 5,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => 150_000,
            ]);

        return $variant->fresh() ?? $variant;
    }
}
