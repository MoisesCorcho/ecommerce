<?php

namespace App\Providers;

use App\Listeners\Cart\MergeGuestCartOnLoginListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(Login::class, MergeGuestCartOnLoginListener::class);

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
