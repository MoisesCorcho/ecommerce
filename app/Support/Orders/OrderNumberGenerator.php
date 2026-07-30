<?php

declare(strict_types=1);

namespace App\Support\Orders;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Generates human-readable unique order numbers: ORD-YYYYMMDD-XXXX.
 */
class OrderNumberGenerator
{
    private const int MAX_ATTEMPTS = 12;

    public function generate(): string
    {
        $date = now()->format('Ymd');

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $suffix = strtoupper(Str::random(4));
            $number = "ORD-{$date}-{$suffix}";

            if (! Order::query()->where('order_number', $number)->exists()) {
                return $number;
            }
        }

        return 'ORD-'.$date.'-'.strtoupper(Str::random(8));
    }
}
