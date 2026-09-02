<?php

use App\Http\Controllers\Account\ProfileOrderDetailController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Commerce\UpdateCurrencyController;
use App\Http\Controllers\Localization\UpdateLocaleController;
use App\Http\Controllers\Orders\OrderThankYouController;
use App\Http\Controllers\Orders\StartOrderPaymentController;
use App\Http\Controllers\Payments\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::livewire('/products', 'catalog-list')->name('products.index');
Route::livewire('/products/{slug}', 'product-detail')->name('products.show');
Route::livewire('/cart', 'cart-page')->name('cart.page');
Route::livewire('/checkout', 'checkout-page')->name('checkout.show');
Route::livewire('/contact', 'contact-page')->name('contact');
Route::livewire('/faq', 'faq-page')->name('faq');
Route::livewire('/about-us', 'about-page')->name('about');
Route::livewire('/blog', 'blog-list')->name('blog.index');
Route::livewire('/blog/{slug}', 'blog-show')->name('blog.show');

// Language preference. POST (not GET) because it mutates server-side state:
// a GET switcher would be prefetchable, proxy-cacheable and triggerable from
// a third-party <img src>.
Route::post('/locale', UpdateLocaleController::class)->name('locale.update');

// Storefront-wide market currency. Distinct from POST /cart/currency, which
// only re-prices the cart: this one drives the whole storefront and keeps the
// cart in step with it.
Route::post('/currency', UpdateCurrencyController::class)->name('currency.update');

// Fortify only provides action contracts (ignoreRoutes); these routes and
// their Livewire components own the full request/response cycle.
Route::middleware('guest')->group(function (): void {
    Route::livewire('/login', 'login-page')->name('login');
    Route::livewire('/register', 'register-page')->name('register');
    Route::livewire('/forgot-password', 'forgot-password-page')->name('password.request');
    Route::livewire('/reset-password/{token}', 'reset-password-page')->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    Route::livewire('/verify-email', 'verify-email-notice')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::livewire('/profile', 'profile-page')->name('profile');
    Route::livewire('/profile/addresses', 'profile-addresses-page')->name('profile.addresses');
    Route::livewire('/profile/orders', 'profile-orders-page')->name('profile.orders');
    Route::get('/profile/orders/{order}', ProfileOrderDetailController::class)->name('profile.orders.show');
    Route::livewire('/profile/reviews', 'profile-reviews-page')->name('profile.reviews');

    Route::livewire('/wishlist', 'wishlist-page')->name('wishlist');
});

Route::get('/orders/{order}/thank-you', OrderThankYouController::class)->name('orders.thank-you');
Route::post('/orders/{order}/pay', StartOrderPaymentController::class)
    ->middleware('throttle:payments-start')
    ->name('orders.pay');

Route::post('/webhooks/stripe', [PaymentWebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/bold', [PaymentWebhookController::class, 'bold'])->name('webhooks.bold');

Route::prefix('api/cart')->name('cart.')->group(function (): void {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/items', [CartController::class, 'storeItem'])->name('items.store');
    Route::patch('/items/{productVariant}', [CartController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{productVariant}', [CartController::class, 'destroyItem'])->name('items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::post('/currency', [CartController::class, 'updateCurrency'])->name('currency');
});

Route::fallback(fn () => redirect('/'));
