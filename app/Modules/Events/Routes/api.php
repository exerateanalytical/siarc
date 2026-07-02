<?php

use App\Modules\Events\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'api.key'])->group(function () {

    Route::get('/events',        [PublicEventController::class, 'index']);
    Route::get('/events/{slug}', [PublicEventController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/events/{slug}/attend',   [PublicEventController::class, 'attend']);
        Route::delete('/events/{slug}/attend', [PublicEventController::class, 'cancelAttend']);
        Route::post('/events/{slug}/exhibit',  [PublicEventController::class, 'exhibit']);
    });

});
