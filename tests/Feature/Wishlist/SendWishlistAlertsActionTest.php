<?php

declare(strict_types=1);

namespace Tests\Feature\Wishlist;

use App\Actions\Wishlist\SendWishlistAlertsAction;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Wishlist\WishlistNotificationTypeEnum;
use App\Mail\Wishlist\WishlistLowStockMail;
use App\Mail\Wishlist\WishlistPriceDropMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistNotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendWishlistAlertsActionTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->category = Category::factory()->create();
    }

    private function createPublishedProduct(array $productAttributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_preorder' => false,
        ], $productAttributes));
    }

    private function createActiveVariant(Product $product, array $variantAttributes = [], int $price = 250_000, CurrencyEnum $currency = CurrencyEnum::Cop): ProductVariant
    {
        $variant = ProductVariant::factory()->for($product)->create(array_merge([
            'is_active' => true,
            'stock' => 10,
        ], $variantAttributes));

        $variant->prices()->create([
            'currency' => $currency,
            'price' => $price,
        ]);

        return $variant;
    }

    public function test_it_dispatches_price_drop_mail_when_current_price_is_lower_than_price_when_added(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, [], 200_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(1, $result->priceDropsSent);
        $this->assertSame(0, $result->lowStockSent);

        Mail::assertQueued(WishlistPriceDropMail::class, function (WishlistPriceDropMail $mail) use ($user, $variant): bool {
            return $mail->hasTo($user->email)
                && $mail->variant->id === $variant->id
                && $mail->oldPrice === 250_000
                && $mail->newPrice === 200_000;
        });

        $this->assertDatabaseHas('wishlist_notification_logs', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => 'price_drop',
        ]);
    }

    public function test_it_dispatches_price_drop_mail_for_legacy_wishlist_item_with_active_discount(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = ProductVariant::factory()->for($product)->create(['is_active' => true, 'stock' => 10]);
        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 180_000,
            'compare_at_price' => 220_000,
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => null,
            'currency_when_added' => null,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(1, $result->priceDropsSent);
        Mail::assertQueued(WishlistPriceDropMail::class);
    }

    public function test_it_dispatches_low_stock_mail_when_stock_is_between_1_and_3_units(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, ['stock' => 2], 250_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(1, $result->lowStockSent);
        $this->assertSame(0, $result->priceDropsSent);

        Mail::assertQueued(WishlistLowStockMail::class, function (WishlistLowStockMail $mail) use ($user, $variant): bool {
            return $mail->hasTo($user->email)
                && $mail->variant->id === $variant->id
                && $mail->stockRemaining === 2;
        });

        $this->assertDatabaseHas('wishlist_notification_logs', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => 'low_stock',
        ]);
    }

    public function test_it_suppresses_alert_if_same_notification_type_sent_within_7_days(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, [], 200_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        WishlistNotificationLog::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => WishlistNotificationTypeEnum::PriceDrop,
            'sent_at' => Carbon::now()->subDays(3),
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(0, $result->totalSent());
        $this->assertSame(1, $result->skippedCooldown);
        Mail::assertNothingQueued();
    }

    public function test_it_allows_low_stock_alert_even_if_price_drop_was_sent_3_days_ago(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, ['stock' => 1], 250_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        WishlistNotificationLog::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'notification_type' => WishlistNotificationTypeEnum::PriceDrop,
            'sent_at' => Carbon::now()->subDays(3),
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(1, $result->lowStockSent);
        Mail::assertQueued(WishlistLowStockMail::class);
    }

    public function test_it_excludes_variants_with_zero_stock_from_low_stock_alerts(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, ['stock' => 0], 250_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(0, $result->totalSent());
        Mail::assertNothingQueued();
    }

    public function test_it_excludes_preorder_products_from_low_stock_alerts(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct(['is_preorder' => true]);
        $variant = $this->createActiveVariant($product, ['stock' => 1], 250_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(0, $result->totalSent());
        Mail::assertNothingQueued();
    }

    public function test_it_excludes_inactive_variants_and_unpublished_products(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct(['is_active' => false]);
        $variant = $this->createActiveVariant($product, ['stock' => 1, 'is_active' => true], 200_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(0, $result->totalSent());
        Mail::assertNothingQueued();
    }

    public function test_it_excludes_unverified_and_soft_deleted_users(): void
    {
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);
        $deletedUser = User::factory()->create(['email_verified_at' => now()]);
        $deletedUser->delete();

        $product = $this->createPublishedProduct();
        $variant = $this->createActiveVariant($product, ['stock' => 2], 200_000, CurrencyEnum::Cop);

        Wishlist::create([
            'user_id' => $unverifiedUser->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        Wishlist::create([
            'user_id' => $deletedUser->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(0, $result->totalSent());
        Mail::assertNothingQueued();
    }

    public function test_it_throttles_alerts_to_maximum_per_user_per_run(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = $this->createPublishedProduct();

        for ($i = 1; $i <= 5; $i++) {
            $variant = $this->createActiveVariant($product, ['stock' => 2], 200_000, CurrencyEnum::Cop);
            Wishlist::create([
                'user_id' => $user->id,
                'product_variant_id' => $variant->id,
                'price_when_added' => 250_000,
                'currency_when_added' => CurrencyEnum::Cop,
            ]);
        }

        $action = app(SendWishlistAlertsAction::class);
        $result = $action();

        $this->assertSame(3, $result->totalSent());
        Mail::assertQueued(WishlistPriceDropMail::class, 3);
    }
}
