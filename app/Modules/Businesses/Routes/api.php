<?php

use App\Modules\Businesses\Controllers\BusinessDocumentController;
use App\Modules\Businesses\Controllers\BusinessGalleryController;
use App\Modules\Businesses\Controllers\MyBusinessController;
use App\Modules\Businesses\Controllers\PublicBusinessController;
use App\Modules\Businesses\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json'])->group(function () {

    // Public
    Route::get('/businesses',              [PublicBusinessController::class, 'index']);
    Route::get('/businesses/featured',     [PublicBusinessController::class, 'featured']);
    Route::get('/businesses/{slug}',       [PublicBusinessController::class, 'show']);

    // Authenticated — owner manages own business
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('my/business')->group(function () {
            Route::get('/',           [MyBusinessController::class, 'show']);
            Route::post('/',          [MyBusinessController::class, 'store']);
            Route::put('/',           [MyBusinessController::class, 'update']);
            Route::post('/publish',   [MyBusinessController::class, 'publish']);
            Route::post('/logo',      [MyBusinessController::class, 'uploadLogo']);
            Route::post('/cover',     [MyBusinessController::class, 'uploadCover']);

            // Gallery
            Route::post('/gallery',             [BusinessGalleryController::class, 'store']);
            Route::delete('/gallery/{id}',      [BusinessGalleryController::class, 'destroy']);
            Route::put('/gallery/reorder',      [BusinessGalleryController::class, 'reorder']);

            // Documents
            Route::get('/documents',            [BusinessDocumentController::class, 'index']);
            Route::post('/documents',           [BusinessDocumentController::class, 'store']);
            Route::delete('/documents/{id}',    [BusinessDocumentController::class, 'destroy']);

            // Verification
            Route::post('/verification/apply',  [VerificationController::class, 'apply']);
            Route::get('/verification/status',  [VerificationController::class, 'status']);
        });
    });
});
