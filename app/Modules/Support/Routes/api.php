<?php

use App\Modules\Support\Controllers\HelpController;
use App\Modules\Support\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json'])->group(function () {

    // Public help center
    Route::get('/help/categories',                   [HelpController::class, 'categories']);
    Route::get('/help/categories/{slug}/articles',   [HelpController::class, 'articles']);
    Route::get('/help/articles/{slug}',              [HelpController::class, 'article']);
    Route::get('/help/search',                       [HelpController::class, 'search']);

    // Support ticket (authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/support/categories',            [SupportTicketController::class, 'categories']);
        Route::get('/support/tickets',               [SupportTicketController::class, 'index']);
        Route::post('/support/tickets',              [SupportTicketController::class, 'store']);
        Route::get('/support/tickets/{id}',          [SupportTicketController::class, 'show']);
        Route::post('/support/tickets/{id}/reply',   [SupportTicketController::class, 'reply']);
    });

});
