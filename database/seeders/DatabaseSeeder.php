<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Admin access: set ADMIN_EMAILS to include the email below (see .env.example).
     * Public images: run `php artisan storage:link` once per environment so /storage
     * resolves — ProductSeeder copies its own fixture images onto the public disk,
     * it does not depend on any pre-existing files there.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(RoleAndAdminBackfillSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
