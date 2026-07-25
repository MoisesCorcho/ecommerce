<?php

use App\Actions\Cart\ChangeCartCurrencyAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartViewAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\DTOs\Cart\ChangeCartCurrencyDTO;
use App\DTOs\Cart\UpdateCartItemQuantityDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartCurrencyChangeBlockedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartItemNotFoundException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\ProductVariant;
use App\Support\Cart\ResolvesCurrentCart;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    use ResolvesCurrentCart;

    public function render()
    {
        return $this->view()->title('Leen Handbags | '.__('cart.page.title'));
    }

    public string $currency = 'COP';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    /**
     * @var array<int|string, int>
     */
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncFromCart();
    }

    public function updateLine(int $productVariantId): void
    {
        $this->clearMessages();

        $quantity = (int) ($this->quantities[$productVariantId] ?? 0);

        $this->validate([
            "quantities.{$productVariantId}" => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(UpdateCartItemQuantityAction::class)(new UpdateCartItemQuantityDTO(
                cartId: $cart->id,
                productVariantId: $productVariantId,
                quantity: $quantity,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->statusMessage = $quantity === 0
                ? __('cart.status.line_removed')
                : __('cart.status.quantity_updated');
            $this->syncFromCart();
        } catch (Throwable $e) {
            $this->handleDomainError($e);
            $this->syncFromCart();
        }
    }

    public function changeQuantity(int $productVariantId, int $quantity): void
    {
        $stock = (int) (ProductVariant::query()->find($productVariantId)?->stock ?? 0);
        $clamped = max(1, min($quantity, max(1, $stock)));

        $this->quantities[$productVariantId] = $clamped;
        $this->updateLine($productVariantId);
    }

    public function removeLine(int $productVariantId): void
    {
        $this->clearMessages();

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(RemoveCartItemAction::class)($cart->id, $productVariantId, $owner);

            $this->statusMessage = __('cart.status.line_removed');
            $this->syncFromCart();
        } catch (Throwable $e) {
            $this->handleDomainError($e);
        }
    }

    public function clearCart(): void
    {
        $this->clearMessages();

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(ClearCartAction::class)($cart->id, $owner);

            $this->statusMessage = __('cart.status.cart_cleared');
            $this->syncFromCart();
        } catch (Throwable $e) {
            $this->handleDomainError($e);
        }
    }

    public function changeCurrency(string $currency): void
    {
        $this->clearMessages();
        $this->currency = $currency;

        $this->validate([
            'currency' => ['required', 'string', 'in:COP,EUR'],
        ]);

        $target = CurrencyEnum::from($this->currency);

        try {
            $cart = $this->resolveCurrentCart();
            $owner = $this->cartOwner();

            app(ChangeCartCurrencyAction::class)(new ChangeCartCurrencyDTO(
                cartId: $cart->id,
                currency: $target,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $this->currency = $target->value;
            $this->statusMessage = __('cart.status.currency_updated');
            $this->syncFromCart();
        } catch (Throwable $e) {
            $this->handleDomainError($e);
            $this->syncFromCart();
        }
    }

    public function with(): array
    {
        $cart = $this->resolveCurrentCart();
        $owner = $this->cartOwner();
        $view = app(GetCartViewAction::class)($cart->id, $owner);

        return [
            'cartView' => $view,
        ];
    }

    private function syncFromCart(): void
    {
        $cart = $this->resolveCurrentCart();
        $owner = $this->cartOwner();
        $view = app(GetCartViewAction::class)($cart->id, $owner);

        $this->currency = $view->currency->value;
        $this->quantities = [];

        foreach ($view->lines as $line) {
            $this->quantities[$line->productVariantId] = $line->quantity;
        }
    }

    private function clearMessages(): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;
    }

    private function handleDomainError(Throwable $e): void
    {
        if ($e instanceof CartAccessDeniedException
            || $e instanceof CartCurrencyChangeBlockedException
            || $e instanceof CartItemNotEligibleException
            || $e instanceof CartItemNotFoundException
            || $e instanceof CartQuantityNotAllowedException
            || $e instanceof InsufficientCartStockException
        ) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        throw $e;
    }
};
