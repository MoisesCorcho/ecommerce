<?php

use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Orders\OrderThankYouController;
use App\Http\Controllers\Orders\StartOrderPaymentController;
use App\Http\Controllers\Payments\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::livewire('/products', 'catalog-list')->name('products.index');
Route::livewire('/products/{slug}', 'product-detail')->name('products.show');
Route::livewire('/cart', 'cart-page')->name('cart.page');
Route::livewire('/checkout', 'checkout-page')->name('checkout.show');

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
