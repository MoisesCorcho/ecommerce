<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('app.locale', 'en');
    }

    public function test_switching_to_spanish_stores_the_preference_and_redirects_to_origin(): void
    {
        $this->from(route('faq'))
            ->post(route('locale.update'), ['locale' => 'es'])
            ->assertRedirect(route('faq'))
            ->assertCookie('locale', 'es');

        $this->assertSame('es', session('locale'));
    }

    public function test_switching_to_spanish_applies_the_locale_to_the_next_request(): void
    {
        $this->post(route('locale.update'), ['locale' => 'es']);

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="es"', false);
    }

    public function test_switching_back_to_english_overrides_a_spanish_preference(): void
    {
        $this->withSession(['locale' => 'es'])
            ->post(route('locale.update'), ['locale' => 'en'])
            ->assertCookie('locale', 'en');

        $this->assertSame('en', session('locale'));
    }

    public function test_the_preference_persists_across_requests(): void
    {
        $this->post(route('locale.update'), ['locale' => 'es']);

        $this->get(route('faq'))->assertSee('lang="es"', false);
        $this->get('/')->assertSee('lang="es"', false);
    }

    public function test_the_locale_is_restored_from_the_cookie_when_the_session_is_empty(): void
    {
        $this->withCookie('locale', 'es')
            ->get('/')
            ->assertOk()
            ->assertSee('lang="es"', false);
    }

    public function test_the_session_takes_precedence_over_the_cookie(): void
    {
        $this->withCookie('locale', 'en')
            ->withSession(['locale' => 'es'])
            ->get('/')
            ->assertOk()
            ->assertSee('lang="es"', false);
    }

    public function test_without_any_preference_the_configured_app_locale_is_used(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false);

        $this->assertNull(session('locale'));
    }

    public function test_an_unsupported_locale_is_rejected_and_leaves_the_preference_untouched(): void
    {
        $this->withSession(['locale' => 'es'])
            ->from(route('faq'))
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->assertSame('es', session('locale'));
    }

    public function test_a_request_without_a_locale_field_is_rejected(): void
    {
        $this->withSession(['locale' => 'es'])
            ->from(route('faq'))
            ->post(route('locale.update'), [])
            ->assertSessionHasErrors('locale');

        $this->assertSame('es', session('locale'));
    }

    public function test_a_cookie_holding_an_unsupported_locale_is_ignored(): void
    {
        $this->withCookie('locale', 'fr')
            ->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false);
    }

    public function test_the_navbar_renders_the_switcher_with_both_languages_in_their_own_language(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Español', false)
            ->assertSee('English', false)
            ->assertSee(route('locale.update'), false);
    }

    public function test_the_switcher_does_not_use_country_flags_for_languages(): void
    {
        // A language is not a country: Spanish is not Spain's, English is not the UK's.
        $content = (string) $this->get('/')->assertOk()->getContent();

        foreach (['🇪🇸', '🇬🇧', '🇺🇸', '🇨🇴'] as $flag) {
            $this->assertStringNotContainsString($flag, $content);
        }
    }
}
