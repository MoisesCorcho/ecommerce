<?php

declare(strict_types=1);

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Wishlist\ToggleWishlistAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Support\Cart\ResolvesCurrentCart;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] class extends Component
{
    use ResolvesCurrentCart;
    use WithPagination;

    public string $currency;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->currency = CurrentCurrency::get()->value;
    }

    public function render()
    {
        return $this->view();
    }

    public function with(): array
    {
        $currency = CurrencyEnum::from($this->currency);

        return [
            'items' => $this->loadWishlistItems($currency),
            'currencyEnum' => $currency,
        ];
    }

    public function addToCart(int $variantId): void
    {
        $this->errorMessage = null;

        $currency = CurrencyEnum::from($this->currency);
        $variant = $this->findOwnWishlistVariant($variantId, $currency);

        if ($variant === null) {
            return;
        }

        $isAvailable = Product::query()
            ->publishedForStorefront($currency)
            ->whereKey($variant->product_id)
            ->exists();

        if (! $isAvailable || $variant->isOutOfStock()) {
            return;
        }

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $variant->id,
                quantity: 1,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: __('storefront.added_to_cart'));
        } catch (Throwable $e) {
            if ($e instanceof CartAccessDeniedException
                || $e instanceof CartItemNotEligibleException
                || $e instanceof CartQuantityNotAllowedException
                || $e instanceof InsufficientCartStockException
            ) {
                $this->errorMessage = $e->getMessage();

                return;
            }

            throw $e;
        }
    }

    public function removeFromWishlist(int $variantId, ToggleWishlistAction $toggleWishlist): void
    {
        $variant = $this->findOwnWishlistVariant($variantId);

        if ($variant === null) {
            return;
        }

        $toggleWishlist(Auth::user(), $variant);

        $this->dispatch(
            'toast',
            message: __('storefront.products.removed_from_favorites'),
            undoEvent: 'restoreWishlistVariant',
            undoPayload: $variantId,
        );
    }

    /**
     * Re-adds a variant previously removed from the wishlist (toast "undo" action).
     *
     * `ToggleWishlistAction` is a symmetric toggle, so it is never called blindly here:
     * a double-invocation (double click, replayed toast) would otherwise flip an
     * already-restored row back off. Checking existence first keeps this idempotent.
     */
    public function restoreWishlistVariant(int $variantId, ToggleWishlistAction $toggleWishlist): void
    {
        $alreadySaved = Auth::user()->wishlists()
            ->where('product_variant_id', $variantId)
            ->exists();

        if ($alreadySaved) {
            return;
        }

        $variant = ProductVariant::query()->find($variantId);

        if ($variant === null) {
            return;
        }

        $toggleWishlist(Auth::user(), $variant);
    }

    /**
     * @return LengthAwarePaginator<int, array{variant: ProductVariant, isAvailable: bool, isOutOfStock: bool}>
     */
    private function loadWishlistItems(CurrencyEnum $currency): LengthAwarePaginator
    {
        return Auth::user()->wishlists()
            ->with(['productVariant' => fn ($q) => $q->with([
                'product.category',
                'images',
                'prices' => fn ($p) => $p->where('currency', $currency->value),
            ])])
            ->latest()
            ->paginate(12)
            ->through(fn (Wishlist $wishlist): array => [
                'variant' => $variant = $wishlist->productVariant,
                'isAvailable' => $variant->is_active && Product::query()
                    ->publishedForStorefront($currency)
                    ->whereKey($variant->product_id)
                    ->exists(),
                'isOutOfStock' => $variant->isOutOfStock(),
            ]);
    }

    /**
     * Scopes every mutation to the authenticated user's own wishlist rows —
     * a variant id that is not saved by the current user resolves to null,
     * never touching another account's wishlist.
     */
    private function findOwnWishlistVariant(int $variantId, ?CurrencyEnum $currency = null): ?ProductVariant
    {
        return Auth::user()->wishlists()
            ->where('product_variant_id', $variantId)
            ->with(['productVariant' => function ($q) use ($currency): void {
                if ($currency !== null) {
                    $q->with(['prices' => fn ($p) => $p->where('currency', $currency->value)]);
                }
            }])
            ->first()
            ?->productVariant;
    }
};
