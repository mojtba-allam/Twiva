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

Route::get('/admins/index', [AdminController::class, 'index']); //show all admins
Route::get('/admins/{id}', [AdminController::class, 'show']); //show all admins
Route::post('/admins/login', [AdminAuthController::class, 'login']); //login admin

Route::post('/register', [AuthController::class, 'register']); //register user
Route::post('/login', [AuthController::class, 'login']); //login user
Route::get('/users', [UserController::class, 'index']); //show all users
Route::get('/users/{id}', [UserController::class, 'show']); //show one user

Route::get('/products/index', [ProductController::class, 'index']); //show all products
Route::get('/products/{id}', [ProductController::class, 'show']); //show one product

Route::get('/categories', [CategoriesController::class, 'index']); //show all categories
Route::get('/categories/{id}', [CategoriesController::class, 'show']); //show one category with its products

Route::middleware('auth:admin')->group(function () {

    Route::put('/admins/{id}/edit', [AdminController::class, 'edit']); //edit admin
    Route::post('/admins/logout', [AdminAuthController::class, 'logout']); //logout admin

    Route::post('/categories/new', [CategoriesController::class, 'store']); //create a category
    Route::delete('/categories/{id}/delete', [CategoriesController::class, 'destroy']); //delete a category
    Route::patch('/categories/{id}/edit', [CategoriesController::class, 'edit']); //edit a category

    Route::post('/products/new', [ProductController::class, 'store']); //create a product
    Route::patch('/products/{id}/edit', [ProductController::class, 'edit']); //edit a product
});

Route::middleware('auth:user')->group(function () {

    Route::patch('/users/{id}/edit', [UserController::class, 'edit']); //edit a user

    Route::get('/orders', [OrderController::class, 'index']); //show all orders
    Route::post('/orders/{id}', [OrderController::class, 'show']); //show one order
    Route::post('/orders/new', [OrderController::class, 'store']);//store an order

    // Route::apiResource('orders', OrderController::class);
    // Route::apiResource('products', ProductController::class);

    Route::post('/logout', [AuthController::class, 'logout']);
});










