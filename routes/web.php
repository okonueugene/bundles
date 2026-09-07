<?php

use App\Http\Controllers\Admin\AdminBundleMappingController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/buy/{product:slug}', [CatalogController::class, 'buy'])->name('buy');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/payment/{orderReference}/waiting', [PaymentController::class, 'waiting'])->name('payment.waiting');
Route::get('/orders/{orderReference}', [OrderController::class, 'show'])
    ->name('orders.show')
    ->middleware('throttle:30,1');

Route::get('/track-order', [TrackOrderController::class, 'index'])->name('track.order');
Route::post('/track-order', [TrackOrderController::class, 'lookup'])
    ->name('track.lookup')
    ->middleware('throttle:10,1');

// TODO before deployment: wrap this group in real auth, e.g.
// Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
// Left unauthenticated deliberately for now per explicit decision —
// do not deploy this to a public server without adding auth first.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{transaction}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{transaction}/resolve', [AdminOrderController::class, 'resolve'])->name('orders.resolve');
    Route::get('/alerts', [AdminOrderController::class, 'alerts'])->name('alerts.index');
    Route::resource('bundle-mappings', AdminBundleMappingController::class)
        ->except(['show'])
        ->parameters(['bundle-mappings' => 'bundleMapping']);
});
