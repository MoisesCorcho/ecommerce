<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_active_nav_item_carries_gold_border_and_inactive_items_do_not(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.orders'));

        $response->assertOk();

        $html = $response->getContent();

        $ordersAnchorStart = strpos($html, 'href="'.route('profile.orders').'"');
        $addressesAnchorStart = strpos($html, 'href="'.route('profile.addresses').'"');

        $this->assertNotFalse($ordersAnchorStart);
        $this->assertNotFalse($addressesAnchorStart);

        $ordersAnchorTag = substr($html, $ordersAnchorStart, 400);
        $addressesAnchorTag = substr($html, $addressesAnchorStart, 400);

        $this->assertStringContainsString('border-soft-gold', $ordersAnchorTag);
        $this->assertStringNotContainsString('border-soft-gold', $addressesAnchorTag);
    }

    public function test_logout_form_is_present_on_account_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();

        $html = $response->getContent();

        $formStart = strpos($html, '<form method="POST" action="'.route('logout').'"');
        $this->assertNotFalse($formStart, 'Logout form with POST action to route(\'logout\') was not found.');

        $formSnippet = substr($html, $formStart, 700);

        $this->assertStringContainsString('name="_token"', $formSnippet);
        $this->assertStringContainsString(__('account.nav.logout'), $formSnippet);
    }

    public function test_order_detail_breadcrumb_has_four_segments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->for($user)->create();

        $response = $this->actingAs($user)->get(route('profile.orders.show', $order));

        $response->assertOk();
        $response->assertSeeInOrder([
            __('account.breadcrumb.home'),
            __('account.breadcrumb.account'),
            __('account.nav.orders'),
            $order->order_number,
        ], false);
    }
}
