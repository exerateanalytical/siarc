#!/usr/bin/env bash
# ============================================================================
#  Idempotent production deploy — run ON THE SERVER, from the app root.
#  Safe to re-run for every release. Assumes code is already present (git pull
#  or an uploaded release). See docs/DEPLOYMENT.md for first-time server setup.
#
#  Usage:   ./deploy.sh
#  Requires: PHP 8.3+, Composer 2, a filled .env (copy from .env.production.example)
# ============================================================================
set -euo pipefail

echo "==> [1/8] Pre-flight checks"
[ -f .env ] || { echo "FATAL: no .env — copy .env.production.example to .env and fill it."; exit 1; }
grep -q "^APP_KEY=base64:" .env || { echo "FATAL: APP_KEY not set — run: php artisan key:generate"; exit 1; }
if grep -q "^APP_DEBUG=true" .env; then echo "FATAL: APP_DEBUG=true in production. Aborting."; exit 1; fi
if ! grep -q "^APP_ENV=production" .env; then echo "WARNING: APP_ENV is not 'production'."; fi

echo "==> [2/8] Maintenance mode ON"
php artisan down --render="errors::503" --retry=15 || true
trap 'php artisan up || true' EXIT   # always lift maintenance, even on failure

echo "==> [3/8] Composer install (production, optimized)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> [4/8] Database migrations (forced, non-interactive)"
php artisan migrate --force

echo "==> [5/8] Storage symlink (business logos / product images)"
php artisan storage:link || true   # harmless if it already exists

echo "==> [6/8] Clear stale caches"
php artisan config:clear
php artisan view:clear
php artisan event:clear || true

echo "==> [7/8] Warm production caches"
php artisan config:cache
php artisan view:cache
php artisan event:cache
php artisan route:cache
# NOTE: route:cache works here ONLY because the closure routes' helper functions
# (webUser, requireAuth, establishSiacSession, dataExportDatasets/…, developerConsumer)
# live in app/Support/route_helpers.php, autoloaded via composer's "files".
# When routes are cached, routes/web.php is NOT re-included per request, so any
# helper defined THERE and called from a cached closure would fatal with
# "Call to undefined function ...". Keep route helpers in the autoloaded file.
# (Also: a closure that captures unserializable state in `use (...)` will make
# route:cache error at build time — convert that one route to a controller.)

echo "==> [8/8] Restart queue workers (pick up new code)"
php artisan queue:restart || true

php artisan up
trap - EXIT
echo "==> Deploy complete. App is live."
echo "    Remember: a supervised 'php artisan queue:work --tries=3 --max-time=3600' must be running."
