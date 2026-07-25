<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_owner_can_view_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->for($user)->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_name' => 'Leather Tote']);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Leather Tote');
    }

    public function test_foreign_order_detail_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = Order::factory()->paid()->for($owner)->create();

        $this->actingAs($stranger)
            ->get(route('profile.orders.show', $order))
            ->assertForbidden();
    }
}
