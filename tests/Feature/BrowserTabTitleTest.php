<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Browser tab title verification across representative pages.
 * Storefront pages share the default SEO brand title (__('seo.default_title')),
 * while auth pages retain the concise "Leen" title.
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
        $this->get('/')->assertOk()->assertSeeHtml('<title>'.e(__('seo.default_title')).'</title>', false);
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
            ->assertSeeHtml('<title>'.e(__('seo.default_title')).'</title>', false);
    }

    public function test_order_detail_page_shows_the_literal_leen_title(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->for($user)->create();
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSeeHtml('<title>'.e(__('seo.default_title')).'</title>', false);
    }
}
