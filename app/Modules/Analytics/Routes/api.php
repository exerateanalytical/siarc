<?php

use App\Modules\Analytics\Controllers\PublicAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'api.key'])->group(function () {

    Route::get('/analytics/stats',            [PublicAnalyticsController::class, 'platformStats']);
    Route::get('/analytics/popular-searches', [PublicAnalyticsController::class, 'popularSearches']);

});
