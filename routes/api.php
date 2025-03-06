<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;



Route::apiResource('users', UserController::class);
Route::apiResource('admins', AdminController::class);
Route::apiResource('categories', CategoriesController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('products', ProductController::class);

Route::post('/products/{id}', [ProductController::class, 'store']);
Route::post('/categories/{id}', [CategoriesController::class, 'store']);
Route::post('/orders/{id}', [OrderController::class, 'store']);





Route::get('/users', [UserController::class, 'index']);
Route::get('/admins', [AdminController::class, 'index']);
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);

