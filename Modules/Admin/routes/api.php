<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\app\Http\Controllers\AdminController;
use Modules\Admin\app\Http\Controllers\AdminAuthController;
use Modules\Admin\app\Http\Controllers\AdminDashboardController;
use Modules\Admin\app\Http\Controllers\AdminProductController;

// Admin routes
Route::prefix('v1/admins')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->name('api.admins.login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('api.admins.dashboard');
        Route::get('/index', [AdminController::class, 'index']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::put('/{id}/edit', [AdminController::class, 'edit']);
    });
});

// Admin Product Management
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/products/pending', [AdminProductController::class, 'pendingProducts']);
    Route::get('/products/rejected', [AdminProductController::class, 'rejectedProducts']);
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approveProduct']);
    Route::post('/products/{id}/reject', [AdminProductController::class, 'rejectProduct']);
    Route::delete('/products/{id}/delete', [AdminProductController::class, 'deleteProduct']);
});