<?php

use App\Modules\Saved\Controllers\SavedController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'auth:sanctum'])->group(function () {

    Route::get('/saved/businesses',                   [SavedController::class, 'businesses']);
    Route::post('/saved/businesses/{slug}/toggle',    [SavedController::class, 'toggleBusiness']);
    Route::get('/saved/products',                     [SavedController::class, 'products']);
    Route::post('/saved/products/{slug}/toggle',      [SavedController::class, 'toggleProduct']);

});
