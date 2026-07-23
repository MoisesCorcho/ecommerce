<?php

use App\Actions\Cart\AddCartItemAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Support\Cart\ResolvesCurrentCart;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    use ResolvesCurrentCart;

    #[Locked]
    public int $productVariantId;

    public int $quantity = 1;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public bool $adding = false;

    public function mount(int $productVariantId): void
    {
        $this->productVariantId = $productVariantId;
    }

    public function addToCart(): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $this->validate([
            'productVariantId' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $this->productVariantId,
                quantity: $this->quantity,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->statusMessage = __('storefront.added_to_cart');
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
};
