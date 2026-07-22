<?php

use App\Http\Controllers\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('cart')->name('cart.')->group(function (): void {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/items', [CartController::class, 'storeItem'])->name('items.store');
    Route::patch('/items/{productVariant}', [CartController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{productVariant}', [CartController::class, 'destroyItem'])->name('items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::post('/currency', [CartController::class, 'updateCurrency'])->name('currency');
});
