<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_home_route_returns_200_and_renders_home_view(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('home');
    }

    public function test_home_route_uses_storefront_layout_not_welcome_page(): void
    {
        // The storefront layout renders the nav "Shop" link; the default welcome page does not.
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.nav.shop'), false)
            ->assertDontSee('bg-[#FDFDFC]', false);
    }

    public function test_home_renders_hero_with_title_and_cta_to_products(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.home.hero_title'), false)
            ->assertSee(route('products.index'), false)
            ->assertSee(__('storefront.home.hero_cta'), false);
    }

    public function test_home_renders_hero_with_la_belle_aurore_accent(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.home.hero_subtitle'), false)
            ->assertSeeHtml('font-labelle-aurore');
    }

    public function test_home_renders_brand_story_section_with_about_us_cta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.home.story_title'), false)
            ->assertSee('/about-us', false)
            ->assertSee(__('storefront.home.story_cta'), false);
    }

    public function test_home_renders_benefits_section_on_soft_sand_background(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.home.benefits_title'), false)
            ->assertSeeHtml('bg-soft-sand');
    }

    public function test_home_renders_instagram_section_with_external_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('storefront.home.instagram_title'), false)
            ->assertSee('https://www.instagram.com/leen_____________________/', false);
    }

    public function test_home_renders_categories_and_featured_grids(): void
    {
        // Even with empty data, the Livewire grid components must be present on the page.
        // Use the same detection logic as Livewire's assertSeeLivewire macro:
        // the component name is embedded as JSON inside wire:snapshot attributes.
        $response = $this->get('/')->assertOk();

        foreach (['categories-grid', 'featured-products-grid'] as $component) {
            $needle = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            $this->assertStringContainsString(
                $needle,
                $response->getContent(),
                "Cannot find Livewire component [{$component}] rendered on page.",
            );
        }
    }
}
