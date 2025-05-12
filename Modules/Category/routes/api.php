<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\app\Http\Controllers\CategoryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('categories/new', [CategoryController::class, 'create'])->name('category.new');
});
// Use prefix for the routes
Route::prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class)->names('category');
    Route::get('categories/{id}', [CategoryController::class, 'show']);
});
