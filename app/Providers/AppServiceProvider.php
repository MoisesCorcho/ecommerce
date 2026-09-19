<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set here rather than in bootstrap/app.php because the configuration
        // repository is not resolved yet while the middleware stack is built.
        //
        // An allowlist, never '*': the origin keeps answering on its own
        // address, and trusting every peer would let a direct caller forge the
        // client address that the register, password-reset, contact and review
        // rate limiters key on.
        TrustProxies::at(config('ecommerce.trusted_proxies'));

        Password::defaults(function () {
            $rule = Password::min(10)->letters()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        // Start hosted checkout — abuse control; not a substitute for authz.
        RateLimiter::for('payments-start', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(20)->by('payments-start:'.$key);
        });

        // Coupon code preview/confirm — slows enumeration of campaign codes (F06 P2).
        RateLimiter::for('coupons-preview', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by('coupons-preview:'.$key);
        });
    }
}
