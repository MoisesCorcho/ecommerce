<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendWishlistAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully_and_outputs_metrics(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_preorder' => false,
        ]);
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 2, 'is_active' => true]);
        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 200_000,
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'price_when_added' => 250_000,
            'currency_when_added' => CurrencyEnum::Cop,
        ]);

        $this->artisan('app:send-wishlist-alerts')
            ->expectsOutputToContain('Alertas de wishlist procesadas: 1 enviadas')
            ->assertSuccessful();
    }

    public function test_command_is_registered_in_scheduler(): void
    {
        $schedule = app(Schedule::class);

        $events = collect($schedule->events())->filter(function (Event $event): bool {
            return str_contains($event->command ?? '', 'app:send-wishlist-alerts');
        });

        $this->assertNotEmpty($events, 'The command app:send-wishlist-alerts is not registered in the scheduler.');
    }
}
