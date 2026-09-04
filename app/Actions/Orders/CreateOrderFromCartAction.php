<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Cart\Concerns\AssertsCartOwnership;
use App\Actions\Orders\Concerns\ValidatesCartLinesForCheckout;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Exceptions\Orders\InvalidCheckoutAddressException;
use App\Exceptions\Orders\OrderAccessDeniedException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Services\Coupons\CouponPricingService;
use App\Services\Orders\ShippingCostService;
use App\Support\Orders\OrderNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateOrderFromCartAction
{
    use AssertsCartOwnership;
    use ValidatesCartLinesForCheckout;

    public function __construct(
        private readonly ShippingCostService $shippingCostService,
        private readonly OrderNumberGenerator $orderNumberGenerator,
        private readonly CouponPricingService $couponPricingService,
    ) {}

    /**
     * Creates a pending order from the cart with live price snapshots.
     * Does not decrement variant stock (F05). Clears cart items on success.
     * Optional coupon is revalidated under lock; redemption + used_count in same TX (R4/R5).
     *
     * @throws OrderAccessDeniedException
     * @throws CheckoutCartEmptyException
     * @throws CheckoutCartNotReadyException
     * @throws InvalidCheckoutAddressException
     * @throws InvalidCouponException
     * @throws Throwable
     */
    public function __invoke(CreateOrderFromCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto): Order {
            /** @var Cart $cart */
            $cart = Cart::query()
                ->with(['items.productVariant.product', 'items.productVariant.prices'])
                ->lockForUpdate()
                ->findOrFail($dto->cartId);

            try {
                $this->assertOwnsCart($cart, $dto->userId, $dto->sessionId);
            } catch (CartAccessDeniedException) {
                throw OrderAccessDeniedException::cart();
            }

            $lines = $this->validatedCheckoutLines($cart);
            $shippingSnapshot = $this->resolveShippingSnapshot($dto);

            $subtotal = array_sum(array_column($lines, 'lineSubtotal'));
            $shippingCost = $this->shippingCostService->standardCost($cart->currency);
            $thresholdDiscount = $cart->currency->calculateThresholdDiscount($subtotal);
            $netSubtotal = max(0, $subtotal - $thresholdDiscount);
            $taxAmount = 0;

            $couponId = null;
            $discount = 0;
            $redeemCode = null;
            $lockedCoupon = null;

            if (! $this->couponPricingService->isBlank($dto->couponCode)) {
                $quote = $this->couponPricingService->quote(
                    code: (string) $dto->couponCode,
                    subtotal: $subtotal,
                    currency: $cart->currency,
                    userId: $dto->userId,
                    forUpdate: true,
                    discountableSubtotal: $netSubtotal,
                );
                $lockedCoupon = $quote->coupon;
                $couponId = (int) $lockedCoupon->id;
                $discount = $quote->discountAmount;
                $redeemCode = $quote->code;
            }

            $total = max(0, $subtotal - $thresholdDiscount - $discount) + $shippingCost + $taxAmount;

            $minChargeable = $cart->currency->minimumChargeableAmount();
            if ($total > 0 && $total < $minChargeable) {
                $discount += $total;
                $total = 0;
            }

            $isFreeOrder = $total === 0;

            $order = Order::query()->create([
                'order_number' => $this->orderNumberGenerator->generate(),
                'user_id' => $dto->userId,
                'email' => $dto->contact->email,
                'coupon_id' => $couponId,
                'status' => $isFreeOrder ? OrderStatusEnum::Paid : OrderStatusEnum::Pending,
                'currency' => $cart->currency,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'threshold_discount' => $thresholdDiscount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'shipping_address_id' => $shippingSnapshot['shipping_address_id'],
                'shipping_full_name' => $shippingSnapshot['shipping_full_name'],
                'shipping_phone' => $shippingSnapshot['shipping_phone'],
                'shipping_address_line_1' => $shippingSnapshot['shipping_address_line_1'],
                'shipping_address_line_2' => $shippingSnapshot['shipping_address_line_2'],
                'shipping_city' => $shippingSnapshot['shipping_city'],
                'shipping_state' => $shippingSnapshot['shipping_state'],
                'shipping_country' => $shippingSnapshot['shipping_country'],
                'shipping_postal_code' => $shippingSnapshot['shipping_postal_code'],
                'tracking_number' => null,
                'customer_notes' => $this->normalizeNotes($dto->customerNotes),
                'paid_at' => $isFreeOrder ? now() : null,
                'shipped_at' => null,
            ]);

            foreach ($lines as $line) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $line['variant']->id,
                    'product_name' => $line['productName'],
                    'variant_label' => $line['variantLabel'],
                    'sku' => $line['sku'],
                    'unit_price' => $line['unitPrice'],
                    'quantity' => $line['quantity'],
                ]);
            }

            if ($isFreeOrder) {
                /** @var array<int, int> $quantityByVariant */
                $quantityByVariant = [];
                foreach ($lines as $line) {
                    $variantId = (int) $line['variant']->id;
                    $quantityByVariant[$variantId] = ($quantityByVariant[$variantId] ?? 0) + (int) $line['quantity'];
                }

                foreach ($quantityByVariant as $variantId => $qty) {
                    /** @var ProductVariant $variant */
                    $variant = ProductVariant::query()
                        ->lockForUpdate()
                        ->findOrFail($variantId);

                    if ((int) $variant->stock < $qty) {
                        $productName = $variant->product?->name ?? $variant->sku ?? 'Item';
                        throw CheckoutCartNotReadyException::insufficientStock(
                            $productName,
                            max(0, (int) $variant->stock),
                        );
                    }

                    $variant->update([
                        'stock' => (int) $variant->stock - $qty,
                    ]);
                }

                Log::channel('payments')->info('orders.free_checkout.completed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'currency' => $order->currency->value,
                    'subtotal' => $order->subtotal,
                    'threshold_discount' => $order->threshold_discount,
                    'discount' => $order->discount,
                    'shipping_cost' => $order->shipping_cost,
                    'total' => $order->total,
                ]);
            }

            if ($lockedCoupon !== null && $redeemCode !== null) {
                CouponRedemption::query()->create([
                    'coupon_id' => $lockedCoupon->id,
                    'order_id' => $order->id,
                    'user_id' => $dto->userId,
                    'code' => $redeemCode,
                    'discount_amount' => $discount,
                    'currency' => $cart->currency,
                ]);

                // Coupon row was locked in quote(forUpdate: true); bump cache in same TX.
                $lockedCoupon->increment('used_count');
            }

            $cart->items()->delete();

            return $order->fresh(['items', 'coupon', 'couponRedemption']) ?? $order;
        });
    }

    /**
     * @return array{
     *     shipping_address_id: ?int,
     *     shipping_full_name: string,
     *     shipping_phone: string,
     *     shipping_address_line_1: string,
     *     shipping_address_line_2: ?string,
     *     shipping_city: string,
     *     shipping_state: string,
     *     shipping_country: string,
     *     shipping_postal_code: ?string
     * }
     *
     * @throws InvalidCheckoutAddressException
     */
    private function resolveShippingSnapshot(CreateOrderFromCartDTO $dto): array
    {
        $shipping = $dto->shipping;

        if ($shipping->addressId !== null) {
            if ($dto->userId === null) {
                throw InvalidCheckoutAddressException::make();
            }

            /** @var Address|null $address */
            $address = Address::query()
                ->whereKey($shipping->addressId)
                ->where('user_id', $dto->userId)
                ->first();

            if ($address === null) {
                throw InvalidCheckoutAddressException::make();
            }

            return [
                'shipping_address_id' => (int) $address->id,
                'shipping_full_name' => $address->full_name,
                'shipping_phone' => $address->phone,
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_country' => $address->country,
                'shipping_postal_code' => $address->postal_code,
            ];
        }

        return [
            'shipping_address_id' => null,
            'shipping_full_name' => $shipping->fullName,
            'shipping_phone' => $shipping->phone,
            'shipping_address_line_1' => $shipping->addressLine1,
            'shipping_address_line_2' => $shipping->addressLine2,
            'shipping_city' => $shipping->city,
            'shipping_state' => $shipping->state,
            'shipping_country' => strtoupper($shipping->country),
            'shipping_postal_code' => $shipping->postalCode,
        ];
    }

    private function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $trimmed = trim($notes);

        return $trimmed === '' ? null : $trimmed;
    }
}
