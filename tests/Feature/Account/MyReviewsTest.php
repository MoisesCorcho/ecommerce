<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_listing_includes_own_reviews_with_moderation_status(): void
    {
        $user = User::factory()->create();
        $approvedProduct = Product::factory()->create();
        $pendingProduct = Product::factory()->create();
        $approved = Review::factory()->for($user)->for($approvedProduct)->approved()->create();
        $pending = Review::factory()->for($user)->for($pendingProduct)->create(['is_approved' => false]);

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->assertViewHas('reviews', function ($reviews) use ($approved, $pending) {
                $ids = $reviews->pluck('id')->all();

                return in_array($approved->id, $ids, true) && in_array($pending->id, $ids, true);
            })
            ->assertDontSee(__('account.reviews.empty_title'));
    }

    public function test_zero_reviews_shows_empty_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->assertSee(__('account.reviews.empty_title'))
            ->assertSeeHtml(route('products.index'))
            ->assertDontSee('data-review-card');
    }

    public function test_editing_own_review_returns_it_to_pending(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->approved()->create([
            'rating' => 3,
            'comment' => 'Old comment',
        ]);

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->call('edit', $review->id)
            ->set('rating', 5)
            ->set('comment', 'Updated comment')
            ->call('save')
            ->assertHasNoErrors();

        $review->refresh();
        $this->assertSame(5, $review->rating);
        $this->assertSame('Updated comment', $review->comment);
        $this->assertFalse($review->is_approved);
    }

    public function test_deleting_own_review_removes_it(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->call('delete', $review->id);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_edit_or_delete_foreign_review(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($owner)->for($product)->create();

        $this->actingAs($stranger);

        Livewire::test('profile-reviews-page')
            ->call('edit', $review->id)
            ->assertForbidden();

        Livewire::test('profile-reviews-page')
            ->call('delete', $review->id)
            ->assertSet('errorMessage', __('reviews.errors.forbidden'));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_delete_confirmation_does_not_render_native_browser_dialog(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        $html = Livewire::test('profile-reviews-page')->html();

        $this->assertStringNotContainsString('wire:confirm', $html);
    }

    public function test_deleting_a_review_requires_confirming_first(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        $component = Livewire::test('profile-reviews-page')
            ->call('confirmDelete', $review->id)
            ->assertSet('confirmingDeleteId', $review->id);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);

        $component->call('delete', $review->id);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_cancelling_delete_confirmation_resets_state_without_deleting(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->call('confirmDelete', $review->id)
            ->assertSet('confirmingDeleteId', $review->id)
            ->call('cancelDeleteConfirmation')
            ->assertSet('confirmingDeleteId', null);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_editing_a_review_clears_a_pending_delete_confirmation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->call('confirmDelete', $review->id)
            ->assertSet('confirmingDeleteId', $review->id)
            ->call('edit', $review->id)
            ->assertSet('confirmingDeleteId', null);
    }

    public function test_review_shows_sku_and_variant_label_of_the_purchased_variant(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();
        $review = Review::factory()->for($user)->for($product)->create();

        $order = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Delivered]);
        OrderItem::factory()->for($order)->for($variant, 'productVariant')->create([
            'sku' => 'LHB-999-TST',
            'variant_label' => 'Rojo / M',
        ]);

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->assertSee('LHB-999-TST')
            ->assertSee('Rojo / M');

        // Ensure the referenced review stays part of the fixture setup.
        $this->assertNotNull($review->id);
    }

    public function test_review_renders_gracefully_without_a_matching_eligible_purchase(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->for($user)->for($product)->create();

        $this->actingAs($user);

        Livewire::test('profile-reviews-page')
            ->assertDontSee(__('account.orders.sku_label'));
    }
}
