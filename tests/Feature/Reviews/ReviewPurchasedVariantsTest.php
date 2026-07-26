<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\CreateReviewAction;
use App\Actions\Reviews\UpdateReviewAction;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Reviews\ReviewVariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPurchasedVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_create_review_stores_purchased_variants(): void
    {
        [$user, $product, $variant] = $this->buyerWithPaidProduct();

        $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]));

        $this->assertNotNull($review->purchased_variants);
        $this->assertCount(1, $review->purchased_variants);
        $this->assertSame($variant->sku, $review->purchased_variants[0]['sku']);
        $this->assertSame($variant->color, $review->purchased_variants[0]['color']);
        $this->assertSame($variant->size, $review->purchased_variants[0]['size']);
    }

    public function test_create_review_stores_multiple_variants(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $variant1 = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-NEGRO-M',
            'color' => 'Negro',
            'size' => 'M',
        ]);
        $variant2 = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-ROJO-S',
            'color' => 'Rojo',
            'size' => 'S',
        ]);

        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant1->id,
            'sku' => $variant1->sku,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant2->id,
            'sku' => $variant2->sku,
        ]);

        $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Love both colors!',
        ]));

        $this->assertCount(2, $review->purchased_variants);
        $this->assertSame('BAG-NEGRO-M', $review->purchased_variants[0]['sku']);
        $this->assertSame('BAG-ROJO-S', $review->purchased_variants[1]['sku']);
    }

    public function test_update_review_refreshes_purchased_variants(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $variant1 = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-1',
            'color' => 'Negro',
            'size' => 'M',
        ]);

        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant1->id,
            'sku' => $variant1->sku,
        ]);

        $review = app(CreateReviewAction::class)($user, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Initial review',
        ]));

        $this->assertCount(1, $review->purchased_variants);

        // Purchase a second variant
        $variant2 = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-2',
            'color' => 'Rojo',
            'size' => 'S',
        ]);

        $order2 = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_variant_id' => $variant2->id,
            'sku' => $variant2->sku,
        ]);

        $updatedReview = app(UpdateReviewAction::class)($user, $review, UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Updated review',
        ]));

        $this->assertCount(2, $updatedReview->purchased_variants);
    }

    public function test_variant_service_limits_to_three_most_recent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Create 4 variants and orders
        for ($i = 1; $i <= 4; $i++) {
            $variant = ProductVariant::factory()->for($product)->create([
                'sku' => "BAG-{$i}",
                'color' => "Color {$i}",
            ]);

            $order = Order::factory()->paid()->create([
                'user_id' => $user->id,
                'created_at' => now()->subDays(5 - $i),
            ]);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'sku' => $variant->sku,
                'created_at' => now()->subDays(5 - $i),
            ]);
        }

        $service = app(ReviewVariantService::class);
        $variants = $service->getRecentPurchasedVariants($user, $product);

        $this->assertCount(3, $variants);
        // Most recent should be first
        $this->assertSame('BAG-4', $variants[0]['sku']);
        $this->assertSame('BAG-3', $variants[1]['sku']);
        $this->assertSame('BAG-2', $variants[2]['sku']);
    }

    public function test_variant_service_returns_empty_for_no_purchases(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $service = app(ReviewVariantService::class);
        $variants = $service->getRecentPurchasedVariants($user, $product);

        $this->assertCount(0, $variants);
    }

    /**
     * @return array{0: User, 1: Product, 2: ProductVariant}
     */
    private function buyerWithPaidProduct(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'BAG-NEGRO-M',
            'color' => 'Negro',
            'size' => 'M',
        ]);

        $order = Order::factory()->paid()->create(['user_id' => $user->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'sku' => $variant->sku,
        ]);

        return [$user, $product, $variant];
    }
}
