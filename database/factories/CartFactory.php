<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => null,
            'currency' => CurrencyEnum::Cop,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'session_id' => Str::uuid()->toString(),
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => CurrencyEnum::Eur,
        ]);
    }
}
