<?php

declare(strict_types=1);

use App\Actions\Cart\AddCartItemAction;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront'), Title('Leen Handbags | Product')] class extends Component
{
    use ResolvesCurrentCart;

    #[Locked]
    public string $slug;

    public string $currency;

    public ?int $selectedVariantId = null;

    public int $quantity = 1;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public ?string $selectedColor = null;

    public ?string $selectedSize = null;

    public int $mainImageIndex = 0;

    public bool $showLightbox = false;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $code = (string) config('ecommerce.default_currency', CurrencyEnum::Cop->value);
        $this->currency = CurrencyEnum::tryFrom($code)?->value ?? CurrencyEnum::Cop->value;

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        $firstVariant = $product->variants->first();
        $this->selectedVariantId = $firstVariant?->id;
        $this->selectedColor = $firstVariant?->color;
        $this->selectedSize = $firstVariant?->size;
        $this->mainImageIndex = $this->resolveMainImageIndex($product, $firstVariant);
    }

    public function updatedSelectedColor(?string $value): void
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        $availableSizes = $this->collectAvailableSizes($product, $currency);

        if ($this->selectedSize && ! $availableSizes->contains($this->selectedSize)) {
            $this->selectedSize = $availableSizes->count() === 1 ? $availableSizes->first() : null;
        }

        $this->resolveAndApplyVariant($product, $currency);
        $this->quantity = 1;
    }

    public function updatedSelectedSize(?string $value): void
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        $this->resolveAndApplyVariant($product, $currency);
        $this->quantity = 1;
    }

    public function toggleFavorite(): void
    {
        if (Auth::guest()) {
            $this->redirect(route('login'));

            return;
        }

        $user = Auth::user();
        $product = $this->findPublishedProduct(CurrencyEnum::from($this->currency));

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->dispatch('toast', message: __('storefront.products.removed_from_favorites'));
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            $this->dispatch('toast', message: __('storefront.products.added_to_favorites'));
        }
    }

    public function buyNow(): void
    {
        $this->addToCart();

        if ($this->errorMessage === null) {
            $this->redirect(route('cart.page'));
        }
    }

    public function selectVariant(int $variantId): void
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        $variant = $product->variants->firstWhere('id', $variantId);

        if ($variant) {
            $this->selectedColor = $variant->color;
            $this->selectedSize = $variant->size;
            $this->selectedVariantId = $variant->id;
            $this->mainImageIndex = $this->resolveMainImageIndex($product, $variant);
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
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        return [
            'product' => $product,
            'currencyEnum' => $currency,
            'pricedVariants' => $product->variants,
            'selectedVariant' => $this->resolveSelectedVariant($product, $currency),
            'relatedProducts' => $this->fetchRelatedProducts($product, $currency),
            'isFavorited' => $this->checkIsFavorited($product),
            'availableColors' => $this->collectAvailableColors($product, $currency),
            'availableSizes' => $this->collectAvailableSizes($product, $currency),
            'cartQuantity' => $this->getCartQuantityForVariant(),
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

        $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
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

    private function resolveSelectedVariant(Product $product, CurrencyEnum $currency): ?ProductVariant
    {
        if ($this->selectedColor === null && $this->selectedSize === null) {
            return null;
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

    /**
     * @return Collection<int, Product>
     */
    private function fetchRelatedProducts(Product $product, CurrencyEnum $currency): Collection
    {
        if ($product->category_id === null) {
            return collect();
        }

        return Product::query()
            ->publishedForStorefront($currency)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q
                    ->active()
                    ->withPriceIn($currency)
                    ->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ])
            ->limit(6)
            ->get();
    }

    private function checkIsFavorited(Product $product): bool
    {
        if (Auth::guest()) {
            return false;
        }

        return Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->exists();
    }

    private function findPublishedProduct(CurrencyEnum $currency): Product
    {
        return Product::query()
            ->publishedForStorefront($currency)
            ->where('slug', $this->slug)
            ->with([
                'category',
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q
                    ->active()
                    ->withPriceIn($currency)
                    ->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ])
            ->firstOrFail();
    }
};
