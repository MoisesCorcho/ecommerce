<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $specificCustomers = [
            [
                'name' => 'Comprador Ejemplo',
                'last_name' => 'Ejemplo',
                'email' => 'buyer@example.com',
                'phone' => '+573001234567',
                'country' => 'CO',
            ],
            [
                'name' => 'Sofía Rodríguez',
                'last_name' => 'Rodríguez',
                'email' => 'cliente.cop@example.com',
                'phone' => '+573109876543',
                'country' => 'CO',
            ],
            [
                'name' => 'Mateo Fernández',
                'last_name' => 'Fernández',
                'email' => 'cliente.eur@example.com',
                'phone' => '+34612345678',
                'country' => 'ES',
            ],
        ];

        foreach ($specificCustomers as $customerData) {
            $user = User::factory()->create([
                'name' => $customerData['name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'created_at' => now()->subDays(rand(60, 180)),
            ]);

            $isColombia = $customerData['country'] === 'CO';

            Address::factory()->create([
                'user_id' => $user->id,
                'label' => 'Casa',
                'full_name' => $user->name,
                'phone' => $user->phone ?? '+573000000000',
                'address_line_1' => $isColombia ? 'Calle 93 # 11A-28' : 'Calle Mayor 14, 2ºB',
                'city' => $isColombia ? 'Bogotá' : 'Madrid',
                'state' => $isColombia ? 'Cundinamarca' : 'Madrid',
                'country' => $customerData['country'],
                'postal_code' => $isColombia ? '110221' : '28001',
                'is_default' => true,
            ]);
        }

        // Random customer cohort over the past 180 days
        $randomUsers = User::factory(15)->create([
            'created_at' => fn (): Carbon => now()->subDays(rand(5, 180)),
        ]);

        foreach ($randomUsers as $user) {
            $addressCount = rand(1, 3);
            for ($i = 0; $i < $addressCount; $i++) {
                $isDefault = ($i === 0);
                $isColombia = rand(0, 1) === 1;

                Address::factory()->create([
                    'user_id' => $user->id,
                    'label' => $i === 0 ? 'Principal' : ($i === 1 ? 'Trabajo' : 'Otra'),
                    'full_name' => $user->name,
                    'phone' => $user->phone ?? fake()->e164PhoneNumber(),
                    'country' => $isColombia ? 'CO' : 'ES',
                    'city' => $isColombia ? fake()->randomElement(['Bogotá', 'Medellín', 'Cali', 'Barranquilla']) : fake()->randomElement(['Madrid', 'Barcelona', 'Valencia', 'Sevilla']),
                    'state' => $isColombia ? fake()->randomElement(['Cundinamarca', 'Antioquia', 'Valle del Cauca', 'Atlántico']) : fake()->randomElement(['Madrid', 'Cataluña', 'Valencia', 'Andalucía']),
                    'is_default' => $isDefault,
                ]);
            }
        }
    }
}
