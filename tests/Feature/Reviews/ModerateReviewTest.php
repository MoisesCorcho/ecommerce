<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\ApproveReviewAction;
use App\Actions\Reviews\GetProductReviewsSummaryAction;
use App\Actions\Reviews\UnapproveReviewAction;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ModerateReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        Config::set('ecommerce.admin_emails', ['admin@example.com']);
    }

    public function test_admin_approve_makes_review_public_and_affects_summary(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $product = Product::factory()->create();
        $review = Review::factory()->for($product)->create([
            'rating' => 4,
            'is_approved' => false,
        ]);

        $summaryBefore = app(GetProductReviewsSummaryAction::class)($product);
        $this->assertSame(0, $summaryBefore->reviewsCount);
        $this->assertNull($summaryBefore->averageRating);

        $approved = app(ApproveReviewAction::class)($admin, $review);

        $this->assertTrue($approved->is_approved);

        $summary = app(GetProductReviewsSummaryAction::class)($product);
        $this->assertSame(1, $summary->reviewsCount);
        $this->assertSame(4.0, $summary->averageRating);
    }

    public function test_admin_unapprove_removes_from_public_summary(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $product = Product::factory()->create();
        $review = Review::factory()->for($product)->approved()->create(['rating' => 5]);

        app(UnapproveReviewAction::class)($admin, $review);

        $this->assertFalse($review->fresh()->is_approved);

        $summary = app(GetProductReviewsSummaryAction::class)($product);
        $this->assertSame(0, $summary->reviewsCount);
        $this->assertNull($summary->averageRating);
    }

    public function test_non_admin_cannot_moderate(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $review = Review::factory()->create(['is_approved' => false]);

        $this->expectException(ReviewForbiddenException::class);

        app(ApproveReviewAction::class)($user, $review);
    }

    public function test_summary_averages_only_approved(): void
    {
        $product = Product::factory()->create();
        Review::factory()->for($product)->approved()->create(['rating' => 5]);
        Review::factory()->for($product)->approved()->create(['rating' => 3]);
        Review::factory()->for($product)->create(['rating' => 1, 'is_approved' => false]);

        $summary = app(GetProductReviewsSummaryAction::class)($product);

        $this->assertSame(2, $summary->reviewsCount);
        $this->assertSame(4.0, $summary->averageRating);
    }
}
