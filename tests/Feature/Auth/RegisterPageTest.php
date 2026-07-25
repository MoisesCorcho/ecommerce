<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_get_register_renders_view(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    public function test_form_has_required_fields_and_csrf(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('action="/register"', false)
            ->assertSee('_token', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="terms"', false);
    }

    public function test_labels_resolve_i18n_keys_en(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('register.fields.name'), false)
            ->assertSee(__('register.fields.email'), false)
            ->assertSee(__('register.fields.password'), false)
            ->assertSee(__('register.fields.password_confirmation'), false)
            ->assertSee(__('register.fields.phone_optional'), false)
            ->assertSee(__('register.placeholders.name'), false)
            ->assertSee(__('register.placeholders.email'), false)
            ->assertSee(__('register.placeholders.phone'), false)
            ->assertDontSee('register.fields.email', false);
    }

    public function test_labels_resolve_i18n_keys_es(): void
    {
        app()->setLocale('es');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('register.fields.name'), false)
            ->assertSee(__('register.fields.email'), false)
            ->assertSee(__('register.fields.password'), false)
            ->assertSee(__('register.fields.password_confirmation'), false)
            ->assertSee(__('register.fields.phone_optional'), false)
            ->assertDontSee('register.fields.email', false);
    }

    public function test_login_link_present(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(route('login'), false)
            ->assertSee(__('register.links.login'), false);
    }

    public function test_terms_gate_markup_present(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="terms"', false)
            ->assertSee('! formValid', false);
    }

    public function test_strength_meter_copy_present(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('register.strength.weak'), false)
            ->assertSee(__('register.strength.medium'), false)
            ->assertSee(__('register.strength.strong'), false);
    }

    public function test_old_input_repopulates_password_stays_blank(): void
    {
        $response = $this->withSession([
            '_old_input' => [
                'name' => 'Ana',
                'email' => 'a@b.test',
                'phone' => '300',
            ],
        ])->get(route('register'));

        $response->assertOk()
            ->assertSee('value="Ana"', false)
            ->assertSee('value="a@b.test"', false)
            ->assertSee('value="300"', false)
            ->assertDontSee('value="secret-password"', false);
    }

    public function test_generic_error_banner_renders_once(): void
    {
        $response = $this->withSession([
            'errors' => [
                'default' => [
                    'messages' => ['email' => ['bad']],
                    'format' => ':message',
                ],
            ],
        ])->get(route('register'));

        $response->assertOk()
            ->assertSee('data-register-error', false)
            ->assertSee('bad', false);

        $matches = substr_count($response->getContent(), 'bad');
        $this->assertSame(1, $matches);
    }

    public function test_minimal_chrome_excludes_storefront_nav(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee(__('storefront.nav.shop'), false);
    }
}
