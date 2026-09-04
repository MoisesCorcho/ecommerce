<?php

declare(strict_types=1);

namespace App\Actions\Wishlist;

use App\DTOs\Wishlist\WishlistAlertResultDTO;
use App\Enums\Wishlist\WishlistNotificationTypeEnum;
use App\Mail\Wishlist\WishlistLowStockMail;
use App\Mail\Wishlist\WishlistPriceDropMail;
use App\Models\User;
use App\Models\WishlistNotificationLog;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

final class SendWishlistAlertsAction
{
    public function __invoke(): WishlistAlertResultDTO
    {
        if (! (bool) config('ecommerce.wishlist_alerts.enabled', true)) {
            return new WishlistAlertResultDTO(0, 0, 0, 0);
        }

        $lowStockThreshold = (int) config('ecommerce.wishlist_alerts.low_stock_threshold', 3);
        $priceDropCooldownDays = (int) config('ecommerce.wishlist_alerts.price_drop_cooldown_days', 2);
        $lowStockCooldownDays = (int) config('ecommerce.wishlist_alerts.low_stock_cooldown_days', 7);
        $maxAlertsPerUser = (int) config('ecommerce.wishlist_alerts.max_alerts_per_user', 3);

        $priceDropsSent = 0;
        $lowStockSent = 0;
        $skippedCooldown = 0;
        $skippedExcluded = 0;

        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->whereHas('wishlists')
            ->with([
                'wishlists.productVariant.product.images',
                'wishlists.productVariant.prices',
                'wishlists.productVariant.images',
            ])
            ->get();

        $priceDropCooldownThreshold = Carbon::now()->subDays($priceDropCooldownDays);
        $lowStockCooldownThreshold = Carbon::now()->subDays($lowStockCooldownDays);

        foreach ($users as $user) {
            $alertsSentForUser = 0;

            foreach ($user->wishlists as $wishlist) {
                if ($alertsSentForUser >= $maxAlertsPerUser) {
                    break;
                }

                $variant = $wishlist->productVariant;
                if (! $variant || ! $variant->is_active) {
                    $skippedExcluded++;

                    continue;
                }

                $product = $variant->product;
                if (! $product || ! $product->is_active || $product->trashed()) {
                    $skippedExcluded++;

                    continue;
                }

                $currency = $wishlist->currency_when_added ?? CurrentCurrency::default();
                $priceModel = $variant->priceIn($currency);

                if (! $priceModel) {
                    $skippedExcluded++;

                    continue;
                }

                // 1. Evaluar si califica para Rebaja de Precio
                $isPriceDrop = false;
                $oldPrice = 0;
                $newPrice = $priceModel->price;

                if ($wishlist->price_when_added !== null && $priceModel->price < $wishlist->price_when_added) {
                    $isPriceDrop = true;
                    $oldPrice = $wishlist->price_when_added;
                } elseif ($wishlist->price_when_added === null && $priceModel->hasDiscount()) {
                    $isPriceDrop = true;
                    $oldPrice = $priceModel->compare_at_price ?? $priceModel->price;
                }

                if ($isPriceDrop) {
                    $isOnCooldown = WishlistNotificationLog::query()
                        ->where('user_id', $user->id)
                        ->where('product_variant_id', $variant->id)
                        ->where('notification_type', WishlistNotificationTypeEnum::PriceDrop->value)
                        ->where('sent_at', '>=', $priceDropCooldownThreshold)
                        ->exists();

                    if ($isOnCooldown) {
                        $skippedCooldown++;
                    } else {
                        Mail::to($user->email)->queue(new WishlistPriceDropMail(
                            user: $user,
                            variant: $variant,
                            oldPrice: $oldPrice,
                            newPrice: $newPrice,
                            currency: $currency,
                        ));

                        WishlistNotificationLog::create([
                            'user_id' => $user->id,
                            'product_variant_id' => $variant->id,
                            'notification_type' => WishlistNotificationTypeEnum::PriceDrop,
                            'sent_at' => Carbon::now(),
                        ]);

                        $priceDropsSent++;
                        $alertsSentForUser++;

                        continue;
                    }
                }

                // 2. Evaluar si califica para Stock Crítico
                $isLowStock = ! $product->is_preorder
                    && $variant->stock >= 1
                    && $variant->stock <= $lowStockThreshold;

                if ($isLowStock) {
                    $isOnCooldown = WishlistNotificationLog::query()
                        ->where('user_id', $user->id)
                        ->where('product_variant_id', $variant->id)
                        ->where('notification_type', WishlistNotificationTypeEnum::LowStock->value)
                        ->where('sent_at', '>=', $lowStockCooldownThreshold)
                        ->exists();

                    if ($isOnCooldown) {
                        $skippedCooldown++;
                    } else {
                        Mail::to($user->email)->queue(new WishlistLowStockMail(
                            user: $user,
                            variant: $variant,
                            stockRemaining: $variant->stock,
                            currency: $currency,
                        ));

                        WishlistNotificationLog::create([
                            'user_id' => $user->id,
                            'product_variant_id' => $variant->id,
                            'notification_type' => WishlistNotificationTypeEnum::LowStock,
                            'sent_at' => Carbon::now(),
                        ]);

                        $lowStockSent++;
                        $alertsSentForUser++;
                    }
                }
            }
        }

        return new WishlistAlertResultDTO(
            priceDropsSent: $priceDropsSent,
            lowStockSent: $lowStockSent,
            skippedCooldown: $skippedCooldown,
            skippedExcluded: $skippedExcluded,
        );
    }
}
