<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StorefrontLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Route::get('/_test/storefront-layout', fn () => view('layouts.storefront', [
            'slot' => 'STOREFRONT_TEST_SLOT',
        ]));
    }

    public function test_storefront_layout_renders_brand_logo_and_nav_links(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee('Sweeter than honey', false)
            ->assertSee(__('storefront.nav.shop'), false)
            ->assertSee(__('storefront.nav.about'), false)
            ->assertSee(__('storefront.nav.contact'), false);
    }

    public function test_storefront_layout_renders_trailing_nav_icons(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee(__('storefront.nav.favorites'), false)
            ->assertSee(__('storefront.nav.bag'), false);
    }

    public function test_storefront_layout_renders_footer_with_links_social_and_copyright(): void
    {
        $year = (int) date('Y');

        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee(__('storefront.footer.faqs'), false)
            ->assertSee(__('storefront.footer.contact'), false)
            ->assertSee(__('storefront.footer.instagram'), false)
            ->assertSee(__('storefront.footer.tiktok'), false)
            ->assertSee((string) $year, false);
    }

    public function test_storefront_layout_has_mobile_nav_toggle(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee('x-data', false)
            ->assertSee(__('storefront.nav.menu_toggle'), false);
    }

    public function test_storefront_layout_renders_main_content_slot(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee('STOREFRONT_TEST_SLOT', false)
            ->assertSee('<main', false);
    }

    public function test_storefront_layout_shows_login_link_for_guest(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee(route('login'), false)
            ->assertSee(__('storefront.nav.login'), false)
            ->assertDontSee(__('storefront.nav.account'), false);
    }

    public function test_storefront_layout_shows_account_link_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSee(__('storefront.nav.account'), false)
            ->assertDontSee(__('storefront.nav.login'), false);
    }

    public function test_storefront_layout_favorites_link_points_to_wishlist_route(): void
    {
        $this->get('/_test/storefront-layout')
            ->assertOk()
            ->assertSeeHtml('href="'.route('wishlist').'"')
            ->assertDontSeeHtml('href="#" class="transition-colors duration-300 hover:text-soft-gold" aria-label="'.__('storefront.nav.favorites').'"');
    }
}
