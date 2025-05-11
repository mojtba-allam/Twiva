<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\App\Http\Controllers\OrderController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('orders', OrderController::class)->names('order');
    // Add route for the new order
    Route::post('orders/new', [OrderController::class, 'store']);
    Route::put('orders/{id}/edit', [OrderController::class, 'update']);
    Route::delete('orders/{id}/delete', [OrderController::class, 'destroy']);
});
