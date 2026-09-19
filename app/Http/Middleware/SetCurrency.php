<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Commerce\CurrencyEnum;
use App\Support\Commerce\CountryCurrencyMap;
use App\Support\Commerce\CurrentCurrency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the visitor's market currency and pins it to the session.
 *
 * Must run after StartSession, so it is appended to the `web` group rather
 * than registered globally.
 */
class SetCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has(CurrentCurrency::SESSION_KEY)) {
            $request->session()->put(
                CurrentCurrency::SESSION_KEY,
                $this->resolve($request)->value,
            );
        }

        return $next($request);
    }

    /**
     * Cookie beats geography: an explicit past choice outranks where the
     * visitor happens to be connecting from today.
     */
    private function resolve(Request $request): CurrencyEnum
    {
        $cookieName = (string) config('ecommerce.currency_preference.cookie_name');
        $cookie = $request->cookie($cookieName);

        if (is_string($cookie)) {
            $fromCookie = CurrencyEnum::tryFrom($cookie);

            if ($fromCookie instanceof CurrencyEnum && $fromCookie->isAvailableInStorefront()) {
                return $fromCookie;
            }
        }

        $header = (string) config('ecommerce.currency_preference.country_header');
        $fromCountry = CountryCurrencyMap::resolve($request->header($header));

        return $fromCountry ?? CurrentCurrency::default();
    }
}
