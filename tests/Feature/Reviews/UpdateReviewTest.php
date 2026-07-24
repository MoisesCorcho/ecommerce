<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\UpdateReviewAction;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_owner_update_remoderates_and_recalculates_verified(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        $review = Review::factory()->for($user)->for($product)->create([
            'rating' => 3,
            'comment' => 'Old',
            'is_approved' => true,
            'is_verified_purchase' => true,
        ]);

        $updated = app(UpdateReviewAction::class)($user, $review, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Updated <i>text</i>',
        ]));

        $this->assertSame(5, $updated->rating);
        $this->assertSame('Updated text', $updated->comment);
        $this->assertFalse($updated->is_approved);
        $this->assertTrue($updated->is_verified_purchase);
    }

    public function test_update_sets_verified_false_when_only_refunded_remain(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Refunded,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        $review = Review::factory()->for($user)->for($product)->create([
            'rating' => 4,
            'is_approved' => true,
            'is_verified_purchase' => true,
        ]);

        $updated = app(UpdateReviewAction::class)($user, $review, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 2,
            'comment' => 'Still editing after refund',
        ]));

        $this->assertFalse($updated->is_approved);
        $this->assertFalse($updated->is_verified_purchase);
    }

    public function test_foreign_user_cannot_update(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($owner)->for($product)->create();

        $this->expectException(ReviewForbiddenException::class);

        app(UpdateReviewAction::class)($stranger, $review, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 1,
            'comment' => 'Nope',
        ]));
    }
}
