<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localization;

use App\Enums\Localization\LocaleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Localization\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class UpdateLocaleController extends Controller
{
    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = LocaleEnum::from((string) $request->validated('locale'));

        $request->session()->put('locale', $locale->value);

        Cookie::queue(Cookie::make(
            name: (string) config('ecommerce.locale.cookie_name'),
            value: $locale->value,
            minutes: (int) config('ecommerce.locale.cookie_lifetime'),
            httpOnly: true,
        ));

        return back();
    }
}
