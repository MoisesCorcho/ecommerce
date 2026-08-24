<?php

declare(strict_types=1);

use App\Actions\Cart\AddCartItemAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Products\SizeEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariantPrice;
use App\Support\Cart\ResolvesCurrentCart;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] class extends Component
{
    use ResolvesCurrentCart;
    use WithPagination;

    public function render()
    {
        return $this->view();
    }

    public string $currency;

    // --- Filter state (synced with URL query params) ---

    /** @var array<int, string> Category slugs */
    #[Url]
    public array $category = [];

    /** @var array<int, string> Color names */
    #[Url]
    public array $color = [];

    /** @var array<int, string> Size names */
    #[Url]
    public array $size = [];

    #[Url]
    public ?int $minPrice = null;

    #[Url]
    public ?int $maxPrice = null;

    #[Url]
    public bool $inStock = false;

    #[Url]
    public string $sort = 'newest';

    public function mount(): void
    {
        $this->currency = CurrentCurrency::get()->value;

        if (is_string($this->category)) {
            $this->category = array_filter([$this->category]);
        }

        $requestCategory = request('category');
        if (empty($this->category) && is_string($requestCategory) && $requestCategory !== '') {
            $this->category = [$requestCategory];
        }
    }

    /**
     * Reset pagination when any filter changes.
     */
    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedColor(): void
    {
        $this->resetPage();
    }

    public function updatedSize(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedInStock(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedCurrency(): void
    {
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->resetPage();
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->resetPage();
    }

    /**
     * Toggle a color in the filter array.
     */
    public function toggleColor(string $color): void
    {
        $key = array_search($color, $this->color, true);

        if ($key === false) {
            $this->color[] = $color;
        } else {
            unset($this->color[$key]);
            $this->color = array_values($this->color);
        }

        $this->resetPage();
    }

    /**
     * Toggle a size in the filter array.
     */
    public function toggleSize(string $size): void
    {
        $key = array_search($size, $this->size, true);

        if ($key === false) {
            $this->size[] = $size;
        } else {
            unset($this->size[$key]);
            $this->size = array_values($this->size);
        }

        $this->resetPage();
    }

    /**
     * Set min and max price filter in a single atomic Livewire action.
     */
    public function setPriceFilter(?int $min, ?int $max): void
    {
        $this->minPrice = $min;
        $this->maxPrice = $max;
        $this->resetPage();
    }

    /**
     * Clear all filters and reset to defaults.
     */
    public function clearFilters(): void
    {
        $this->category = [];
        $this->color = [];
        $this->size = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->inStock = false;
        $this->sort = 'newest';
        $this->resetPage();
    }

    /**
     * Add a product variant to the cart (called from product-card hover button).
     */
    #[On('add-to-cart')]
    public function addToCart(int $variantId): void
    {
        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $variantId,
                quantity: 1,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: __('storefront.added_to_cart'));
        } catch (Throwable $e) {
            $message = match (true) {
                $e instanceof CartAccessDeniedException,
                $e instanceof CartItemNotEligibleException,
                $e instanceof CartQuantityNotAllowedException,
                $e instanceof InsufficientCartStockException => $e->getMessage(),
                default => __('storefront.add_to_cart_error'),
            };

            $this->dispatch('toast', message: $message);
        }
    }

    public function with(): array
    {
        $currency = CurrencyEnum::from($this->currency);

        // --- Base query: published products for this currency ---
        $baseQuery = Product::query()
            ->publishedForStorefront($currency)
            ->with([
                'category',
                'images',
                'variants' => fn ($q) => $q
                    ->active()
                    ->withPriceIn($currency)
                    ->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ]);

        // --- Apply filters ---
        $filteredQuery = $this->applyFilters($baseQuery, $currency);

        // --- Apply sorting ---
        $filteredQuery = $this->applySorting($filteredQuery);

        // --- Paginate ---
        $products = $filteredQuery->paginate(12)->withQueryString();

        // --- Facet data for sidebar ---
        $categories = $this->buildCategoryFacets($currency);
        $colors = $this->buildColorFacets($currency);
        $sizes = $this->buildSizeFacets($currency);
        [$globalMinPrice, $globalMaxPrice] = $this->buildPriceRange($currency);

        return [
            'products' => $products,
            'currencyEnum' => $currency,
            'categories' => $categories,
            'colors' => $colors,
            'sizes' => $sizes,
            'globalMinPrice' => $globalMinPrice,
            'globalMaxPrice' => $globalMaxPrice,
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applyFilters(Builder $query, CurrencyEnum $currency): Builder
    {
        if ($this->category !== []) {
            $query->whereHas('category', fn (Builder $q) => $q->whereIn('slug', $this->category));
        }

        if ($this->color !== []) {
            $query->whereHas(
                'variants',
                fn (Builder $q) => $q->active()->whereIn('color', $this->color)
            );
        }

        if ($this->size !== []) {
            $query->whereHas(
                'variants',
                fn (Builder $q) => $q->active()->whereIn('size', $this->size)
            );
        }

        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $query->whereHas('variants', function (Builder $vq) use ($currency): void {
                $vq->active()
                    ->whereHas('prices', function (Builder $pq) use ($currency): void {
                        $pq->where('currency', $currency->value);

                        if ($this->minPrice !== null) {
                            $pq->where('price', '>=', $this->minPrice);
                        }
                        if ($this->maxPrice !== null) {
                            $pq->where('price', '<=', $this->maxPrice);
                        }
                    });
            });
        }

        if ($this->inStock) {
            $query->whereHas('variants', fn (Builder $q) => $q->active()->where('stock', '>', 0));
        }

        return $query;
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applySorting(Builder $query): Builder
    {
        if ($this->sort === 'price_asc' || $this->sort === 'price_desc') {
            $direction = $this->sort === 'price_asc' ? 'asc' : 'desc';

            return $query->orderBy(
                ProductVariantPrice::query()
                    ->select('price')
                    ->join('product_variants', 'product_variants.id', '=', 'product_variant_prices.product_variant_id')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->where('product_variant_prices.currency', $this->currency)
                    ->limit(1),
                $direction
            );
        }

        return $query->latest();
    }

    /**
     * Category facets: all categories with product counts for this currency.
     *
     * @return array<int, array{slug: string, name: string, count: int}>
     */
    private function buildCategoryFacets(CurrencyEnum $currency): array
    {
        return Category::query()
            ->whereHas('products', fn (Builder $q) => $q->publishedForStorefront($currency))
            ->withCount(['products' => fn (Builder $q) => $q->publishedForStorefront($currency)])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $cat) => [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => $cat->products_count,
            ])
            ->all();
    }

    /**
     * Color facets: distinct colors from active variants of published products.
     *
     * @return array<int, string>
     */
    private function buildColorFacets(CurrencyEnum $currency): array
    {
        return Product::query()
            ->publishedForStorefront($currency)
            ->whereHas('variants', fn (Builder $q) => $q->active()->whereNotNull('color'))
            ->with(['variants' => fn ($q) => $q->active()->whereNotNull('color')])
            ->get()
            ->flatMap(fn (Product $p) => $p->variants->pluck('color'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Size facets: distinct sizes from active variants of published products.
     *
     * @return array<int, string>
     */
    private function buildSizeFacets(CurrencyEnum $currency): array
    {
        return Product::query()
            ->publishedForStorefront($currency)
            ->whereHas('variants', fn (Builder $q) => $q->active()->whereNotNull('size'))
            ->with(['variants' => fn ($q) => $q->active()->whereNotNull('size')])
            ->get()
            ->flatMap(fn (Product $p) => $p->variants->pluck('size'))
            ->map(fn ($size) => $size instanceof SizeEnum ? $size->value : (is_string($size) ? $size : null))
            ->filter()
            ->unique()
            ->sortBy(fn (string $size) => SizeEnum::tryFrom($size)?->sortOrder() ?? 999)
            ->values()
            ->all();
    }

    /**
     * Global price range (min, max) across all published products strictly for this currency.
     *
     * @return array{0: int|null, 1: int|null}
     */
    private function buildPriceRange(CurrencyEnum $currency): array
    {
        $stats = ProductVariantPrice::query()
            ->where('currency', $currency->value)
            ->whereHas('productVariant', fn (Builder $q) => $q->active()->whereHas(
                'product', fn (Builder $pq) => $pq->publishedForStorefront($currency)
            ))
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        if (! $stats || $stats->min_price === null || $stats->max_price === null) {
            return [null, null];
        }

        return [(int) $stats->min_price, (int) $stats->max_price];
    }
};
