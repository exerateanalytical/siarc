<?php

use App\Modules\Messaging\Controllers\ChannelController;
use App\Modules\Messaging\Controllers\ConversationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'auth:sanctum'])->group(function () {

    Route::get('/conversations',                          [ConversationController::class, 'index']);
    Route::post('/conversations',                         [ConversationController::class, 'start']);
    Route::get('/conversations/{id}',                     [ConversationController::class, 'show']);
    Route::post('/conversations/{id}/reply',              [ConversationController::class, 'reply']);
    Route::get('/channels/conversation/{id}/auth',        [ChannelController::class, 'authenticate']);

});
