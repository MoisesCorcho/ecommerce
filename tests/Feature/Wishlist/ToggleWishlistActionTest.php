<?php

declare(strict_types=1);

namespace Tests\Feature\Wishlist;

use App\Actions\Wishlist\ToggleWishlistAction;
use App\Enums\Commerce\CurrencyEnum;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleWishlistActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_the_variant_when_not_previously_wishlisted(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();

        $result = app(ToggleWishlistAction::class)($user, $variant);

        $this->assertTrue($result);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_it_removes_the_variant_when_already_wishlisted(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();
        Wishlist::factory()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);

        $result = app(ToggleWishlistAction::class)($user, $variant);

        $this->assertFalse($result);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_it_does_not_create_duplicate_rows_for_the_same_user_and_variant(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();

        app(ToggleWishlistAction::class)($user, $variant);
        app(ToggleWishlistAction::class)($user, $variant);
        $result = app(ToggleWishlistAction::class)($user, $variant);

        $this->assertTrue($result);
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_it_only_toggles_the_wishlist_of_the_explicitly_passed_user(): void
    {
        $actingUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $variant = ProductVariant::factory()->create();
        Wishlist::factory()->create([
            'user_id' => $otherUser->id,
            'product_variant_id' => $variant->id,
        ]);

        $result = app(ToggleWishlistAction::class)($actingUser, $variant);

        $this->assertTrue($result);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $actingUser->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $otherUser->id,
            'product_variant_id' => $variant->id,
        ]);
        $this->assertDatabaseCount('wishlists', 2);
    }

    public function test_it_captures_price_and_currency_when_adding_to_wishlist_with_explicit_currency(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();
        $variant->prices()->create([
            'currency' => CurrencyEnum::Eur,
            'price' => 12_500,
            'compare_at_price' => 15_000,
        ]);

        $result = app(ToggleWishlistAction::class)($user, $variant, CurrencyEnum::Eur);

        $this->assertTrue($result);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 12_500,
            'currency_when_added' => 'EUR',
        ]);
    }

    public function test_it_captures_price_and_currency_defaulting_to_current_currency(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();
        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 250_000,
        ]);

        $result = app(ToggleWishlistAction::class)($user, $variant);

        $this->assertTrue($result);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => 'COP',
        ]);
    }
}
