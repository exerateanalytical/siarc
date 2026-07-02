<?php

use App\Modules\CMS\Controllers\CmsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'api.key'])->group(function () {

    Route::get('/cms/pages/{slug}',    [CmsController::class, 'page']);
    Route::get('/cms/posts',           [CmsController::class, 'posts']);
    Route::get('/cms/posts/{slug}',    [CmsController::class, 'post']);
    Route::get('/cms/faqs',            [CmsController::class, 'faqs']);
    Route::get('/cms/announcements',   [CmsController::class, 'announcements']);

});
