<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderAdminTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_list_orders(): void
    {
        $this->actingAsAdmin();

        $orders = Order::factory()->count(2)->create();

        Livewire::test(ListOrders::class)
            ->assertCanSeeTableRecords($orders);
    }

    public function test_admin_can_view_and_cancel_pending_order(): void
    {
        $this->actingAsAdmin();

        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
            'order_number' => 'ORD-20260721-TEST',
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSuccessful()
            ->callAction('cancel')
            ->assertNotified();

        $this->assertSame(OrderStatusEnum::Cancelled, $order->fresh()->status);
    }

    public function test_cancel_action_hidden_for_paid_order(): void
    {
        $this->actingAsAdmin();

        $order = Order::factory()->paid()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSuccessful()
            ->assertActionHidden('cancel');
    }
}
