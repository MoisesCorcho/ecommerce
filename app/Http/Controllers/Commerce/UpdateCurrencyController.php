<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Cart\ChangeCartCurrencyAction;
use App\Actions\Cart\GetOrCreateCartAction;
use App\DTOs\Cart\ChangeCartCurrencyDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartCurrencyChangeBlockedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\UpdateCurrencyRequest;
use App\Support\Cart\ResolvesCurrentCart;
use App\Support\Commerce\CurrentCurrency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class UpdateCurrencyController extends Controller
{
    use ResolvesCurrentCart;

    public function __invoke(
        UpdateCurrencyRequest $request,
        GetOrCreateCartAction $getOrCreateCart,
        ChangeCartCurrencyAction $changeCartCurrency,
    ): RedirectResponse {
        $currency = CurrencyEnum::from((string) $request->validated('currency'));

        // The cart moves first. If it cannot, the storefront does not move
        // either: a storefront in one currency and a cart in another lets a
        // shopper read a price in dollars and pay it in pesos.
        try {
            $this->moveCart($currency, $getOrCreateCart, $changeCartCurrency);
        } catch (CartCurrencyChangeBlockedException $e) {
            return back()->withErrors(['currency' => $e->getMessage()]);
        }

        $request->session()->put(CurrentCurrency::SESSION_KEY, $currency->value);

        Cookie::queue(Cookie::make(
            name: (string) config('ecommerce.currency_preference.cookie_name'),
            value: $currency->value,
            minutes: (int) config('ecommerce.currency_preference.cookie_lifetime'),
            httpOnly: true,
        ));

        return back();
    }

    /**
     * @throws CartCurrencyChangeBlockedException when a line has no price in
     *                                            the requested currency
     */
    private function moveCart(
        CurrencyEnum $currency,
        GetOrCreateCartAction $getOrCreateCart,
        ChangeCartCurrencyAction $changeCartCurrency,
    ): void {
        $cart = $this->resolveCurrentCart($getOrCreateCart);

        if ($cart->currency === $currency) {
            return;
        }

        $owner = $this->cartOwner();

        // Ownership is asserted inside the action; a failure there is not a
        // domain outcome and must not be swallowed.
        $changeCartCurrency(new ChangeCartCurrencyDTO(
            cartId: $cart->id,
            currency: $currency,
            userId: $owner->userId,
            sessionId: $owner->sessionId,
        ));
    }
}
