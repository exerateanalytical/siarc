<?php

use App\Modules\Admin\Controllers\AdminApiConsumerController;
use App\Modules\Admin\Controllers\AdminBusinessController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminSettingsController;
use App\Modules\Admin\Controllers\AdminSupportController;
use App\Modules\Admin\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/admin')->middleware(['api', 'json', 'auth:sanctum', 'role:admin|super_admin|moderator'])->group(function () {

    // Dashboard
    Route::get('/dashboard',                              [AdminDashboardController::class, 'index']);

    // Businesses
    Route::get('/businesses',                             [AdminBusinessController::class, 'index']);
    Route::put('/businesses/{id}/status',                 [AdminBusinessController::class, 'updateStatus']);
    Route::post('/businesses/{id}/feature',               [AdminBusinessController::class, 'featureBusiness']);
    Route::delete('/businesses/{id}/feature',             [AdminBusinessController::class, 'unfeatureBusiness']);

    // Verification queue
    Route::get('/verifications',                          [AdminBusinessController::class, 'verificationQueue']);
    Route::post('/verifications/{id}/approve',            [AdminBusinessController::class, 'approveVerification']);
    Route::post('/verifications/{id}/reject',             [AdminBusinessController::class, 'rejectVerification']);

    // Users
    Route::get('/users',                                  [AdminUserController::class, 'index']);
    Route::put('/users/{id}/status',                      [AdminUserController::class, 'updateStatus']);
    Route::post('/users/{id}/roles',                      [AdminUserController::class, 'assignRole']);
    Route::delete('/users/{id}/roles',                    [AdminUserController::class, 'removeRole']);

    // Support tickets
    Route::get('/support/tickets',                        [AdminSupportController::class, 'index']);
    Route::post('/support/tickets/{id}/reply',            [AdminSupportController::class, 'reply']);
    Route::post('/support/tickets/{id}/close',            [AdminSupportController::class, 'close']);

    // API consumers
    Route::get('/api-consumers',                          [AdminApiConsumerController::class, 'index']);
    Route::post('/api-consumers/{id}/approve',            [AdminApiConsumerController::class, 'approve']);
    Route::post('/api-consumers/{id}/reject',             [AdminApiConsumerController::class, 'reject']);

    // Settings
    Route::get('/settings',                               [AdminSettingsController::class, 'index']);
    Route::put('/settings',                               [AdminSettingsController::class, 'update']);
    Route::get('/settings/feature-flags',                 [AdminSettingsController::class, 'featureFlags']);
    Route::post('/settings/feature-flags/{name}/toggle',  [AdminSettingsController::class, 'toggleFlag']);
    Route::get('/audit-log',                              [AdminSettingsController::class, 'auditLog']);

});
