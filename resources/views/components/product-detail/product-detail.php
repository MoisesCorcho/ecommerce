<?php

use App\Actions\Cart\AddCartItemAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Product;
use App\Support\Cart\ResolvesCurrentCart;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront'), Title('Producto')] class extends Component
{
    use ResolvesCurrentCart;

    #[Locked]
    public string $slug;

    public string $currency;

    public ?int $selectedVariantId = null;

    public int $quantity = 1;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $code = (string) config('ecommerce.default_currency', CurrencyEnum::Cop->value);
        $this->currency = CurrencyEnum::tryFrom($code)?->value ?? CurrencyEnum::Cop->value;

        $currency = CurrencyEnum::from($this->currency);
        $product = $this->findPublishedProduct($currency);

        $firstVariant = $product->variants->first();
        $this->selectedVariantId = $firstVariant?->id;
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

            $this->statusMessage = 'Agregado al carrito.';
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
        ];
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
