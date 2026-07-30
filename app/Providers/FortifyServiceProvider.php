<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\ResetPasswordAction;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

/**
 * Fortify is used only for its action contracts and password rules; its
 * own routes and views are disabled. Livewire full-page components call
 * these contracts directly.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Must run in register(): the package's own provider registers its
        // routes in boot(), and every provider's register() runs before any boot().
        Fortify::ignoreRoutes();
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(RegisterUserAction::class);
        Fortify::resetUserPasswordsUsing(ResetPasswordAction::class);
    }
}
