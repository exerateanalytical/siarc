<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// API routes are registered here by each module's RouteServiceProvider

Route::get('/health', HealthController::class)->name('health');
