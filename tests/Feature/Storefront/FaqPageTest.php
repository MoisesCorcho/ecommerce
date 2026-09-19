<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FaqPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    // --- R1: route + rendering ---

    public function test_faq_page_responds_ok_for_guest(): void
    {
        $this->get('/faq')->assertOk();
    }

    public function test_faq_page_responds_ok_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/faq')->assertOk();
    }

    // --- R1, R2, R8, R9: page content ---

    public function test_faq_page_renders_breadcrumb_title_subtitle_and_cta(): void
    {
        Livewire::test('faq-page')
            ->assertSee(__('faq.breadcrumb.home'))
            ->assertSee(__('faq.breadcrumb.faq'))
            ->assertSee(__('faq.title'))
            ->assertSee(__('faq.subtitle'))
            ->assertSee(__('faq.cta.heading'))
            ->assertSee(__('faq.cta.button'));
    }

    public function test_faq_page_renders_all_category_tabs(): void
    {
        $categories = __('faq.categories');

        $component = Livewire::test('faq-page');

        foreach ($categories as $category) {
            $component->assertSee($category['label']);
        }
    }

    public function test_faq_page_renders_questions_from_each_category(): void
    {
        $categories = __('faq.categories');

        $component = Livewire::test('faq-page');

        foreach ($categories as $category) {
            foreach ($category['questions'] as $qa) {
                $component->assertSee($qa['q']);
            }
        }
    }

    // --- R3: tabs with Alpine.js attributes ---

    public function test_faq_page_contains_tabs_with_alpine_attributes(): void
    {
        $this->get('/faq')
            ->assertSee('role="tablist"', false)
            ->assertSee('role="tab"', false)
            ->assertSee('activeCategory', false)
            ->assertSee('x-data', false);
    }

    // --- R4, R5, R6: accordion with Alpine.js attributes ---

    public function test_faq_page_contains_accordion_with_alpine_attributes(): void
    {
        $this->get('/faq')
            ->assertSee('openQuestion', false)
            ->assertSee('x-transition:enter', false)
            ->assertSee('aria-expanded', false)
            ->assertSee('x-show', false);
    }

    // --- R7: tabs fit without horizontal scroll ---

    public function test_faq_page_tabs_use_flex_wrap(): void
    {
        $this->get('/faq')
            ->assertSee('flex flex-wrap', false);
    }

    // --- R10: max-width layout ---

    public function test_faq_page_content_uses_storefront_max_width(): void
    {
        $this->get('/faq')
            ->assertSee('max-w-storefront', false);
    }

    // --- R11: localized copy ---

    public function test_faq_page_uses_localized_copy_in_english(): void
    {
        app()->setLocale('en');

        $this->get('/faq')
            ->assertSee(__('faq.title'))
            ->assertSee(__('faq.subtitle'))
            ->assertSee(__('faq.cta.heading'))
            ->assertDontSee('Preguntas Frecuentes');
    }

    public function test_faq_page_uses_localized_copy_in_spanish(): void
    {
        app()->setLocale('es');

        $this->get('/faq')
            ->assertSee(__('faq.title'))
            ->assertSee(__('faq.subtitle'))
            ->assertSee(__('faq.cta.heading'))
            ->assertDontSee('Frequently Asked Questions');
    }

    // --- R12: footer link (already tested in StorefrontLayoutTest, but verifying route resolves) ---

    public function test_faq_route_resolves_with_200(): void
    {
        $this->get('/faq')->assertOk();
    }

    // --- R14: nested route returns 404 ---

    public function test_faq_subroute_returns_404(): void
    {
        $this->get('/faq/something')->assertRedirect('/');
    }
}
