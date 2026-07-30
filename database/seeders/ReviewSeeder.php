<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
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

                // Collect purchased variants for this user+product across all orders
                $purchasedVariants = $eligibleOrders
                    ->flatMap(fn (Order $o) => $o->items)
                    ->filter(fn ($oi) => $oi->productVariant?->product_id === $product->id && $oi->order->user_id === $user->id)
                    ->map(fn ($oi) => [
                        'sku' => $oi->sku ?? $oi->productVariant?->sku ?? '',
                        'color' => $oi->productVariant?->color,
                        'size' => $oi->productVariant?->size,
                    ])
                    ->unique(fn (array $v): string => $v['sku'])
                    ->values()
                    ->take(3)
                    ->all();

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
                        'purchased_variants' => $purchasedVariants,
                        'created_at' => (clone $order->created_at)->addDays(rand(5, 20)),
                    ]
                );
            }
        }

        // ── Ensure Honey Bag Medium has enough reviews for pagination testing ──
        $honeyBag = Product::query()->where('slug', 'honey-bag-medium')->first();
        if ($honeyBag !== null) {
            $existingCount = Review::query()
                ->where('product_id', $honeyBag->id)
                ->approved()
                ->count();

            $allVariants = [
                ['sku' => 'D2401-NEGRO', 'color' => 'Negro', 'size' => '36cm x 29cm x 8cm'],
                ['sku' => 'D2401-ROJO', 'color' => 'Rojo', 'size' => '36cm x 29cm x 8cm'],
                ['sku' => 'D2401-BEIGE', 'color' => 'Beige', 'size' => '36cm x 29cm x 8cm'],
            ];

            $users = User::query()
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
                ->take(12)
                ->get();

            // Fill up to 11 approved reviews if needed
            $toCreate = max(0, 11 - $existingCount);
            for ($i = 0; $i < $toCreate; $i++) {
                $user = $users[$i % $users->count()];
                $reviewUser = User::query()->where('id', $user->id)->first();

                Review::query()->updateOrCreate(
                    [
                        'user_id' => $reviewUser->id,
                        'product_id' => $honeyBag->id,
                    ],
                    [
                        'rating' => rand(4, 5),
                        'comment' => $commentsPositive[array_rand($commentsPositive)],
                        'is_approved' => true,
                        'is_verified_purchase' => true,
                        'purchased_variants' => [$allVariants[array_rand($allVariants)]],
                        'created_at' => now()->subDays(rand(1, 30)),
                    ]
                );
            }

            // Ensure at least 1 review has all 3 purchased variants
            $hasTriple = Review::query()
                ->where('product_id', $honeyBag->id)
                ->approved()
                ->get()
                ->contains(fn (Review $r) => is_array($r->purchased_variants) && count($r->purchased_variants) >= 3);

            if (! $hasTriple && $users->isNotEmpty()) {
                $tripleUser = $users->first();
                Review::query()->updateOrCreate(
                    [
                        'user_id' => $tripleUser->id,
                        'product_id' => $honeyBag->id,
                    ],
                    [
                        'rating' => 5,
                        'comment' => 'Compré todos los colores y cada uno es una maravilla. ¡La calidad es consistente!',
                        'is_approved' => true,
                        'is_verified_purchase' => true,
                        'purchased_variants' => $allVariants,
                        'created_at' => now()->subDays(1),
                    ]
                );
            }
        }
    }
}
