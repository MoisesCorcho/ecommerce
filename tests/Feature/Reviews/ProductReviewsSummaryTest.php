<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\GetProductReviewsSummaryAction;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewsSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_product_summary_is_zero_count_null_average(): void
    {
        $product = Product::factory()->create();

        $summary = app(GetProductReviewsSummaryAction::class)($product);

        $this->assertSame(0, $summary->reviewsCount);
        $this->assertNull($summary->averageRating);
    }

    public function test_public_list_scope_only_approved(): void
    {
        $product = Product::factory()->create();
        $approved = Review::factory()->for($product)->approved()->create();
        Review::factory()->for($product)->create(['is_approved' => false]);

        $ids = Review::query()
            ->where('product_id', $product->id)
            ->approved()
            ->pluck('id')
            ->all();

        $this->assertSame([$approved->id], $ids);
    }
}
