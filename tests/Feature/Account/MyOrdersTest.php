<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_listing_includes_only_paid_and_later_statuses(): void
    {
        $user = User::factory()->create();

        $paid = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Paid]);
        $processing = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Processing]);
        $shipped = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Shipped]);
        $delivered = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Delivered]);
        $pending = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Pending]);
        $cancelled = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Cancelled]);

        $this->actingAs($user);

        Livewire::test('profile-orders-page')
            ->assertViewHas('orders', function ($orders) use ($paid, $processing, $shipped, $delivered, $pending, $cancelled) {
                $ids = $orders->pluck('id')->all();

                return in_array($paid->id, $ids, true)
                    && in_array($processing->id, $ids, true)
                    && in_array($shipped->id, $ids, true)
                    && in_array($delivered->id, $ids, true)
                    && ! in_array($pending->id, $ids, true)
                    && ! in_array($cancelled->id, $ids, true);
            })
            ->assertDontSee(__('account.orders.empty_title'));
    }

    public function test_zero_orders_shows_empty_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('profile-orders-page')
            ->assertSee(__('account.orders.empty_title'))
            ->assertSeeHtml(route('products.index'))
            ->assertDontSee('data-order-card');
    }

    public function test_listing_excludes_orders_from_other_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = Order::factory()->for($user)->create(['status' => OrderStatusEnum::Paid]);
        $foreign = Order::factory()->for($other)->create(['status' => OrderStatusEnum::Paid]);

        $this->actingAs($user);

        Livewire::test('profile-orders-page')
            ->assertViewHas('orders', function ($orders) use ($mine, $foreign) {
                $ids = $orders->pluck('id')->all();

                return in_array($mine->id, $ids, true) && ! in_array($foreign->id, $ids, true);
            });
    }
}
