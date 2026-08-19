<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Localization\LocaleEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's language preference to the current request.
 *
 * Must run after StartSession, so it is appended to the `web` group rather
 * than registered globally.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        App::setLocale($locale->value);

        return $next($request);
    }

    /**
     * Session wins over cookie: it carries the most recent explicit choice.
     *
     * A cookie-sourced locale is written back to the session so the next
     * request resolves in a single step.
     */
    private function resolve(Request $request): LocaleEnum
    {
        $session = LocaleEnum::tryFromValid($this->stringOrNull($request->session()->get('locale')));

        if ($session instanceof LocaleEnum) {
            return $session;
        }

        $cookieName = (string) config('ecommerce.locale.cookie_name');
        $cookie = LocaleEnum::tryFromValid($this->stringOrNull($request->cookie($cookieName)));

        if ($cookie instanceof LocaleEnum) {
            $request->session()->put('locale', $cookie->value);

            return $cookie;
        }

        // Guards a misconfigured APP_LOCALE pointing at an unsupported language.
        return LocaleEnum::tryFromValid((string) config('app.locale'))
            ?? LocaleEnum::En;
    }

    /**
     * Session and cookie payloads are untrusted and may hold arrays or objects.
     */
    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
