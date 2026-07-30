<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPagesUseAuthLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_auth_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('auth.back_to_store'))
            ->assertSee(__('auth.login.title'))
            ->assertDontSee(__('storefront.nav.shop'));
    }

    public function test_register_page_renders_with_auth_layout(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('auth.back_to_store'))
            ->assertSee(__('auth.register.title'))
            ->assertDontSee(__('storefront.nav.shop'));
    }
}
