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

Route::post('/admins/login', [AdminAuthController::class, 'login']); //login admin

Route::post('/register', [AuthController::class, 'register']); //register user
Route::post('/login', [AuthController::class, 'login']); //login user

Route::get('/products/index', [ProductController::class, 'index'])->name('products.index'); //show all products

Route::get('/categories', [CategoriesController::class, 'index']); //show all categories
Route::get('/categories/{id}', [CategoriesController::class, 'show'])->name('categories.show'); //show one category with its products
Route::get('/categories/{id}/delete', [CategoriesController::class, 'destroy']); //show one category with its products

// Move pending products route outside admin middleware to allow business access
Route::get('/products/pending', [AdminProductController::class, 'pendingProducts']);

// Order routes accessible by both admin and user
Route::middleware(['auth:admin,user'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']); //show all orders
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show'); //show one order
});

// User-specific order routes
Route::middleware('auth:user')->group(function () {
    Route::patch('/users/{id}/edit', [UserController::class, 'edit']); //edit a user
    Route::post('/orders/new', [OrderController::class, 'store']); //store an order
    Route::put('/orders/{id}/edit', [OrderController::class, 'update']); //edit an order
    Route::delete('/orders/{id}/delete', [OrderController::class, 'destroy']); //delete an order
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin routes
Route::middleware('auth:admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']); //show all users
    Route::put('/admins/{id}/edit', [AdminController::class, 'edit']); //edit admin
    Route::post('/admins/logout', [AdminAuthController::class, 'logout']); //logout admin
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Update order status

    // Admin Category Management
    Route::post('/categories/new', [CategoriesController::class, 'store']); //create a category
    Route::delete('/categories/{id}/delete', [CategoriesController::class, 'destroy']); //delete a category
    Route::patch('/categories/{id}/edit', [CategoriesController::class, 'edit']); //edit a category

    // Admin Product Management
    Route::post('/products/{id}/approve', [AdminProductController::class, 'approveProduct']); // Approve a product
    Route::post('/products/{id}/reject', [AdminProductController::class, 'rejectProduct']); // Reject a product
    Route::delete('/products/{id}/delete', [AdminProductController::class, 'deleteProduct']); // Delete a product
});

// This route must come after /products/pending to avoid conflicts
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show'); //show one product

Route::middleware(['auth:admin,user'])->group(function () {
    Route::get('/admins/index', [AdminController::class, 'index']); //show all admins
    Route::get('/admins/{id}', [AdminController::class, 'show']); //show all admins
    Route::get('/users/{id}', [UserController::class, 'show']); //show one user
});

Route::prefix('business')->group(function () {
    Route::get('/index', [BusinessAccountController::class, 'index']);
    Route::post('/register', [BusinessAccountController::class, 'register']);
    Route::post('/login', [BusinessAccountController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/new', [ProductController::class, 'store']); //create a product
        Route::patch('/products/{id}/edit', [ProductController::class, 'edit']); //edit a product
        Route::get('/profile/{id}', [BusinessAccountController::class, 'profile'])->name('business.profile');
        Route::put('/profile', [BusinessAccountController::class, 'updateProfile']);
        Route::post('/logout', [BusinessAccountController::class, 'logout']);
    });
});









