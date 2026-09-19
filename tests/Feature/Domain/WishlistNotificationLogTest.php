<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Wishlist\WishlistNotificationTypeEnum;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistNotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WishlistNotificationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_notification_log_persists_and_casts_enum_correctly(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();
        $now = Carbon::now()->subMinutes(5);

        $log = WishlistNotificationLog::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => WishlistNotificationTypeEnum::PriceDrop,
            'sent_at' => $now,
        ]);

        $this->assertDatabaseHas('wishlist_notification_logs', [
            'id' => $log->id,
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => 'price_drop',
        ]);

        $freshLog = $log->fresh(['user', 'productVariant']);

        $this->assertTrue($freshLog->user->is($user));
        $this->assertTrue($freshLog->productVariant->is($variant));
        $this->assertSame(WishlistNotificationTypeEnum::PriceDrop, $freshLog->notification_type);
        $this->assertEquals($now->timestamp, $freshLog->sent_at->timestamp);
    }

    public function test_wishlist_model_casts_price_and_currency_when_added(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create();

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 25000,
            'currency_when_added' => CurrencyEnum::Eur,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 25000,
            'currency_when_added' => 'EUR',
        ]);

        $fresh = $wishlist->fresh();

        $this->assertSame(25000, $fresh->price_when_added);
        $this->assertSame(CurrencyEnum::Eur, $fresh->currency_when_added);
    }

    public function test_wishlist_notification_type_enum_cases_and_labels(): void
    {
        $this->assertSame('price_drop', WishlistNotificationTypeEnum::PriceDrop->value);
        $this->assertSame('low_stock', WishlistNotificationTypeEnum::LowStock->value);

        $this->assertNotEmpty(WishlistNotificationTypeEnum::PriceDrop->label());
        $this->assertNotEmpty(WishlistNotificationTypeEnum::LowStock->label());
    }
}
