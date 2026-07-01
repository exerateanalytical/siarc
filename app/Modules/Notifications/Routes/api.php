<?php

use App\Modules\Notifications\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'auth:sanctum'])->group(function () {

    Route::get('/notifications',                      [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read',           [NotificationController::class, 'markRead']);
    Route::get('/notifications/preferences',          [NotificationController::class, 'preferences']);
    Route::put('/notifications/preferences',          [NotificationController::class, 'updatePreferences']);

});
