<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleAndAdminBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBackfillSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_assigns_admin_role_to_admin_emails_and_customer_to_the_rest(): void
    {
        config(['ecommerce.admin_emails' => ['boss@example.com']]);

        $admin = User::factory()->create(['email' => 'boss@example.com']);
        $customer = User::factory()->create(['email' => 'someone@example.com']);

        $this->seed(RoleAndAdminBackfillSeeder::class);

        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($customer->fresh()->hasRole('customer'));
        $this->assertFalse($customer->fresh()->hasRole('admin'));
    }

    public function test_backfill_is_idempotent(): void
    {
        config(['ecommerce.admin_emails' => ['boss@example.com']]);
        $admin = User::factory()->create(['email' => 'boss@example.com']);

        $this->seed(RoleAndAdminBackfillSeeder::class);
        $this->seed(RoleAndAdminBackfillSeeder::class);

        $this->assertCount(1, $admin->fresh()->roles);
    }
}
