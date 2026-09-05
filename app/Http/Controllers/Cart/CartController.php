<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ChangeCartCurrencyAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartViewAction;
use App\Actions\Cart\GetOrCreateCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Cart\CartViewDTO;
use App\DTOs\Cart\ChangeCartCurrencyDTO;
use App\DTOs\Cart\ResolveCartDTO;
use App\DTOs\Cart\UpdateCartItemQuantityDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartCurrencyChangeBlockedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartItemNotFoundException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\ChangeCartCurrencyRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Cart;
use App\Support\Cart\CartSession;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Minimal HTTP surface for cart domain (F03). Not a branded storefront.
 */
class CartController extends Controller
{
    public function show(
        Request $request,
        GetOrCreateCartAction $getOrCreateCart,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        $cart = $this->resolveCart($request, $getOrCreateCart);
        $view = $getCartView($cart->id, $this->ownerFromRequest($request));

        return response()->json($this->viewPayload($view));
    }

    public function storeItem(
        AddCartItemRequest $request,
        GetOrCreateCartAction $getOrCreateCart,
        AddCartItemAction $addCartItem,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        try {
            $cart = $this->resolveCart($request, $getOrCreateCart);
            $owner = $this->ownerFromRequest($request);

            $addCartItem(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: (int) $request->validated('product_variant_id'),
                quantity: (int) $request->validated('quantity'),
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $view = $getCartView($cart->id, $owner);

            return response()->json($this->viewPayload($view), 201);
        } catch (Throwable $e) {
            return $this->domainError($e);
        }
    }

    public function updateItem(
        UpdateCartItemRequest $request,
        int $productVariant,
        GetOrCreateCartAction $getOrCreateCart,
        UpdateCartItemQuantityAction $updateQuantity,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        try {
            $cart = $this->resolveCart($request, $getOrCreateCart);
            $owner = $this->ownerFromRequest($request);

            $updateQuantity(new UpdateCartItemQuantityDTO(
                cartId: $cart->id,
                productVariantId: $productVariant,
                quantity: (int) $request->validated('quantity'),
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $view = $getCartView($cart->id, $owner);

            return response()->json($this->viewPayload($view));
        } catch (Throwable $e) {
            return $this->domainError($e);
        }
    }

    public function destroyItem(
        Request $request,
        int $productVariant,
        GetOrCreateCartAction $getOrCreateCart,
        RemoveCartItemAction $removeCartItem,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        try {
            $cart = $this->resolveCart($request, $getOrCreateCart);
            $owner = $this->ownerFromRequest($request);

            $removeCartItem($cart->id, $productVariant, $owner);

            $view = $getCartView($cart->id, $owner);

            return response()->json($this->viewPayload($view));
        } catch (Throwable $e) {
            return $this->domainError($e);
        }
    }

    public function clear(
        Request $request,
        GetOrCreateCartAction $getOrCreateCart,
        ClearCartAction $clearCart,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        try {
            $cart = $this->resolveCart($request, $getOrCreateCart);
            $owner = $this->ownerFromRequest($request);

            $clearCart($cart->id, $owner);

            $view = $getCartView($cart->id, $owner);

            return response()->json($this->viewPayload($view));
        } catch (Throwable $e) {
            return $this->domainError($e);
        }
    }

    public function updateCurrency(
        ChangeCartCurrencyRequest $request,
        GetOrCreateCartAction $getOrCreateCart,
        ChangeCartCurrencyAction $changeCurrency,
        GetCartViewAction $getCartView,
    ): JsonResponse {
        try {
            $cart = $this->resolveCart($request, $getOrCreateCart);
            $owner = $this->ownerFromRequest($request);
            $currency = CurrencyEnum::from((string) $request->validated('currency'));

            $changeCurrency(new ChangeCartCurrencyDTO(
                cartId: $cart->id,
                currency: $currency,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
            ));

            $view = $getCartView($cart->id, $owner);

            return response()->json($this->viewPayload($view));
        } catch (Throwable $e) {
            return $this->domainError($e);
        }
    }

    private function resolveCart(Request $request, GetOrCreateCartAction $getOrCreateCart): Cart
    {
        $user = $request->user();

        if ($user !== null) {
            return $getOrCreateCart(new ResolveCartDTO(
                userId: (int) $user->getAuthIdentifier(),
                currency: CurrentCurrency::get(),
            ));
        }

        $sessionId = CartSession::ensureId();

        return $getOrCreateCart(new ResolveCartDTO(
            sessionId: $sessionId,
            currency: CurrentCurrency::get(),
        ));
    }

    private function ownerFromRequest(Request $request): CartOwnerDTO
    {
        $user = $request->user();

        if ($user !== null) {
            return new CartOwnerDTO(userId: (int) $user->getAuthIdentifier());
        }

        return new CartOwnerDTO(sessionId: CartSession::ensureId());
    }

    /**
     * @return array{
     *     cart_id: int,
     *     currency: string,
     *     total: int,
     *     lines: list<array{
     *         cart_item_id: int,
     *         product_variant_id: int,
     *         quantity: int,
     *         unit_price: int,
     *         line_subtotal: int,
     *         sku: ?string,
     *         product_name: ?string
     *     }>
     * }
     */
    private function viewPayload(CartViewDTO $view): array
    {
        return [
            'cart_id' => $view->cartId,
            'currency' => $view->currency->value,
            'total' => $view->total,
            'lines' => array_map(
                static fn ($line): array => [
                    'cart_item_id' => $line->cartItemId,
                    'product_variant_id' => $line->productVariantId,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'line_subtotal' => $line->lineSubtotal,
                    'sku' => $line->sku,
                    'product_name' => $line->productName,
                ],
                $view->lines,
            ),
        ];
    }

    private function domainError(Throwable $e): JsonResponse
    {
        $status = match (true) {
            $e instanceof CartAccessDeniedException => 403,
            $e instanceof CartItemNotFoundException => 404,
            $e instanceof CartItemNotEligibleException,
            $e instanceof InsufficientCartStockException,
            $e instanceof CartQuantityNotAllowedException,
            $e instanceof CartCurrencyChangeBlockedException => 422,
            default => throw $e,
        };

        return response()->json([
            'message' => $e->getMessage(),
        ], $status);
    }
}
