<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Enums\Products\SizeEnum;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderAndPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();
        $adminUser = User::query()->whereIn('email', config('ecommerce.admin_emails', []))->first()
            ?? User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->first();

        $variants = ProductVariant::query()->with(['product', 'prices'])->get();
        $coupons = Coupon::query()->where('is_active', true)->get();

        if ($variants->isEmpty()) {
            return;
        }

        // Generate 50 historical orders over the past 180 days
        $totalOrders = 50;

        for ($i = 1; $i <= $totalOrders; $i++) {
            $daysAgo = rand(0, 180);
            $createdAt = now()->subDays($daysAgo)->subHours(rand(1, 23))->subMinutes(rand(1, 59));

            // 70% COP (Colombia/Bold), 30% EUR (Spain/Stripe)
            $isCop = rand(1, 100) <= 70;
            $currency = $isCop ? CurrencyEnum::Cop : CurrencyEnum::Eur;
            $provider = $isCop ? PaymentProviderEnum::Bold : PaymentProviderEnum::Stripe;

            // Guarantee at least 2 delivered purchases for the admin user to test the reviews feature
            if ($i <= 2 && $adminUser !== null) {
                $user = $adminUser;
                $isGuest = false;
            } else {
                $isGuest = rand(1, 100) <= 25;
                $user = ($isGuest || $users->isEmpty()) ? null : $users->random();
            }

            if ($user !== null) {
                $address = Address::query()->where('user_id', $user->id)->first();
                $email = $user->email;
                $fullName = $user->name;
                $phone = $user->phone ?? '+573001234567';
                $line1 = $address?->address_line_1 ?? 'Calle 100 # 15-20';
                $city = $address?->city ?? ($isCop ? 'Bogotá' : 'Madrid');
                $state = $address?->state ?? ($isCop ? 'Cundinamarca' : 'Madrid');
                $country = $isCop ? 'CO' : 'ES';
                $postalCode = $address?->postal_code ?? ($isCop ? '110111' : '28001');
                $addressId = $address?->id;
            } else {
                $fullName = fake()->name();
                $email = fake()->safeEmail();
                $phone = fake()->e164PhoneNumber();
                $line1 = fake()->streetAddress();
                $city = $isCop ? 'Medellín' : 'Barcelona';
                $state = $isCop ? 'Antioquia' : 'Cataluña';
                $country = $isCop ? 'CO' : 'ES';
                $postalCode = $isCop ? '050001' : '08001';
                $addressId = null;
            }

            // Determine status according to order age (always Delivered for admin's initial orders)
            $status = ($i <= 2 && $adminUser !== null)
                ? OrderStatusEnum::Delivered
                : $this->determineOrderStatus($daysAgo);

            // Select 1 to 3 variant items
            $orderVariants = $variants->random(rand(1, min(3, $variants->count())));
            $subtotal = 0;
            $itemsData = [];

            foreach ($orderVariants as $variant) {
                $priceObj = $variant->priceIn($currency);
                $unitPrice = $priceObj?->price ?? ($isCop ? 750_000 : 18_750);
                $quantity = rand(1, 2);
                $subtotal += ($unitPrice * $quantity);

                $sizeLabel = $variant->size instanceof SizeEnum ? $variant->size->label() : ($variant->size ?? '');
                $itemsData[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_label' => trim(($variant->color ?? '').' '.$sizeLabel),
                    'sku' => $variant->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                ];
            }

            // Apply coupon conditionally (~30% of orders)
            $appliedCoupon = null;
            $discount = 0;

            if (rand(1, 100) <= 30 && $coupons->isNotEmpty()) {
                $matchingCoupons = $coupons->filter(function (Coupon $c) use ($currency, $subtotal): bool {
                    if ($c->currency !== null && $c->currency !== $currency) {
                        return false;
                    }
                    if ($c->min_order_amount !== null && $subtotal < $c->min_order_amount) {
                        return false;
                    }

                    return true;
                });

                if ($matchingCoupons->isNotEmpty()) {
                    /** @var Coupon $appliedCoupon */
                    $appliedCoupon = $matchingCoupons->random();
                    $discount = $this->calculateDiscount($appliedCoupon, $subtotal);
                }
            }

            $shippingCost = $isCop ? 15_000 : 500; // $15,000 COP or 5.00 EUR (500 cents)
            $total = max(0, $subtotal - $discount) + $shippingCost;

            $paidAt = in_array($status, [OrderStatusEnum::Paid, OrderStatusEnum::Processing, OrderStatusEnum::Shipped, OrderStatusEnum::Delivered, OrderStatusEnum::Refunded], true)
                ? (clone $createdAt)->addMinutes(rand(5, 60))
                : null;

            $shippedAt = in_array($status, [OrderStatusEnum::Shipped, OrderStatusEnum::Delivered], true)
                ? (clone $paidAt ?? $createdAt)->addDays(rand(1, 3))
                : null;

            $orderNumber = 'LHB-'.$createdAt->format('Y').'-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'user_id' => $user?->id,
                'email' => $email,
                'coupon_id' => $appliedCoupon?->id,
                'status' => $status,
                'currency' => $currency,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'tax_amount' => 0,
                'total' => $total,
                'shipping_address_id' => $addressId,
                'shipping_full_name' => $fullName,
                'shipping_phone' => $phone,
                'shipping_address_line_1' => $line1,
                'shipping_address_line_2' => null,
                'shipping_city' => $city,
                'shipping_state' => $state,
                'shipping_country' => $country,
                'shipping_postal_code' => $postalCode,
                'tracking_number' => $shippedAt !== null ? 'TRK-'.strtoupper(Str::random(10)) : null,
                'customer_notes' => rand(1, 100) <= 20 ? 'Por favor entregar en portería' : null,
                'paid_at' => $paidAt,
                'shipped_at' => $shippedAt,
                'created_at' => $createdAt,
                'updated_at' => $shippedAt ?? $paidAt ?? $createdAt,
            ]);

            // Save Order Items
            foreach ($itemsData as $item) {
                OrderItem::query()->create(array_merge($item, [
                    'order_id' => $order->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));
            }

            // Save Coupon Redemption if coupon was applied and order is paid/delivered/shipped/refunded
            if ($appliedCoupon !== null && $paidAt !== null) {
                CouponRedemption::query()->create([
                    'coupon_id' => $appliedCoupon->id,
                    'order_id' => $order->id,
                    'user_id' => $user?->id,
                    'code' => $appliedCoupon->code,
                    'discount_amount' => $discount,
                    'currency' => $currency,
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]);
            }

            // Save Payment & Webhook Event for non-pending orders (or failed payment for cancelled orders)
            if ($paidAt !== null || $status === OrderStatusEnum::Cancelled) {
                $this->seedPaymentAndWebhook($order, $provider, $currency, $total, $status, $paidAt, $createdAt);
            }
        }
    }

    private function determineOrderStatus(int $daysAgo): OrderStatusEnum
    {
        if ($daysAgo > 30) {
            $chance = rand(1, 100);
            if ($chance <= 85) {
                return OrderStatusEnum::Delivered;
            }
            if ($chance <= 93) {
                return OrderStatusEnum::Refunded;
            }

            return OrderStatusEnum::Cancelled;
        }

        if ($daysAgo > 7) {
            $chance = rand(1, 100);
            if ($chance <= 60) {
                return OrderStatusEnum::Delivered;
            }
            if ($chance <= 85) {
                return OrderStatusEnum::Shipped;
            }

            return OrderStatusEnum::Cancelled;
        }

        if ($daysAgo > 2) {
            $chance = rand(1, 100);
            if ($chance <= 50) {
                return OrderStatusEnum::Shipped;
            }
            if ($chance <= 80) {
                return OrderStatusEnum::Processing;
            }

            return OrderStatusEnum::Pending;
        }

        // Recent orders (0-2 days)
        $chance = rand(1, 100);
        if ($chance <= 40) {
            return OrderStatusEnum::Paid;
        }
        if ($chance <= 70) {
            return OrderStatusEnum::Processing;
        }

        return OrderStatusEnum::Pending;
    }

    private function calculateDiscount(Coupon $coupon, int $subtotal): int
    {
        if ($coupon->type === CouponTypeEnum::Percentage) {
            return (int) round(($subtotal * $coupon->value) / 100);
        }

        return min($subtotal, $coupon->value);
    }

    private function seedPaymentAndWebhook(
        Order $order,
        PaymentProviderEnum $provider,
        CurrencyEnum $currency,
        int $amount,
        OrderStatusEnum $status,
        ?Carbon $paidAt,
        Carbon $createdAt
    ): void {
        $isBold = $provider === PaymentProviderEnum::Bold;
        $isRefunded = $status === OrderStatusEnum::Refunded;
        $isCancelled = $status === OrderStatusEnum::Cancelled;

        $paymentStatus = match ($status) {
            OrderStatusEnum::Refunded => PaymentStatusEnum::Refunded,
            OrderStatusEnum::Cancelled => PaymentStatusEnum::Declined,
            default => PaymentStatusEnum::Approved,
        };

        $externalId = $isBold
            ? (string) Str::uuid()
            : 'pi_3M'.Str::random(24);

        $eventId = $isBold
            ? (string) Str::uuid()
            : 'evt_'.Str::random(24);

        $paymentMethod = $isBold
            ? fake()->randomElement(['card', 'pse', 'nequi'])
            : fake()->randomElement(['card', 'klarna', 'ideal']);

        $processedAt = $paidAt ?? (clone $createdAt)->addMinutes(10);
        $refundedAt = $isRefunded ? (clone $processedAt)->addDays(rand(2, 10)) : null;

        // 1. Create Payment record
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => $provider,
            'currency' => $currency,
            'external_id' => $externalId,
            'payment_method' => $paymentMethod,
            'status' => $paymentStatus,
            'amount' => $amount,
            'raw_response' => [
                'provider' => $provider->value,
                'transaction_id' => $externalId,
                'status' => $paymentStatus->value,
                'amount' => $amount,
                'currency' => $currency->value,
            ],
            'paid_at' => $paymentStatus === PaymentStatusEnum::Declined ? null : $paidAt,
            'refunded_at' => $refundedAt,
            'created_at' => $processedAt,
            'updated_at' => $refundedAt ?? $processedAt,
        ]);

        // 2. Create PaymentWebhookEvent record simulating Bold/Stripe payload
        $eventType = match ($provider) {
            PaymentProviderEnum::Bold => match ($paymentStatus) {
                PaymentStatusEnum::Approved => 'SALE_APPROVED',
                PaymentStatusEnum::Declined => 'SALE_REJECTED',
                PaymentStatusEnum::Refunded => 'REFUND_APPROVED',
                default => 'SALE_APPROVED',
            },
            PaymentProviderEnum::Stripe => match ($paymentStatus) {
                PaymentStatusEnum::Approved => 'payment_intent.succeeded',
                PaymentStatusEnum::Declined => 'payment_intent.payment_failed',
                PaymentStatusEnum::Refunded => 'charge.refunded',
                default => 'payment_intent.succeeded',
            },
        };

        $payload = $isBold ? [
            'event' => $eventType,
            'transaction_id' => $externalId,
            'order_id' => $order->order_number,
            'amount' => [
                'total' => $amount,
                'currency' => 'COP',
            ],
            'payment_method' => strtoupper($paymentMethod),
            'timestamp' => $processedAt->toIso8601String(),
        ] : [
            'id' => $eventId,
            'object' => 'event',
            'type' => $eventType,
            'data' => [
                'object' => [
                    'id' => $externalId,
                    'amount' => $amount,
                    'currency' => 'eur',
                    'status' => $paymentStatus === PaymentStatusEnum::Approved ? 'succeeded' : 'failed',
                    'payment_method_types' => [$paymentMethod],
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'order_number' => $order->order_number,
                    ],
                ],
            ],
        ];

        PaymentWebhookEvent::query()->create([
            'provider' => $provider,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => $payload,
            'processed_at' => $processedAt,
            'created_at' => $processedAt,
            'updated_at' => $processedAt,
        ]);
    }
}
