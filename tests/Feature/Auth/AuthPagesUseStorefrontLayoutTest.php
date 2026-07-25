<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPagesUseStorefrontLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_storefront_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('storefront.nav.shop'))
            ->assertSee(__('auth.login.title'));
    }

    public function test_register_page_renders_with_storefront_layout(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('storefront.nav.shop'))
            ->assertSee(__('auth.register.title'));
    }
}
