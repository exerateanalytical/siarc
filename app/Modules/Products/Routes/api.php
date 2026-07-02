<?php

use App\Modules\Products\Controllers\MyProductController;
use App\Modules\Products\Controllers\ProductImageController;
use App\Modules\Products\Controllers\ProductReportController;
use App\Modules\Products\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'api.key'])->group(function () {

    // Public
    Route::get('/products',                           [PublicProductController::class, 'index']);
    Route::get('/products/{slug}',                    [PublicProductController::class, 'show']);
    Route::get('/businesses/{slug}/products',         [PublicProductController::class, 'byBusiness']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {

        // Owner product management
        Route::prefix('my/products')->group(function () {
            Route::get('/',                           [MyProductController::class, 'index']);
            Route::post('/',                          [MyProductController::class, 'store']);
            Route::get('/{id}',                       [MyProductController::class, 'show']);
            Route::put('/{id}',                       [MyProductController::class, 'update']);
            Route::delete('/{id}',                    [MyProductController::class, 'destroy']);
            Route::post('/{id}/publish',              [MyProductController::class, 'publish']);
            Route::post('/{id}/unpublish',            [MyProductController::class, 'unpublish']);

            // Images
            Route::post('/{id}/images',               [ProductImageController::class, 'store']);
            Route::delete('/{id}/images/{imageId}',   [ProductImageController::class, 'destroy']);
            Route::put('/{id}/images/reorder',        [ProductImageController::class, 'reorder']);
        });

        // Report a product
        Route::post('/products/{slug}/report',        [ProductReportController::class, 'store']);
    });
});
