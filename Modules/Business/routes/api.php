<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\app\Http\Controllers\BusinessController;
use Modules\Product\app\Http\Controllers\ProductController;

// Public business routes without versioning
Route::get('/business/index', [BusinessController::class, 'index']);
Route::get('/business/{id}', [BusinessController::class, 'show'])->name('business.profile');

// Business prefix group
Route::prefix('v1/business')->group(function () {
    // Public routes
    Route::get('/index', [BusinessController::class, 'index']);
    Route::post('/register', [BusinessController::class, 'register']);
    Route::post('/login', [BusinessController::class, 'login']);
    Route::get('/profile/{id}', [BusinessController::class, 'profile'])->name('api.business.profile');
    Route::get('/{id}', [BusinessController::class, 'show'])->name('api.business.profile');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::patch('/update-profile', [BusinessController::class, 'updateProfile']);
        Route::get('/my-products', [BusinessController::class, 'myProducts'])->name('api.business.myProducts');
        Route::post('/products/new', [ProductController::class, 'store']);
        Route::patch('/products/{id}/edit', [ProductController::class, 'edit']);
        Route::post('/logout', [BusinessController::class, 'logout']);
    });
});