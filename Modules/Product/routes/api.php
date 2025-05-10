<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\App\Http\Controllers\ProductController;

Route::prefix('v1/')->group(function () {
// Add custom route for products/index
Route::get('products/index', [ProductController::class, 'index']);

// Constrain show by numeric ID so "pending" won't match here
Route::get('products/show/{id}', [ProductController::class, 'show'])
    ->where('id', '[0-9]+');

// Then define the standard resource routes,
// but only match numeric {product} placeholders
Route::apiResource('products', ProductController::class)
        ->names('products')
        ->where(['product' => '[0-9]+']);
});
