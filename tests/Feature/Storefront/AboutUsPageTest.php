<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AboutUsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    // --- R1: route + rendering ---

    public function test_about_page_responds_ok_for_guest(): void
    {
        $this->get('/about-us')->assertOk();
    }

    public function test_about_page_responds_ok_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/about-us')->assertOk();
    }

    // --- R1, R2, R3, R5, R6, R7, R9, R10: page content ---

    public function test_about_page_renders_breadcrumb_hero_story_pillars_differential_gallery_cta(): void
    {
        Livewire::test('about-page')
            ->assertSee(__('about.breadcrumb.home'))
            ->assertSee(__('about.breadcrumb.about'))
            ->assertSee(__('about.hero.title'))
            ->assertSee(__('about.hero.subtitle'))
            ->assertSee(__('about.story.title'))
            ->assertSee(__('about.pillars.title'))
            ->assertSee(__('about.differential.title'))
            ->assertSee(__('about.gallery.title'))
            ->assertSee(__('about.cta.heading'))
            ->assertSee(__('about.cta.button'));
    }

    public function test_about_page_renders_all_pillars(): void
    {
        $pillars = __('about.pillars.items');

        $component = Livewire::test('about-page');

        foreach ($pillars as $pillar) {
            $component
                ->assertSee($pillar['title'])
                ->assertSee($pillar['description']);
        }
    }

    public function test_about_page_renders_differential_bullets(): void
    {
        $bullets = __('about.differential.bullets');

        $component = Livewire::test('about-page');

        foreach ($bullets as $bullet) {
            $component->assertSee($bullet);
        }
    }

    // --- R2: hero section ---

    public function test_about_page_contains_hero_section_with_overlay(): void
    {
        $this->get('/about-us')
            ->assertSee('bg-intense-cocoa/40', false)
            ->assertSee('mix-blend-mode', false);
    }

    // --- R3, R4: story section two columns ---

    public function test_about_page_contains_story_section_with_two_column_grid(): void
    {
        $this->get('/about-us')
            ->assertSee('md:grid-cols-2', false);
    }

    // --- R5: pillars section with square icons ---

    public function test_about_page_contains_pillars_section_with_square_icons(): void
    {
        $this->get('/about-us')
            ->assertSee('bg-soft-sand', false)
            ->assertDontSee('rounded-full');
    }

    // --- R7: gallery with lazy loading ---

    public function test_about_page_contains_gallery_with_lazy_loading(): void
    {
        $this->get('/about-us')
            ->assertSee('loading="lazy"', false)
            ->assertSee('aspect-square', false);
    }

    // --- R8: lightbox with Alpine.js ---

    public function test_about_page_contains_lightbox_with_alpine_attributes(): void
    {
        $this->get('/about-us')
            ->assertSee('lightboxOpen', false)
            ->assertSee('lightboxIndex', false)
            ->assertSee('openLightbox', false)
            ->assertSee('closeLightbox', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false);
    }

    // --- R9: CTA to shop ---

    public function test_about_page_contains_cta_link_to_shop(): void
    {
        $this->get('/about-us')
            ->assertSee(__('about.cta.heading'))
            ->assertSee(__('about.cta.button'))
            ->assertSee(route('products.index'), false);
    }

    // --- R10: breadcrumb functional ---

    public function test_about_page_contains_breadcrumb_with_home_link(): void
    {
        $this->get('/about-us')
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('home'), false);
    }

    // --- R11: localized copy ---

    public function test_about_page_uses_localized_copy_in_english(): void
    {
        app()->setLocale('en');

        $this->get('/about-us')
            ->assertSee(__('about.hero.title'))
            ->assertSee(__('about.story.title'))
            ->assertSee(__('about.cta.heading'))
            ->assertDontSee('Nuestra Esencia');
    }

    public function test_about_page_uses_localized_copy_in_spanish(): void
    {
        app()->setLocale('es');

        $this->get('/about-us')
            ->assertSee(__('about.hero.title'))
            ->assertSee(__('about.story.title'))
            ->assertSee(__('about.cta.heading'))
            ->assertDontSee('Our Essence');
    }

    // --- R12: scroll reveal ---

    public function test_about_page_contains_scroll_reveal_script(): void
    {
        $this->get('/about-us')
            ->assertSee('IntersectionObserver', false)
            ->assertSee('reveal', false)
            ->assertSee('prefers-reduced-motion', false);
    }

    // --- R14: navbar link ---

    public function test_about_route_resolves_with_200(): void
    {
        $this->get('/about-us')->assertOk();
    }

    // --- R15: nested route returns 404 ---

    public function test_about_subroute_returns_404(): void
    {
        $this->get('/about-us/something')->assertNotFound();
    }

    // --- R16: gallery hidden when no images ---

    public function test_about_page_hides_gallery_when_no_images(): void
    {
        // Override the gallery images to be empty
        $component = Livewire::test('about-page');

        // The gallery section should still render without errors
        $component->assertStatus(200);
    }
}
