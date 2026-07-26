<?php

declare(strict_types=1);

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Reviews\CreateReviewAction;
use App\Actions\Reviews\DeleteReviewAction;
use App\Actions\Reviews\GetProductReviewsSummaryAction;
use App\Actions\Reviews\UpdateReviewAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewAlreadyExistsException;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Exceptions\Reviews\ReviewNotAllowedException;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Wishlist;
use App\Services\Reviews\ReviewEligibilityService;
use App\Support\Cart\ResolvesCurrentCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
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

    public ?int $reviewRating = null;

    public string $reviewComment = '';

    public ?string $reviewStatusMessage = null;

    public ?string $reviewErrorMessage = null;

    public bool $reviewFormHydrated = false;

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

        $this->hydrateViewerReviewForm($product);
    }

    public function updatedSelectedColor(?string $value): void
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        $sizesForColor = $this->collectSizesForColor($product, $currency, $this->selectedColor);

        if ($this->selectedSize && ! $sizesForColor->contains($this->selectedSize)) {
            $this->selectedSize = $sizesForColor->first();
        }

        $this->resolveAndApplyVariant($product, $currency);
        $this->quantity = 1;
    }

    public function updatedSelectedSize(?string $value): void
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        $colorsForSize = $this->collectColorsForSize($product, $currency, $this->selectedSize);

        if ($this->selectedColor && ! $colorsForSize->contains($this->selectedColor)) {
            $this->selectedColor = $colorsForSize->first();
        }

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

    public function saveReview(
        CreateReviewAction $createReview,
        UpdateReviewAction $updateReview,
        ReviewEligibilityService $eligibility,
    ): void {
        $this->reviewStatusMessage = null;
        $this->reviewErrorMessage = null;

        if (Auth::guest()) {
            $this->reviewErrorMessage = __('reviews.ui.login_required');

            return;
        }

        if (! $this->attemptReviewRateLimit()) {
            $this->reviewErrorMessage = __('reviews.errors.rate_limited');

            return;
        }

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewComment' => ['nullable', 'string', 'max:2000'],
        ], [
            'reviewRating.required' => __('reviews.validation.rating_required'),
            'reviewRating.integer' => __('reviews.validation.rating_integer'),
            'reviewRating.min' => __('reviews.validation.rating_between'),
            'reviewRating.max' => __('reviews.validation.rating_between'),
            'reviewComment.max' => __('reviews.validation.comment_max'),
        ]);

        $user = Auth::user();
        $product = $this->findPublishedProduct(CurrencyEnum::from($this->currency));
        $dto = UpsertReviewDTO::fromArray([
            'product_id' => $product->id,
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
        ]);

        try {
            $existing = Review::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing !== null) {
                $updateReview($user, $existing, $dto);
                $this->reviewStatusMessage = __('reviews.notifications.updated_pending');
            } else {
                if (! $eligibility->hasEligiblePurchase($user, $product)) {
                    $this->reviewErrorMessage = __('reviews.ui.not_eligible');

                    return;
                }

                $createReview($user, $dto);
                $this->reviewStatusMessage = __('reviews.notifications.saved_pending');
            }

            $this->dispatch('toast', message: $this->reviewStatusMessage);
        } catch (ReviewNotAllowedException|ReviewAlreadyExistsException|InvalidReviewRatingException|ReviewForbiddenException $e) {
            $this->reviewErrorMessage = $e->getMessage();
        } catch (ValidationException $e) {
            $this->reviewErrorMessage = collect($e->errors())->flatten()->first()
                ?? __('reviews.errors.invalid_rating');
        }
    }

    public function deleteReview(DeleteReviewAction $deleteReview): void
    {
        $this->reviewStatusMessage = null;
        $this->reviewErrorMessage = null;

        if (Auth::guest()) {
            $this->reviewErrorMessage = __('reviews.ui.login_required');

            return;
        }

        if (! $this->attemptReviewRateLimit()) {
            $this->reviewErrorMessage = __('reviews.errors.rate_limited');

            return;
        }

        $user = Auth::user();
        $product = $this->findPublishedProduct(CurrencyEnum::from($this->currency));
        $review = Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($review === null) {
            $this->reviewErrorMessage = __('reviews.errors.not_found');

            return;
        }

        try {
            $deleteReview($user, $review);
            $this->reviewRating = null;
            $this->reviewComment = '';
            $this->reviewFormHydrated = false;
            $this->reviewStatusMessage = __('reviews.notifications.deleted');
            $this->dispatch('toast', message: $this->reviewStatusMessage);
        } catch (ReviewForbiddenException $e) {
            $this->reviewErrorMessage = $e->getMessage();
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

    public function render()
    {
        $product = $this->findPublishedProduct(CurrencyEnum::from($this->currency));

        return $this->view()->title('Leen Handbags | '.$product->name);
    }

    public function with(): array
    {
        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);
        $eligibility = app(ReviewEligibilityService::class);
        $summary = app(GetProductReviewsSummaryAction::class)($product);

        $viewerReview = null;
        $canCreateReview = false;
        $canEditReview = false;

        if (Auth::check()) {
            $user = Auth::user();
            $viewerReview = Review::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            $canEditReview = $viewerReview !== null;
            $canCreateReview = $viewerReview === null
                && $eligibility->hasEligiblePurchase($user, $product);
        }

        $approvedReviews = Review::query()
            ->where('product_id', $product->id)
            ->approved()
            ->with(['user:id,name'])
            ->latest()
            ->limit(10)
            ->get();

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
            'approvedReviews' => $approvedReviews,
            'reviewsSummary' => $summary,
            'viewerReview' => $viewerReview,
            'canCreateReview' => $canCreateReview,
            'canEditReview' => $canEditReview,
        ];
    }

    private function attemptReviewRateLimit(): bool
    {
        $key = 'reviews-mutate:'.(Auth::id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return false;
        }

        RateLimiter::hit($key, 60);

        return true;
    }

    private function hydrateViewerReviewForm(Product $product): void
    {
        if ($this->reviewFormHydrated || Auth::guest()) {
            return;
        }

        $viewerReview = Review::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($viewerReview === null) {
            return;
        }

        $this->reviewRating = $viewerReview->rating;
        $this->reviewComment = (string) ($viewerReview->comment ?? '');
        $this->reviewFormHydrated = true;
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
