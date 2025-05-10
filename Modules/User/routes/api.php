<?php

use Illuminate\Support\Facades\Route;
use Modules\User\app\Http\Controllers\UserController;
use Modules\User\app\Http\Controllers\AuthController;

// Public routes without versioning
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// User authenticated routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::patch('/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// General authenticated routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show')->where('id', '[0-9]+');
});