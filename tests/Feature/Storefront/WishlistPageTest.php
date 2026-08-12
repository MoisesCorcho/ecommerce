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

class WishlistPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_guest_is_redirected_to_login_when_visiting_wishlist_route(): void
    {
        $this->get('/wishlist')->assertRedirect(route('login'));
    }

    public function test_wishlist_page_lists_only_the_authenticated_users_saved_variants(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownVariant = $this->createPublishedVariant('Owner Bag', 'owner-bag');
        $otherVariant = $this->createPublishedVariant('Other Bag', 'other-bag');

        Wishlist::factory()->create(['user_id' => $owner->id, 'product_variant_id' => $ownVariant->id]);
        Wishlist::factory()->create(['user_id' => $otherUser->id, 'product_variant_id' => $otherVariant->id]);

        Livewire::actingAs($owner)
            ->test('wishlist-page')
            ->assertSee('Owner Bag')
            ->assertDontSee('Other Bag')
            ->assertViewHas('items', fn ($items) => $items->count() === 1
                && $items->first()['variant']->id === $ownVariant->id);
    }

    public function test_wishlist_page_shows_empty_state_with_link_to_products(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSee(__('storefront.wishlist.empty_title'))
            ->assertSeeHtml(route('products.index'));
    }

    public function test_two_different_variants_of_the_same_product_render_as_two_separate_grid_entries(): void
    {
        $user = User::factory()->create();
        [, $redVariant, $blackVariant] = $this->createPublishedProductWithTwoColorVariants('Two Color Bag', 'two-color-bag');

        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $redVariant->id]);
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $blackVariant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSeeHtml('data-wishlist-item="'.$redVariant->id.'"')
            ->assertSeeHtml('data-wishlist-item="'.$blackVariant->id.'"')
            ->assertViewHas('items', fn ($items) => $items->count() === 2);
    }

    public function test_unpublished_product_variant_is_marked_no_longer_available_and_add_to_cart_is_disabled(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Discontinued Bag', 'discontinued-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        $variant->product->update(['is_active' => false]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSee(__('storefront.wishlist.unavailable_badge'))
            ->assertViewHas('items', fn ($items) => $items->first()['isAvailable'] === false)
            ->call('addToCart', $variant->id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_out_of_stock_variant_shows_badge_and_add_to_cart_is_disabled(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Sold Out Bag', 'sold-out-bag', stock: 0);
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSee(__('storefront.out_of_stock'))
            ->assertViewHas('items', fn ($items) => $items->first()['isOutOfStock'] === true)
            ->call('addToCart', $variant->id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_to_cart_from_wishlist_page_adds_item_without_removing_it_from_wishlist(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Available Bag', 'available-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->call('addToCart', $variant->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_add_to_cart_from_wishlist_page_adds_the_exact_saved_variant_not_an_arbitrary_one(): void
    {
        $user = User::factory()->create();
        [, $redVariant, $blackVariant] = $this->createPublishedProductWithTwoColorVariants('Two Color Bag', 'two-color-bag');

        // The user saved the SECOND variant (black), not the product's first variant (red).
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $blackVariant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->call('addToCart', $blackVariant->id);

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $blackVariant->id,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $redVariant->id,
        ]);
    }

    public function test_remove_from_wishlist_updates_the_listing_without_a_redirect(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Removable Bag', 'removable-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSee('Removable Bag')
            ->call('removeFromWishlist', $variant->id)
            ->assertNoRedirect()
            ->assertDontSee('Removable Bag');

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_removing_a_variant_not_owned_by_the_user_does_not_touch_another_users_wishlist(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();
        $victimVariant = $this->createPublishedVariant('Victim Bag', 'victim-bag');

        Wishlist::factory()->create(['user_id' => $victim->id, 'product_variant_id' => $victimVariant->id]);

        Livewire::actingAs($attacker)
            ->test('wishlist-page')
            ->call('removeFromWishlist', $victimVariant->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $victim->id,
            'product_variant_id' => $victimVariant->id,
        ]);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $attacker->id,
            'product_variant_id' => $victimVariant->id,
        ]);
    }

    public function test_wishlist_page_renders_a_toast_confirmation_listener(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSeeHtml('x-on:toast.window');
    }

    public function test_removing_an_item_dispatches_a_toast_with_undo_payload_and_restore_re_adds_the_variant(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Undo Bag', 'undo-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->call('removeFromWishlist', $variant->id)
            ->assertDispatched(
                'toast',
                message: __('storefront.products.removed_from_favorites'),
                undoEvent: 'restoreWishlistVariant',
                undoPayload: $variant->id,
            );

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->call('restoreWishlistVariant', $variant->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_restoring_a_variant_twice_in_a_row_does_not_toggle_it_back_off(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Double Restore Bag', 'double-restore-bag');

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->call('restoreWishlistVariant', $variant->id)
            ->call('restoreWishlistVariant', $variant->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_restoring_a_variant_is_ownership_safe(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();
        $variant = $this->createPublishedVariant('Ownership Restore Bag', 'ownership-restore-bag');

        Livewire::actingAs($attacker)
            ->test('wishlist-page')
            ->call('restoreWishlistVariant', $variant->id);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $attacker->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $victim->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_remove_button_uses_a_trash_icon_instead_of_the_heart_icon(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPublishedVariant('Icon Bag', 'icon-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertDontSeeHtml('M21 8.25c0-2.485')
            ->assertSeeHtml('14.74 9l-.346 9');
    }

    public function test_wishlist_page_paginates_and_reports_the_true_total_count(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 13; $i++) {
            $variant = $this->createPublishedVariant("Bag {$i}", "bag-{$i}");
            Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);
        }

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertViewHas('items', fn ($items) => $items->count() === 12 && $items->total() === 13)
            ->assertSee('(13)')
            ->assertSeeHtml('role="navigation"');
    }

    public function test_unavailable_item_shows_unavailable_message_and_link_while_out_of_stock_item_shows_out_of_stock_message(): void
    {
        $user = User::factory()->create();

        $unavailableVariant = $this->createPublishedVariant('Gone Bag', 'gone-bag');
        $unavailableVariant->product->update(['is_active' => false]);
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $unavailableVariant->id]);

        $outOfStockVariant = $this->createPublishedVariant('Sold Out Message Bag', 'sold-out-message-bag', stock: 0);
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $outOfStockVariant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSee(__('storefront.wishlist.unavailable_badge'))
            ->assertSeeHtml(route('products.index'))
            ->assertSee(__('storefront.out_of_stock'));
    }

    public function test_color_swatch_renders_next_to_the_color_label_when_the_variant_has_a_color(): void
    {
        $user = User::factory()->create();
        [, $redVariant] = $this->createPublishedProductWithTwoColorVariants('Swatch Bag', 'swatch-bag');
        Wishlist::factory()->create(['user_id' => $user->id, 'product_variant_id' => $redVariant->id]);

        Livewire::actingAs($user)
            ->test('wishlist-page')
            ->assertSeeHtml('style="background-color:');
    }

    private function createPublishedVariant(string $name, string $slug, int $stock = 5): ProductVariant
    {
        $product = Product::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => strtoupper(str_replace('-', '_', $slug)).'-V',
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->create([
                'currency' => CurrencyEnum::Cop,
                'price' => 150_000,
            ]);

        return $variant->fresh(['product', 'prices']) ?? $variant;
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

        return [
            $product->fresh() ?? $product,
            $redVariant->fresh(['product', 'prices']) ?? $redVariant,
            $blackVariant->fresh(['product', 'prices']) ?? $blackVariant,
        ];
    }
}
