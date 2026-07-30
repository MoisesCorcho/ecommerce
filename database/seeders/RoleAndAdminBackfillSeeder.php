<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the admin/customer roles and assigns admin to users currently
 * listed in ADMIN_EMAILS. Must run before User::canAccessPanel() depends
 * on hasRole('admin'), or existing admins lose panel access.
 *
 * Idempotent: safe to run more than once, roles are synced not appended.
 */
class RoleAndAdminBackfillSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $adminEmails = config('ecommerce.admin_emails', []);

        User::query()->each(function (User $user) use ($adminEmails): void {
            $user->syncRoles([in_array($user->email, $adminEmails, true) ? 'admin' : 'customer']);
        });
    }
}
