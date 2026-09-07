<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaWebhookController;
use App\Http\Controllers\MpesaStkCallbackController;
use App\Http\Controllers\MpesaValidationController;
use App\Http\Controllers\Api\OrderStatusController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/mpesa/confirm', [MpesaWebhookController::class, 'handleConfirm']);
Route::post('/v1/mpesa/stk-callback', [MpesaStkCallbackController::class, 'handle']);
Route::post('/v1/mpesa/validate', [MpesaValidationController::class, 'validate']);
Route::post('/v1/c2b/confirm', [MpesaWebhookController::class, 'handleConfirm']);
Route::post('/v1/c2b/validate', [MpesaValidationController::class, 'validate']);
Route::post('/v1/c2b/stk-callback', [MpesaStkCallbackController::class, 'handle']);
Route::get('/v1/orders/{reference}/status', [OrderStatusController::class, 'show'])->name('api.orders.status');
