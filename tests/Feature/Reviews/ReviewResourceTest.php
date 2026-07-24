<?php

declare(strict_types=1);

namespace Tests\Feature\Reviews;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Pages\ViewReview;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_list_reviews(): void
    {
        $this->actingAsAdmin();

        $reviews = Review::factory()->count(2)->create();

        Livewire::test(ListReviews::class)
            ->assertCanSeeTableRecords($reviews);
    }

    public function test_admin_can_approve_from_list(): void
    {
        $this->actingAsAdmin();

        $review = Review::factory()->create([
            'is_approved' => false,
            'rating' => 5,
        ]);

        Livewire::test(ListReviews::class)
            ->callAction(TestAction::make('approve')->table($review))
            ->assertNotified();

        $this->assertTrue($review->fresh()->is_approved);
    }

    public function test_admin_can_view_and_unapprove(): void
    {
        $this->actingAsAdmin();

        $review = Review::factory()->approved()->create([
            'comment' => 'Visible body for infolist',
        ]);

        Livewire::test(ViewReview::class, ['record' => $review->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('Visible body for infolist')
            ->callAction('unapprove')
            ->assertNotified();

        $this->assertFalse($review->fresh()->is_approved);
    }

    public function test_resource_cannot_create(): void
    {
        $this->assertFalse(ReviewResource::canCreate());
    }

    public function test_filter_by_approved(): void
    {
        $this->actingAsAdmin();

        $approved = Review::factory()->approved()->create();
        $pending = Review::factory()->create(['is_approved' => false]);

        Livewire::test(ListReviews::class)
            ->filterTable('is_approved', true)
            ->assertCanSeeTableRecords([$approved])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_admin_delete_from_view(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();
        $review = Review::factory()->for($product)->create();

        Livewire::test(ViewReview::class, ['record' => $review->getRouteKey()])
            ->callAction('delete')
            ->assertNotified();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
