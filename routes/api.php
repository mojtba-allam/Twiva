<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BusinessAccountController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\NotificationController;

// Public routes - no authentication required
// User authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Product & category listing
Route::get('/products/index', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/pending', [AdminProductController::class, 'pendingProducts'])->middleware('auth:sanctum');
Route::get('/products/rejected', [AdminProductController::class, 'rejectedProducts'])->middleware('auth:sanctum');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}', [CategoriesController::class, 'show'])->name('categories.show');

// Business account routes
Route::get('/business/index', [BusinessAccountController::class, 'index']);
Route::get('/business/{id}', [BusinessAccountController::class, 'show'])->name('business.profile');
Route::prefix('business')->group(function () {
    Route::post('/register', [BusinessAccountController::class, 'register']);
    Route::post('/login', [BusinessAccountController::class, 'login']);
    Route::get('/profile/{id}', [BusinessAccountController::class, 'profile'])->name('api.business.profile');

    // Authenticated business routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/new', [ProductController::class, 'store']);
        Route::patch('/products/{id}/edit', [ProductController::class, 'edit']);
    });
});

// Admin routes
Route::prefix('admins')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/index', [AdminController::class, 'index']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::put('/{id}/edit', [AdminController::class, 'edit']);
    });
});

// User authenticated routes
Route::middleware('auth:user')->group(function () {
    Route::patch('/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/orders/new', [OrderController::class, 'store']);
    Route::put('/orders/{id}/edit', [OrderController::class, 'update']);
    Route::delete('/orders/{id}/delete', [OrderController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Routes accessible by both admin and user
Route::middleware(['auth:admin,user'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
});

// General authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    // Admin-restricted routes (controller should verify admin role)
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Admin Category Management
    Route::post('/categories/new', [CategoriesController::class, 'store']);
    Route::delete('/categories/{id}/delete', [CategoriesController::class, 'destroy']);
    Route::patch('/categories/{id}/edit', [CategoriesController::class, 'edit']);

    // Admin Product Management
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approveProduct']);
    Route::post('/products/{id}/reject', [AdminProductController::class, 'rejectProduct']);
    Route::delete('/products/{id}/delete', [AdminProductController::class, 'deleteProduct']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});









