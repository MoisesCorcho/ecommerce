<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $eligibleOrders = Order::query()
            ->whereIn('status', [OrderStatusEnum::Delivered, OrderStatusEnum::Shipped])
            ->whereNotNull('user_id')
            ->with(['items.productVariant.product', 'user'])
            ->get();

        if ($eligibleOrders->isEmpty()) {
            return;
        }

        $commentsPositive = [
            '¡Excelente calidad de cuero! Los herrajes se sienten súper firmes y elegantes. Llegó antes de lo esperado.',
            'Un bolso hermoso, los acabados son impecables. Vale completamente cada peso.',
            'Superó mis expectativas. El empaque venía hermoso y el producto es de altísima gama.',
            'Perfecto para el uso diario. Me encantan los detalles en dorado y la suavidad del material.',
            'Compré el color rojo y es divino. Varios amigos me han preguntado de dónde es.',
        ];

        $commentsMixed = [
            'El bolso está muy bonito pero la correa me resultó un poco más corta de lo que esperaba.',
            'Buena calidad del cuero, aunque el envío tardó un par de días más de lo previsto.',
            'Es lindo pero el tamaño medium es algo justo si llevás muchas cosas.',
        ];

        $processedPairs = [];

        foreach ($eligibleOrders as $order) {
            $user = $order->user;
            if ($user === null) {
                continue;
            }

            foreach ($order->items as $item) {
                $product = $item->productVariant?->product;
                if ($product === null) {
                    continue;
                }

                $pairKey = $user->id.'-'.$product->id;
                if (isset($processedPairs[$pairKey])) {
                    continue;
                }
                $processedPairs[$pairKey] = true;

                // 80% positive (4-5 stars), 20% mixed (2-3 stars)
                $isPositive = rand(1, 100) <= 80;
                $rating = $isPositive ? rand(4, 5) : rand(2, 3);
                $comment = $isPositive
                    ? $commentsPositive[array_rand($commentsPositive)]
                    : $commentsMixed[array_rand($commentsMixed)];

                // 85% approved, 15% pending moderation
                $isApproved = rand(1, 100) <= 85;

                Review::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'rating' => $rating,
                        'comment' => $comment,
                        'is_approved' => $isApproved,
                        'is_verified_purchase' => true,
                        'created_at' => (clone $order->created_at)->addDays(rand(5, 20)),
                    ]
                );
            }
        }
    }
}
