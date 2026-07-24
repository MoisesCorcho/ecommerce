<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Actions\Reviews\DeleteReviewAction;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeleteReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_owner_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($user)->for($product)->create();

        app(DeleteReviewAction::class)($user, $review);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_foreign_user_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $this->expectException(ReviewForbiddenException::class);

        app(DeleteReviewAction::class)($stranger, $review);
    }

    public function test_admin_can_delete_any_review(): void
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $owner = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        app(DeleteReviewAction::class)($admin, $review);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
