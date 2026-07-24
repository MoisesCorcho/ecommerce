<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_product_detail_shows_only_approved_reviews(): void
    {
        $product = $this->createPublishedProduct('Review Bag', 'review-bag');
        $approved = Review::factory()->for($product)->approved()->verifiedPurchase()->create([
            'rating' => 5,
            'comment' => 'Public praise',
        ]);
        Review::factory()->for($product)->create([
            'rating' => 1,
            'comment' => 'Hidden pending',
            'is_approved' => false,
        ]);

        Livewire::test('product-detail', ['slug' => 'review-bag'])
            ->assertSee(__('reviews.ui.section_title'))
            ->assertSee('Public praise')
            ->assertDontSee('Hidden pending')
            ->assertSee(__('reviews.ui.login_required'));

        $this->assertDatabaseHas('reviews', ['id' => $approved->id, 'is_approved' => true]);
    }

    public function test_eligible_buyer_can_create_review_from_pdp(): void
    {
        $user = User::factory()->create();
        $product = $this->createPublishedProduct('Buyer Bag', 'buyer-bag');
        $variant = $product->variants->first();
        $this->assertNotNull($variant);

        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        $this->actingAs($user);

        Livewire::test('product-detail', ['slug' => 'buyer-bag'])
            ->set('reviewRating', 5)
            ->set('reviewComment', 'Love this piece')
            ->call('saveReview')
            ->assertSet('reviewErrorMessage', null)
            ->assertSet('reviewStatusMessage', __('reviews.notifications.saved_pending'));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Love this piece',
            'is_approved' => false,
            'is_verified_purchase' => true,
        ]);
    }

    public function test_guest_cannot_create_review_from_pdp(): void
    {
        $this->createPublishedProduct('Guest Bag', 'guest-bag');

        Livewire::test('product-detail', ['slug' => 'guest-bag'])
            ->set('reviewRating', 5)
            ->set('reviewComment', 'Should fail')
            ->call('saveReview')
            ->assertSet('reviewErrorMessage', __('reviews.ui.login_required'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_authenticated_non_buyer_sees_not_eligible(): void
    {
        $user = User::factory()->create();
        $this->createPublishedProduct('No Buy', 'no-buy');

        $this->actingAs($user);

        Livewire::test('product-detail', ['slug' => 'no-buy'])
            ->assertSee(__('reviews.ui.not_eligible'))
            ->assertDontSee(__('reviews.actions.submit'));
    }

    public function test_owner_can_delete_review_from_pdp(): void
    {
        $user = User::factory()->create();
        $product = $this->createPublishedProduct('Delete Bag', 'delete-bag');
        $variant = $product->variants->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Paid,
            'paid_at' => now(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        Review::factory()->for($user)->for($product)->create([
            'rating' => 4,
            'comment' => 'Mine',
        ]);

        $this->actingAs($user);

        Livewire::test('product-detail', ['slug' => 'delete-bag'])
            ->call('deleteReview')
            ->assertSet('reviewStatusMessage', __('reviews.notifications.deleted'));

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
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
}
