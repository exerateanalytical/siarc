<?php

use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\LogoutController;
use App\Modules\Auth\Controllers\OtpController;
use App\Modules\Auth\Controllers\ProfileController;
use App\Modules\Auth\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', RegisterController::class)->middleware('throttle:auth');
        Route::post('/login',    LoginController::class)->middleware('throttle:auth');
        Route::post('/otp/send',    [OtpController::class, 'send'])->middleware('throttle:auth');
        Route::post('/otp/verify',  [OtpController::class, 'verify'])->middleware('throttle:auth');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout',          LogoutController::class);
            Route::get('/me',               [ProfileController::class, 'show']);
            Route::put('/me',               [ProfileController::class, 'update']);
            Route::put('/me/password',      [ProfileController::class, 'changePassword']);
        });
    });

});
