<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Wishlist\SendWishlistAlertsAction;
use Illuminate\Console\Command;

final class SendWishlistAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-wishlist-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evalúa y despacha alertas automáticas de rebaja de precio y stock crítico para wishlists';

    /**
     * Execute the console command.
     */
    public function handle(SendWishlistAlertsAction $action): int
    {
        $result = $action();

        $this->info("Alertas de wishlist procesadas: {$result->totalSent()} enviadas ({$result->priceDropsSent} rebajas, {$result->lowStockSent} stock bajo). Omitidas por cooldown: {$result->skippedCooldown}, excluidas por reglas: {$result->skippedExcluded}.");

        return self::SUCCESS;
    }
}
