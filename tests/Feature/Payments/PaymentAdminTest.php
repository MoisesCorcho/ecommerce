<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentAdminTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_order_view_shows_payment_attempts(): void
    {
        $this->actingAsAdmin();

        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
            'order_number' => 'ORD-PAY-ADMIN-1',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => PaymentProviderEnum::Bold,
            'status' => PaymentStatusEnum::Pending,
            'amount' => 150_000,
            'external_id' => 'LNK_TEST_ADMIN',
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSuccessful()
            ->assertSee(__('payments.sections.payments'), false)
            ->assertSee('LNK_TEST_ADMIN', false)
            ->assertSee(__('enums.payment_provider.bold'), false);
    }

    public function test_admin_can_still_cancel_pending_order_with_payments(): void
    {
        $this->actingAsAdmin();

        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatusEnum::Pending,
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSuccessful()
            ->callAction('cancel')
            ->assertNotified();

        $this->assertSame(OrderStatusEnum::Cancelled, $order->fresh()->status);
    }
}
