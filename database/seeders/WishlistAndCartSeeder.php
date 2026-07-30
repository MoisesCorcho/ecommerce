<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WishlistAndCartSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->whereDoesntHave('roles', function ($query): void {
            $query->where('name', 'super_admin');
        })->get();

        $variants = ProductVariant::query()->active()->get();

        if ($variants->isEmpty()) {
            return;
        }

        // 1. Seed Wishlists for registered users
        foreach ($users->take(10) as $user) {
            $wishlistVariants = $variants->random(rand(1, 3));
            foreach ($wishlistVariants as $variant) {
                Wishlist::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'product_variant_id' => $variant->id,
                ]);
            }
        }

        // 2. Seed active Carts for some users (authenticated cart)
        foreach ($users->take(4) as $user) {
            $isCop = rand(0, 1) === 1;
            $cart = Cart::query()->create([
                'user_id' => $user->id,
                'session_id' => null,
                'currency' => $isCop ? CurrencyEnum::Cop : CurrencyEnum::Eur,
            ]);

            $cartVariants = $variants->random(rand(1, 2));
            foreach ($cartVariants as $variant) {
                CartItem::query()->create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(1, 2),
                ]);
            }
        }

        // 3. Seed active guest Carts (session_id set)
        for ($g = 0; $g < 3; $g++) {
            $isCop = $g % 2 === 0;
            $cart = Cart::query()->create([
                'user_id' => null,
                'session_id' => (string) Str::uuid(),
                'currency' => $isCop ? CurrencyEnum::Cop : CurrencyEnum::Eur,
            ]);

            $cartVariants = $variants->random(rand(1, 2));
            foreach ($cartVariants as $variant) {
                CartItem::query()->create([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(1, 2),
                ]);
            }
        }
    }
}
