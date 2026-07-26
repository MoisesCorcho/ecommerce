<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Product;
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
}
