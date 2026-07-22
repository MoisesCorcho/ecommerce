<?php

declare(strict_types=1);

namespace Tests\Feature\Accounts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AccountsPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_users_resource(): void
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_non_admin_cannot_access_users_resource(): void
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $buyer = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);

        $this->actingAs($buyer)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_users_resource(): void
    {
        $this->get('/admin/users')
            ->assertRedirect();
    }
}
