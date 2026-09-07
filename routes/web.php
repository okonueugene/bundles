<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/buy/{product:slug}', [CatalogController::class, 'buy'])->name('buy');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/payment/{orderReference}/waiting', [PaymentController::class, 'waiting'])->name('payment.waiting');
Route::get('/orders/{orderReference}', [OrderController::class, 'show'])->name('orders.show');

Route::get('/track-order', [TrackOrderController::class, 'index'])->name('track.order');
Route::post('/track-order', [TrackOrderController::class, 'lookup'])->name('track.lookup');
