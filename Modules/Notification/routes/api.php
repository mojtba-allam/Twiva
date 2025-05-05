<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\App\Http\Controllers\NotificationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
});
