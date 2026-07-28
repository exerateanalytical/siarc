<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\VerificationApiController;
use Illuminate\Support\Facades\Route;

// Module API routes are registered by each module's RouteServiceProvider.

Route::get('/health', HealthController::class)->name('health');

/*
 * The public certificate verification API.
 *
 * Unauthenticated on purpose. Every other endpoint under /api/v1 sits behind a
 * key or a token, which is right for anything that reads or writes an account's
 * data; it is exactly wrong here. A certificate is presented to strangers — a
 * museum registrar, an insurer, a customs officer — and requiring them to hold
 * a credential from the issuer before they may check the issuer's own signature
 * turns verification into a favour we grant. It has to be open to be worth
 * anything.
 *
 * Open, and therefore throttled: the certificate namespace is sequential, and
 * an open lookup over guessable numbers is an enumeration surface. 60 a minute
 * per IP is generous for a registrar clearing a shipment and useless for a
 * crawler walking the range. The directory below it leaks nothing beyond that
 * a reference exists — no contents without the PIN, and never the wrong type
 * for a number that does not resolve.
 *
 * Cross-origin GET is allowed by config/cors.php on api/*, because the tool
 * doing the checking is often a page in the institution's own intranet.
 */
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/verify/{reference}', [VerificationApiController::class, 'verify'])
        ->name('api.verification.verify');

    Route::get('/jwks.json', [VerificationApiController::class, 'jwks'])
        ->name('api.verification.jwks');

    Route::get('/openapi.json', [VerificationApiController::class, 'openapi'])
        ->name('api.verification.openapi');
});
