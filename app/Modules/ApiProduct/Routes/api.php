<?php

use App\Modules\ApiProduct\Controllers\ApiConsumerController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'auth:sanctum'])->group(function () {

    Route::post('/developer/register',        [ApiConsumerController::class, 'register']);
    Route::get('/developer/account',          [ApiConsumerController::class, 'show']);
    Route::post('/developer/keys',            [ApiConsumerController::class, 'createKey']);
    Route::delete('/developer/keys/{id}',     [ApiConsumerController::class, 'revokeKey']);
    Route::get('/developer/usage',            [ApiConsumerController::class, 'usage']);

});
