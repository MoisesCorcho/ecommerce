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
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Cart\ResolvesCurrentCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use ResolvesCurrentCart;

    public bool $showModal = false;

    public ?int $productId = null;

    public string $currency = 'COP';

    public ?int $selectedVariantId = null;

    public ?string $selectedColor = null;

    public ?string $selectedSize = null;

    public int $quantity = 1;

    public int $mainImageIndex = 0;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $code = (string) config('ecommerce.default_currency', CurrencyEnum::Cop->value);
        $this->currency = CurrencyEnum::tryFrom($code)?->value ?? CurrencyEnum::Cop->value;
    }

    #[On('open-quick-view')]
    public function openQuickView(int $productId): void
    {
        $this->productId = $productId;
        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->quantity = 1;
        $this->mainImageIndex = 0;
        $this->selectedColor = null;
        $this->selectedSize = null;
        $this->selectedVariantId = null;

        $currency = CurrencyEnum::from($this->currency);
        $product = Product::query()
            ->publishedForStorefront($currency)
            ->with([
                'category',
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q
                    ->active()
                    ->withPriceIn($currency)
                    ->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ])
            ->find($productId);

        if ($product === null) {
            $this->productId = null;
            $this->showModal = false;

            return;
        }

        $firstVariant = $product->variants->first();
        $this->selectedVariantId = $firstVariant?->id;
        $this->selectedColor = $firstVariant?->color;
        $this->selectedSize = $firstVariant?->size;
        $this->mainImageIndex = $this->resolveMainImageIndex($product, $firstVariant);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->productId = null;
        $this->statusMessage = null;
        $this->errorMessage = null;
    }

    public function updatedSelectedColor(?string $value): void
    {
        if ($this->productId === null) {
            return;
        }

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        if ($product === null) {
            return;
        }

        $sizesForColor = $this->collectSizesForColor($product, $currency, $this->selectedColor);

        if ($this->selectedSize && ! $sizesForColor->contains($this->selectedSize)) {
            $this->selectedSize = $sizesForColor->first();
        }

        $this->resolveAndApplyVariant($product, $currency);
        $this->quantity = 1;
    }

    public function updatedSelectedSize(?string $value): void
    {
        if ($this->productId === null) {
            return;
        }

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        if ($product === null) {
            return;
        }

        $colorsForSize = $this->collectColorsForSize($product, $currency, $this->selectedSize);

        if ($this->selectedColor && ! $colorsForSize->contains($this->selectedColor)) {
            $this->selectedColor = $colorsForSize->first();
        }

        $this->resolveAndApplyVariant($product, $currency);
        $this->quantity = 1;
    }

    public function selectVariant(int $variantId): void
    {
        if ($this->productId === null) {
            return;
        }

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        if ($product === null) {
            return;
        }

        $variant = $product->variants->firstWhere('id', $variantId);

        if ($variant !== null) {
            $this->selectedColor = $variant->color;
            $this->selectedSize = $variant->size;
            $this->selectedVariantId = $variant->id;
            $this->mainImageIndex = $this->resolveMainImageIndex($product, $variant);
            $this->quantity = 1;
        }
    }

    public function toggleFavorite(ToggleWishlistAction $toggleWishlist): void
    {
        if (Auth::guest()) {
            $this->redirect(route('login'));

            return;
        }

        if ($this->selectedVariantId === null) {
            return;
        }

        $variant = ProductVariant::query()->active()->find($this->selectedVariantId);
        if ($variant === null) {
            return;
        }

        $saved = $toggleWishlist(Auth::user(), $variant);

        $this->dispatch('toast', message: $saved
            ? __('storefront.products.added_to_favorites')
            : __('storefront.products.removed_from_favorites'));
    }

    public function buyNow(): void
    {
        $this->addToCart();

        if ($this->errorMessage === null) {
            $this->redirect(route('cart.page'));
        }
    }

    public function addToCart(): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $this->validate([
            'selectedVariantId' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: (int) $this->selectedVariantId,
                quantity: $this->quantity,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->statusMessage = __('storefront.added_to_cart');
            $this->quantity = 1;
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

    public function with(): array
    {
        if (! $this->showModal || $this->productId === null) {
            return [
                'product' => null,
                'currencyEnum' => CurrencyEnum::from($this->currency),
                'selectedVariant' => null,
                'availableColors' => collect(),
                'availableSizes' => collect(),
                'cartQuantity' => 0,
                'availableStock' => 0,
                'isFavorited' => false,
            ];
        }

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        if ($product === null) {
            return [
                'product' => null,
                'currencyEnum' => $currency,
                'selectedVariant' => null,
                'availableColors' => collect(),
                'availableSizes' => collect(),
                'cartQuantity' => 0,
                'availableStock' => 0,
                'isFavorited' => false,
            ];
        }

        $selectedVariant = $this->resolveSelectedVariant($product, $currency);
        $cartQuantity = $this->getCartQuantityForVariant();
        $availableStock = $selectedVariant ? max(0, $selectedVariant->stock - $cartQuantity) : 0;

        return [
            'product' => $product,
            'currencyEnum' => $currency,
            'selectedVariant' => $selectedVariant,
            'availableColors' => $this->collectAvailableColors($product, $currency),
            'availableSizes' => $this->collectAvailableSizes($product, $currency),
            'cartQuantity' => $cartQuantity,
            'availableStock' => $availableStock,
            'isFavorited' => $this->checkIsFavorited(),
        ];
    }

    private function getCartQuantityForVariant(): int
    {
        if ($this->selectedVariantId === null) {
            return 0;
        }

        try {
            $cart = $this->resolveCurrentCart();
        } catch (Throwable) {
            return 0;
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $this->selectedVariantId)
            ->first();

        return $cartItem?->quantity ?? 0;
    }

    /**
     * @return Collection<int, string>
     */
    private function collectAvailableColors(Product $product, CurrencyEnum $currency): Collection
    {
        return $product->variants
            ->filter(fn (ProductVariant $v): bool => $v->priceIn($currency) !== null && $v->color !== null)
            ->pluck('color')
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function collectAvailableSizes(Product $product, CurrencyEnum $currency): Collection
    {
        return $product->variants
            ->filter(fn (ProductVariant $v): bool => $v->priceIn($currency) !== null && $v->size !== null)
            ->pluck('size')
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function collectSizesForColor(Product $product, CurrencyEnum $currency, ?string $color): Collection
    {
        return $product->variants
            ->filter(fn (ProductVariant $v): bool => $v->priceIn($currency) !== null
                && $v->size !== null
                && $v->color === $color)
            ->pluck('size')
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function collectColorsForSize(Product $product, CurrencyEnum $currency, ?string $size): Collection
    {
        return $product->variants
            ->filter(fn (ProductVariant $v): bool => $v->priceIn($currency) !== null
                && $v->color !== null
                && $v->size === $size)
            ->pluck('color')
            ->unique()
            ->values();
    }

    private function resolveSelectedVariant(Product $product, CurrencyEnum $currency): ?ProductVariant
    {
        if ($this->selectedColor === null && $this->selectedSize === null) {
            return $product->variants->first();
        }

        return $product->variants->first(function (ProductVariant $v) use ($currency): bool {
            $matchesColor = $this->selectedColor === null || $v->color === $this->selectedColor;
            $matchesSize = $this->selectedSize === null || $v->size === $this->selectedSize;

            return $matchesColor && $matchesSize && $v->priceIn($currency) !== null;
        });
    }

    private function resolveAndApplyVariant(Product $product, CurrencyEnum $currency): void
    {
        $variant = $this->resolveSelectedVariant($product, $currency);

        $this->selectedVariantId = $variant?->id;
        $this->mainImageIndex = $this->resolveMainImageIndex($product, $variant);
    }

    private function resolveMainImageIndex(Product $product, ?ProductVariant $variant): int
    {
        if ($variant === null) {
            return 0;
        }

        foreach ($product->images as $index => $image) {
            if ($image->product_variant_id === $variant->id) {
                return $index;
            }
        }

        $primaryIndex = $product->images->search(fn ($img): bool => $img->is_primary);

        return $primaryIndex !== false ? $primaryIndex : 0;
    }

    private function checkIsFavorited(): bool
    {
        if (Auth::guest() || $this->selectedVariantId === null) {
            return false;
        }

        $variant = ProductVariant::query()->active()->find($this->selectedVariantId);

        return $variant !== null && $variant->wishlists()->where('user_id', Auth::id())->exists();
    }

    private function findPublishedProduct(CurrencyEnum $currency): ?Product
    {
        if ($this->productId === null) {
            return null;
        }

        return Product::query()
            ->publishedForStorefront($currency)
            ->where('id', $this->productId)
            ->with([
                'category',
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q
                    ->active()
                    ->withPriceIn($currency)
                    ->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ])
            ->first();
    }
};
