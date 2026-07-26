<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every page in the storefront/account/auth areas shares one literal browser
 * tab title ("Leen") — no per-page suffix. Locked in across a representative
 * sample of pages using each of the two title-setting mechanisms in this app
 * (Livewire full-page `#[Layout]` components, and plain Blade views wrapped
 * by the `<x-layouts::storefront>`/`<x-layouts::auth>` components).
 */
class BrowserTabTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_home_page_shows_the_literal_leen_title(): void
    {
        $this->get('/')->assertOk()->assertSeeHtml('<title>Leen</title>', false);
    }

    public function test_login_page_shows_the_literal_leen_title(): void
    {
        $this->get(route('login'))->assertOk()->assertSeeHtml('<title>Leen</title>', false);
    }

    public function test_wishlist_page_shows_the_literal_leen_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('wishlist'))
            ->assertOk()
            ->assertSeeHtml('<title>Leen</title>', false);
    }

    public function test_order_detail_page_shows_the_literal_leen_title(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->for($user)->create();
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSeeHtml('<title>Leen</title>', false);
    }
}
