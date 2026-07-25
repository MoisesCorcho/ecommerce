<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_visible_in_account_history_returns_only_eligible_statuses_for_the_given_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $paid = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Paid]);
        $delivered = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Delivered]);
        $pending = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Pending]);
        $refunded = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Refunded]);
        Order::factory()->for($other)->create(['status' => OrderStatusEnum::Paid]);

        $ids = Order::query()->visibleInAccountHistory($user->id)->pluck('id')->all();

        $this->assertContains($paid->id, $ids);
        $this->assertContains($delivered->id, $ids);
        $this->assertNotContains($pending->id, $ids);
        $this->assertNotContains($refunded->id, $ids);
        $this->assertCount(2, $ids);
    }

    public function test_owned_by_returns_only_reviews_of_the_given_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();

        $mine = Review::factory()->for($user)->for($product)->create();
        Review::factory()->for($other)->for($product)->create();

        $ids = Review::query()->ownedBy($user->id)->pluck('id')->all();

        $this->assertSame([$mine->id], $ids);
    }
}
