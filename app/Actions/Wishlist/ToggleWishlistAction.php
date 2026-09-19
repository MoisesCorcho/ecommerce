<?php

declare(strict_types=1);

namespace App\Actions\Wishlist;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Support\Commerce\CurrentCurrency;

final class ToggleWishlistAction
{
    /**
     * Toggles the given product variant on the given user's wishlist.
     *
     * The caller is always responsible for passing the acting user
     * (typically `Auth::user()`); this Action never resolves an implicit
     * "current user" and never accepts a user id from request input.
     *
     * @return bool `true` if the variant ended up saved, `false` if it was removed.
     */
    public function __invoke(User $user, ProductVariant $variant, ?CurrencyEnum $currency = null): bool
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $resolvedCurrency = $currency ?? CurrentCurrency::get();
        $price = $variant->priceIn($resolvedCurrency)?->price;

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => $price,
            'currency_when_added' => $resolvedCurrency->value,
        ]);

        return true;
    }
}
