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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admins/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/admins/index', [AdminController::class, 'index']);
    Route::get('/admins/{id}', [AdminController::class, 'show']);
    Route::put('/admins/{id}', [AdminController::class, 'edit']);
    Route::post('/admins/logout', [AdminAuthController::class, 'logout']);
    // Route::post('/admins', [AdminController::class, 'store']);
    // Route::delete('/admins/{id}', [AdminController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Route::put('/users/{id}', [UserController::class, 'update']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}/edit', [UserController::class, 'edit']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/{id}', [ProductController::class, 'store']);
    Route::patch('/products/{id}/edit', [ProductController::class, 'edit']);


    Route::get('/categories', [CategoriesController::class, 'index']);
    // Route::get('/categories/{id}', [CategoriesController::class, 'show']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('admins', AdminController::class);
    Route::apiResource('categories', CategoriesController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('products', ProductController::class);

    Route::post('/categories/{id}', [CategoriesController::class, 'store']);
    Route::post('/orders/{id}', [OrderController::class, 'store']);
});










