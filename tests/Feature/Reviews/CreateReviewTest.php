<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\CreateReviewAction;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewAlreadyExistsException;
use App\Exceptions\Reviews\ReviewNotAllowedException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_eligible_buyer_creates_pending_verified_review(): void
    {
        [$user, $product] = $this->buyerWithPaidProduct();

        $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => '  Great bag <b>love</b> it  ',
        ]));

        $this->assertFalse($review->is_approved);
        $this->assertTrue($review->is_verified_purchase);
        $this->assertSame(5, $review->rating);
        $this->assertSame('Great bag love it', $review->comment);
        $this->assertSame($user->id, $review->user_id);
        $this->assertSame($product->id, $review->product_id);
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_create_without_eligible_purchase_fails(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->expectException(ReviewNotAllowedException::class);

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => null,
        ]));
    }

    public function test_pending_cancelled_refunded_orders_are_not_eligible(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        foreach ([OrderStatusEnum::Pending, OrderStatusEnum::Cancelled, OrderStatusEnum::Refunded] as $status) {
            $order = Order::factory()->create([
                'user_id' => $user->id,
                'status' => $status,
            ]);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
            ]);
        }

        $this->expectException(ReviewNotAllowedException::class);

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 3,
            'comment' => null,
        ]));
    }

    public function test_duplicate_create_fails(): void
    {
        [$user, $product] = $this->buyerWithPaidProduct();

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => null,
        ]));

        $this->expectException(ReviewAlreadyExistsException::class);

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Again',
        ]));
    }

    public function test_invalid_rating_fails(): void
    {
        [$user, $product] = $this->buyerWithPaidProduct();

        $this->expectException(InvalidReviewRatingException::class);

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => null,
        ]));
    }

    public function test_comment_over_2000_fails(): void
    {
        [$user, $product] = $this->buyerWithPaidProduct();

        $this->expectException(ValidationException::class);

        app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => str_repeat('a', 2001),
        ]));
    }

    public function test_empty_comment_becomes_null(): void
    {
        [$user, $product] = $this->buyerWithPaidProduct();

        $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 2,
            'comment' => '   ',
        ]));

        $this->assertNull($review->comment);
    }

    public function test_processing_shipped_delivered_also_eligible(): void
    {
        foreach ([OrderStatusEnum::Processing, OrderStatusEnum::Shipped, OrderStatusEnum::Delivered] as $status) {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $variant = ProductVariant::factory()->for($product)->create();

            $order = Order::factory()->create([
                'user_id' => $user->id,
                'status' => $status,
                'paid_at' => now(),
            ]);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
            ]);

            $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => null,
            ]));

            $this->assertTrue($review->is_verified_purchase);
            $this->assertFalse($review->is_approved);
        }
    }

    /**
     * @return array{0: User, 1: Product}
     */
    private function buyerWithPaidProduct(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $order = Order::factory()->paid()->create([
            'user_id' => $user->id,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        return [$user, $product];
    }
}
