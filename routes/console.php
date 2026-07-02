<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────
// Scheduled maintenance
// Requires a cron entry (or Windows task) running: php artisan schedule:run
// ─────────────────────────────────────────────

// OTP codes expire after 10 minutes; keep rows 24h for attempt auditing, then purge
Schedule::call(function () {
    DB::table('otp_verifications')->where('created_at', '<', now()->subDay())->delete();
})->hourly()->name('purge-expired-otps')->onOneServer();

// Reset links are only valid for 60 minutes
Schedule::call(function () {
    DB::table('password_reset_tokens')->where('created_at', '<', now()->subHour())->delete();
})->hourly()->name('purge-expired-reset-tokens')->onOneServer();

// API usage analytics: 90-day retention
Schedule::call(function () {
    DB::table('api_usage_logs')->where('called_at', '<', now()->subDays(90))->delete();
})->daily()->name('purge-old-api-usage-logs')->onOneServer();

// View-tracking analytics: 180-day retention (aggregates live in dashboards)
Schedule::call(function () {
    DB::table('business_views')->where('viewed_at', '<', now()->subDays(180))->delete();
    DB::table('product_views')->where('viewed_at', '<', now()->subDays(180))->delete();
})->daily()->name('purge-old-view-tracking')->onOneServer();
