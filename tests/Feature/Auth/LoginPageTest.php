<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_get_login_renders_view(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_form_has_required_fields_and_csrf(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('action="/login"', false)
            ->assertSee('_token', false);
    }

    public function test_labels_resolve_i18n_keys(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('login.fields.email'), false)
            ->assertSee(__('login.fields.password'), false)
            ->assertDontSee('login.fields.email', false);
    }

    public function test_guarded_links_absent_by_default(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(__('login.links.register'), false)
            ->assertDontSee(__('login.links.forgot_password'), false);
    }

    public function test_guarded_links_present_when_routes_registered(): void
    {
        Route::get('/register', fn () => 'register')->name('register');
        Route::get('/forgot-password', fn () => 'forgot')->name('password.request');
        Route::getRoutes()->refreshNameLookups();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('login.links.register'), false)
            ->assertSee(__('login.links.forgot_password'), false);
    }

    public function test_old_email_repopulates_password_stays_blank(): void
    {
        $response = $this->withSession(['_old_input' => ['email' => 'a@b.test']])
            ->get(route('login'));

        $response->assertOk()
            ->assertSee('value="a@b.test"', false)
            ->assertDontSee('value="secret-password"', false);
    }

    public function test_generic_error_banner_renders_once(): void
    {
        // withViewErrors() shares view data directly on the View factory, but the
        // 'web' middleware group's ShareErrorsFromSession runs during dispatch and
        // unconditionally overwrites 'errors' from the session — so a full HTTP
        // request test must flash the error bag through the session in the same
        // JSON shape Store::marshalErrorBag() expects to rebuild a ViewErrorBag.
        $response = $this->withSession([
            'errors' => [
                'default' => [
                    'messages' => ['email' => ['bad']],
                    'format' => ':message',
                ],
            ],
        ])->get(route('login'));

        $response->assertOk()
            ->assertSee('data-login-error', false)
            ->assertSee('bad', false);

        $matches = substr_count($response->getContent(), 'bad');
        $this->assertSame(1, $matches);
    }

    public function test_minimal_chrome_excludes_storefront_nav(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(__('storefront.nav.shop'), false);
    }
}
