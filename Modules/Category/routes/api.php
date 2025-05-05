<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\app\Http\Controllers\CategoryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class)->names('category');

    // If you need a "new" endpoint, add it like this:
    Route::get('categories/new', [CategoryController::class, 'create'])->name('category.new');
    Route::get('categories/{id}',[CategoryController::class, 'show']);
});