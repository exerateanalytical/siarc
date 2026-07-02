<?php

use App\Modules\Taxonomy\Controllers\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'json', 'api.key'])->group(function () {

    Route::get('/industries',                     [TaxonomyController::class, 'industries']);
    Route::get('/industries/{slug}',              [TaxonomyController::class, 'industry']);
    Route::get('/sectors',                        [TaxonomyController::class, 'sectors']);
    Route::get('/categories',                     [TaxonomyController::class, 'categories']);
    Route::get('/categories/{slug}',              [TaxonomyController::class, 'category']);
    Route::get('/regions',                        [TaxonomyController::class, 'regions']);
    Route::get('/certifications',                 [TaxonomyController::class, 'certifications']);
    Route::get('/attribute-templates',            [TaxonomyController::class, 'attributeTemplates']);

});
