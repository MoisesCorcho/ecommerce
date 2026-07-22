<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Cart\Concerns\AssertsCartItemEligibility;
use App\DTOs\Cart\ResolveCartDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Throwable;

class MergeGuestCartIntoUserCartAction
{
    use AssertsCartItemEligibility;

    public function __construct(
        private readonly GetOrCreateCartAction $getOrCreateCart,
    ) {}

    /**
     * Merges guest cart lines into the user cart (best-effort).
     * Skips lines with stock 0 or not eligible in the user cart currency.
     * Guest cart is deleted afterwards so it is no longer canonical.
     *
     * @throws Throwable
     */
    public function __invoke(int $userId, string $guestSessionId): Cart
    {
        return DB::transaction(function () use ($userId, $guestSessionId): Cart {
            $userCart = ($this->getOrCreateCart)(new ResolveCartDTO(userId: $userId));

            /** @var Cart|null $guestCart */
            $guestCart = Cart::query()
                ->whereNull('user_id')
                ->where('session_id', $guestSessionId)
                ->with(['items.productVariant.product', 'items.productVariant.prices'])
                ->lockForUpdate()
                ->first();

            if ($guestCart === null || $guestCart->items->isEmpty()) {
                if ($guestCart !== null) {
                    $guestCart->delete();
                }

                return $userCart->fresh(['items']) ?? $userCart;
            }

            /** @var Cart $lockedUserCart */
            $lockedUserCart = Cart::query()->lockForUpdate()->findOrFail($userCart->id);

            foreach ($guestCart->items as $guestItem) {
                $this->mergeLine($lockedUserCart, $guestItem);
            }

            $guestCart->items()->delete();
            $guestCart->delete();

            return $lockedUserCart->fresh(['items']) ?? $lockedUserCart;
        });
    }

    private function mergeLine(Cart $userCart, CartItem $guestItem): void
    {
        /** @var ProductVariant|null $variant */
        $variant = $guestItem->productVariant;

        if ($variant === null) {
            return;
        }

        $variant->loadMissing(['product', 'prices']);

        try {
            $this->assertEligible($variant, $userCart->currency);
        } catch (Throwable) {
            return;
        }

        $maxAllowed = $this->maxAllowedQuantity((int) $variant->stock);
        if ($maxAllowed < 1) {
            return;
        }

        /** @var CartItem|null $existing */
        $existing = CartItem::query()
            ->where('cart_id', $userCart->id)
            ->where('product_variant_id', $variant->id)
            ->lockForUpdate()
            ->first();

        $desired = ($existing?->quantity ?? 0) + (int) $guestItem->quantity;
        $finalQty = min($desired, $maxAllowed);

        if ($finalQty < 1) {
            return;
        }

        if ($existing !== null) {
            $existing->update(['quantity' => $finalQty]);

            return;
        }

        CartItem::query()->create([
            'cart_id' => $userCart->id,
            'product_variant_id' => $variant->id,
            'quantity' => $finalQty,
        ]);
    }
}
