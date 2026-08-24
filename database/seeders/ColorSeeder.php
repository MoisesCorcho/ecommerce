<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'Negro', 'hex_code' => '#201B14'],
            ['name' => 'Cognac', 'hex_code' => '#8B5A2B'],
            ['name' => 'Marrón', 'hex_code' => '#6B4226'],
            ['name' => 'Chocolate', 'hex_code' => '#372621'],
            ['name' => 'Cocoa', 'hex_code' => '#372621'],
            ['name' => 'Tan', 'hex_code' => '#D2B48C'],
            ['name' => 'Camel', 'hex_code' => '#C19A6B'],
            ['name' => 'Arena', 'hex_code' => '#D2B48C'],
            ['name' => 'Crema', 'hex_code' => '#FAF3E0'],
            ['name' => 'Dorado', 'hex_code' => '#D2AE36'],
            ['name' => 'Dune', 'hex_code' => '#C2A67D'],
            ['name' => 'Oliva', 'hex_code' => '#6B6B3C'],
            ['name' => 'Verde', 'hex_code' => '#5A6B3C'],
            ['name' => 'Azul', 'hex_code' => '#2C3E50'],
            ['name' => 'Navy', 'hex_code' => '#2C3E50'],
            ['name' => 'Burdeos', 'hex_code' => '#6B2D3E'],
            ['name' => 'Vino', 'hex_code' => '#6B2D3E'],
            ['name' => 'Rosa', 'hex_code' => '#D4A0A0'],
            ['name' => 'Blush', 'hex_code' => '#D4A0A0'],
            ['name' => 'Rojo', 'hex_code' => '#8B3A3A'],
            ['name' => 'Rojo oscuro', 'hex_code' => '#4A1A1A'],
            ['name' => 'Gris', 'hex_code' => '#8B8B8B'],
            ['name' => 'Piedra', 'hex_code' => '#9B9B8B'],
            ['name' => 'Nude', 'hex_code' => '#D4B5A0'],
            ['name' => 'Whisky', 'hex_code' => '#C5832A'],
            ['name' => 'Amarillo', 'hex_code' => '#C9A227'],
            ['name' => 'Naranja', 'hex_code' => '#C1672B'],
            ['name' => 'Beige', 'hex_code' => '#E3D5B8'],
            ['name' => 'Púrpura', 'hex_code' => '#5B3A5E'],
            ['name' => 'Blanco', 'hex_code' => '#F5F1E8'],
        ];

        foreach ($colors as $index => $color) {
            Color::firstOrCreate(
                ['slug' => Str::slug($color['name'])],
                [
                    'name' => $color['name'],
                    'hex_code' => $color['hex_code'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
