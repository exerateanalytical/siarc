# Galerie virtuelle de l'artisanat du Cameroun — Day 1: Foundation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bootstrap a production-ready Laravel 11 modular monolith — all 156 database tables migrated, OAuth 2.0 authentication working end-to-end, roles/permissions seeded, bilingual middleware operational, and all 12 module directory structures scaffolded.

**Architecture:** Modular monolith under `app/Modules/`. Each module owns controllers, services, models, requests, resources, jobs, and routes loaded by its own ServiceProvider. Shared middleware handles auth, locale, JSON enforcement, rate limiting. All responses use a standard JSON envelope `{ success, data, message, meta, errors }`.

**Tech Stack:** Laravel 11, MySQL 8.0, Redis 7, Laravel Passport (OAuth 2.0), Spatie Laravel Permission, Spatie Activity Log, Laravel Horizon, Laravel Scout + Meilisearch, AWS S3 Flysystem, dedoc/scramble, Sentry, pragmarx/google2fa.

---

## File Map

```
# Root
.env
.env.example
bootstrap/app.php                           (middleware registration)
bootstrap/providers.php                     (module service providers)

# Shared
app/Shared/Traits/ApiResponse.php
app/Shared/Enums/UserRole.php
app/Shared/Enums/VerificationStatus.php
app/Shared/Enums/OfferingStatus.php
app/Shared/Enums/PaymentStatus.php
app/Http/Controllers/Controller.php         (base — uses ApiResponse)
app/Http/Middleware/ForceJsonResponse.php
app/Http/Middleware/SetLocale.php
app/Http/Middleware/LogApiUsage.php

# Modules (12) — each has same sub-structure
app/Modules/{Name}/Providers/{Name}ServiceProvider.php
app/Modules/{Name}/Routes/api.php
app/Modules/{Name}/Controllers/
app/Modules/{Name}/Models/
app/Modules/{Name}/Services/
app/Modules/{Name}/Requests/
app/Modules/{Name}/Resources/
app/Modules/{Name}/Jobs/
app/Modules/{Name}/Policies/

# Migrations (12 files — one per module)
database/migrations/2026_06_24_080000_create_auth_module_tables.php
database/migrations/2026_06_24_080100_create_directory_module_tables.php
database/migrations/2026_06_24_080200_create_verification_module_tables.php
database/migrations/2026_06_24_080300_create_trading_module_tables.php
database/migrations/2026_06_24_080400_create_investors_module_tables.php
database/migrations/2026_06_24_080500_create_payments_module_tables.php
database/migrations/2026_06_24_080600_create_compliance_module_tables.php
database/migrations/2026_06_24_080700_create_notifications_module_tables.php
database/migrations/2026_06_24_080800_create_support_module_tables.php
database/migrations/2026_06_24_080900_create_cms_module_tables.php
database/migrations/2026_06_24_081000_create_api_module_tables.php
database/migrations/2026_06_24_081100_create_admin_module_tables.php

# Seeders
database/seeders/DatabaseSeeder.php
database/seeders/RolesAndPermissionsSeeder.php
database/seeders/TaxonomySeeder.php
database/seeders/VerificationTierSeeder.php
database/seeders/SystemSettingsSeeder.php

# Auth module — full implementation
app/Modules/Auth/Models/User.php
app/Modules/Auth/Models/OtpVerification.php
app/Modules/Auth/Models/TwoFactorSetting.php
app/Modules/Auth/Controllers/RegisterController.php
app/Modules/Auth/Controllers/LoginController.php
app/Modules/Auth/Controllers/LogoutController.php
app/Modules/Auth/Controllers/TokenController.php
app/Modules/Auth/Controllers/EmailVerificationController.php
app/Modules/Auth/Controllers/OtpController.php
app/Modules/Auth/Controllers/TwoFactorController.php
app/Modules/Auth/Controllers/PasswordController.php
app/Modules/Auth/Requests/RegisterRequest.php
app/Modules/Auth/Requests/LoginRequest.php
app/Modules/Auth/Requests/OtpRequest.php
app/Modules/Auth/Resources/UserResource.php
app/Modules/Auth/Resources/TokenResource.php
app/Modules/Auth/Services/AuthService.php
app/Modules/Auth/Services/OtpService.php
app/Modules/Auth/Services/TwoFactorService.php
app/Modules/Auth/Jobs/SendOtpJob.php
app/Modules/Auth/Jobs/SendVerificationEmailJob.php

# Health check
app/Http/Controllers/HealthController.php

# Config
config/passport.php
config/horizon.php

# Tests
tests/Feature/Auth/RegisterTest.php
tests/Feature/Auth/LoginTest.php
tests/Feature/Auth/OtpTest.php
tests/Feature/Auth/TwoFactorTest.php
tests/Feature/HealthTest.php

# CI
.github/workflows/ci.yml
```

---

## Task 1: Create Laravel Project

**Files:** `.env`, `composer.json`

- [ ] **Step 1.1: Create project**

```bash
cd C:\laragon\www
# If camerooncompany directory exists with only docs/, run from inside it:
cd camerooncompany
composer create-project laravel/laravel . --prefer-dist
# Accept overwrite of existing files when prompted (docs/ will be unaffected)
```

- [ ] **Step 1.2: Install all Composer packages**

```bash
composer require \
  laravel/passport:^12.0 \
  spatie/laravel-permission:^6.0 \
  spatie/laravel-activitylog:^4.0 \
  dedoc/scramble:^0.11 \
  laravel/horizon:^5.0 \
  laravel/scout:^10.0 \
  meilisearch/meilisearch-php:^1.1 \
  http-interop/http-factory-guzzle:^1.0 \
  league/flysystem-aws-s3-v3:^3.0 \
  sentry/sentry-laravel:^4.0 \
  pragmarx/google2fa:^8.0 \
  bacon/bacon-qr-code:^2.0 \
  intervention/image-laravel:^1.0
```

- [ ] **Step 1.3: Install dev packages**

```bash
composer require --dev \
  fakerphp/faker \
  laravel/pint \
  phpunit/phpunit:^11.0
```

- [ ] **Step 1.4: Publish vendor configs**

```bash
php artisan vendor:publish --provider="Laravel\Passport\PassportServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag=scramble-config
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

Expected output: multiple `PUBLISHED` lines, no errors.

- [ ] **Step 1.5: Commit**

```bash
git init
git add .
git commit -m "feat: bootstrap Laravel 11 with all packages"
```

---

## Task 2: Configure Environment

**Files:** `.env`, `.env.example`

- [ ] **Step 2.1: Write `.env`**

```ini
APP_NAME="Galerie virtuelle de l'artisanat du Cameroun"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://camerooncompany.test
APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=camerooncompany
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=log
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@cameroon-directory.cm"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=cameroon-directory-public
AWS_PRIVATE_BUCKET=cameroon-directory-private
AWS_BACKUP_BUCKET=cameroon-directory-backups
AWS_URL=

MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

SCOUT_DRIVER=meilisearch

SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=0.1

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=

MTN_MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com
MTN_MOMO_SUBSCRIPTION_KEY=
MTN_MOMO_API_USER=
MTN_MOMO_API_KEY=
MTN_MOMO_ENVIRONMENT=sandbox

ORANGE_MONEY_BASE_URL=https://api.orange.com
ORANGE_MONEY_CLIENT_ID=
ORANGE_MONEY_CLIENT_SECRET=
ORANGE_MONEY_MERCHANT_KEY=

PLATFORM_FEE_PERCENT=2.5
VAT_PERCENT=19.25
```

- [ ] **Step 2.2: Generate app key**

```bash
php artisan key:generate
```

Expected: `Application key set successfully.`

- [ ] **Step 2.3: Create MySQL database**

```sql
CREATE DATABASE camerooncompany CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run in Laragon's HeidiSQL or: `mysql -u root -e "CREATE DATABASE camerooncompany CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`

- [ ] **Step 2.4: Verify DB connection**

```bash
php artisan db:show
```

Expected: shows `camerooncompany` database details, no errors.

- [ ] **Step 2.5: Commit**

```bash
git add .env.example
git commit -m "chore: configure environment"
```

---

## Task 3: Scaffold Module Directory Structure

**Files:** 12 × module directories, `bootstrap/providers.php`

- [ ] **Step 3.1: Run scaffold script (PowerShell)**

```powershell
$modules = @('Auth','Directory','Verification','Trading','Investors','Payments','Compliance','Notifications','Support','Cms','Api','Admin')
$parts   = @('Controllers','Models','Services','Requests','Resources','Jobs','Policies','Providers','Routes')
foreach ($m in $modules) {
    foreach ($p in $parts) {
        New-Item -ItemType Directory -Force -Path "app/Modules/$m/$p" | Out-Null
    }
    # Touch route files
    New-Item -ItemType File -Force -Path "app/Modules/$m/Routes/api.php" | Out-Null
}
Write-Host "Modules scaffolded."
```

- [ ] **Step 3.2: Create ServiceProvider for each module**

Create `app/Modules/Auth/Providers/AuthServiceProvider.php`:

```php
<?php

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
```

Repeat for all 12 modules — replace `Auth` with: `Directory`, `Verification`, `Trading`, `Investors`, `Payments`, `Compliance`, `Notifications`, `Support`, `Cms`, `Api`, `Admin`.

PowerShell to generate all 12:

```powershell
$modules = @('Auth','Directory','Verification','Trading','Investors','Payments','Compliance','Notifications','Support','Cms','Api','Admin')
foreach ($m in $modules) {
    $ns = "App\Modules\$m\Providers"
    $content = @"
<?php

namespace $ns;

use Illuminate\Support\ServiceProvider;

class ${m}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
"@
    Set-Content -Path "app/Modules/$m/Providers/${m}ServiceProvider.php" -Value $content -Encoding utf8
}
Write-Host "ServiceProviders created."
```

- [ ] **Step 3.3: Register all ServiceProviders**

Write `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Directory\Providers\DirectoryServiceProvider::class,
    App\Modules\Verification\Providers\VerificationServiceProvider::class,
    App\Modules\Trading\Providers\TradingServiceProvider::class,
    App\Modules\Investors\Providers\InvestorsServiceProvider::class,
    App\Modules\Payments\Providers\PaymentsServiceProvider::class,
    App\Modules\Compliance\Providers\ComplianceServiceProvider::class,
    App\Modules\Notifications\Providers\NotificationsServiceProvider::class,
    App\Modules\Support\Providers\SupportServiceProvider::class,
    App\Modules\Cms\Providers\CmsServiceProvider::class,
    App\Modules\Api\Providers\ApiServiceProvider::class,
    App\Modules\Admin\Providers\AdminServiceProvider::class,
];
```

- [ ] **Step 3.4: Verify providers load**

```bash
php artisan route:list
```

Expected: no errors (empty route list is fine at this point).

- [ ] **Step 3.5: Commit**

```bash
git add app/Modules bootstrap/providers.php
git commit -m "feat: scaffold 12 module directories with ServiceProviders"
```

---

## Task 4: Shared Infrastructure — Base Controller & Middleware

**Files:** `app/Shared/Traits/ApiResponse.php`, `app/Http/Controllers/Controller.php`, `app/Http/Middleware/ForceJsonResponse.php`, `app/Http/Middleware/SetLocale.php`, `app/Http/Middleware/LogApiUsage.php`, `bootstrap/app.php`

- [ ] **Step 4.1: Write ApiResponse trait**

Create `app/Shared/Traits/ApiResponse.php`:

```php
<?php

namespace App\Shared\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = '', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message, 'data' => $data];
        if ($meta) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }

    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }

    protected function notFound(string $message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], 422);
    }

    protected function paginated(mixed $paginator, string $message = ''): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
```

- [ ] **Step 4.2: Update base Controller**

Write `app/Http/Controllers/Controller.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Shared\Traits\ApiResponse;

abstract class Controller
{
    use ApiResponse;
}
```

- [ ] **Step 4.3: Write ForceJsonResponse middleware**

Create `app/Http/Middleware/ForceJsonResponse.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
```

- [ ] **Step 4.4: Write SetLocale middleware**

Create `app/Http/Middleware/SetLocale.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['fr', 'en'];
        $locale    = $request->header('Accept-Language', 'fr');

        // Normalise "fr-CM" → "fr"
        $locale = strtolower(substr($locale, 0, 2));

        App::setLocale(in_array($locale, $supported, true) ? $locale : 'fr');

        $response = $next($request);
        $response->headers->set('Content-Language', App::getLocale());
        return $response;
    }
}
```

- [ ] **Step 4.5: Write LogApiUsage middleware**

Create `app/Http/Middleware/LogApiUsage.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $start    = microtime(true);
        $response = $next($request);
        $ms       = round((microtime(true) - $start) * 1000);

        Log::channel('api')->info('api_request', [
            'method'     => $request->method(),
            'path'       => $request->path(),
            'status'     => $response->getStatusCode(),
            'ms'         => $ms,
            'ip'         => $request->ip(),
            'user_id'    => $request->user()?->id,
        ]);

        return $response;
    }
}
```

- [ ] **Step 4.6: Register middleware in bootstrap/app.php**

Write `bootstrap/app.php`:

```php
<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\LogApiUsage;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            SetLocale::class,
        ]);
        $middleware->api(append: [
            LogApiUsage::class,
        ]);

        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => __('validation.failed'),
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        });
    })->create();
```

- [ ] **Step 4.7: Add `api` log channel to `config/logging.php`**

In `config/logging.php`, add `'api'` to the `channels` array:

```php
'api' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/api.log'),
    'level'  => 'info',
    'days'   => 14,
],
```

- [ ] **Step 4.8: Test the base setup**

```bash
php artisan route:list
curl http://camerooncompany.test/api/v1/up
```

Expected: `{"success":true,"status":"ok"}` or 200 from the health endpoint.

- [ ] **Step 4.9: Commit**

```bash
git add app/Shared app/Http bootstrap/app.php config/logging.php
git commit -m "feat: add shared ApiResponse trait, middleware stack, and base controller"
```

---

## Task 5: Auth Module Migration (9 custom tables)

**File:** `database/migrations/2026_06_24_080000_create_auth_module_tables.php`

Note: Spatie Permission creates `roles`, `permissions`, `model_has_*`, `role_has_permissions` via its own migration. Passport creates `oauth_*` tables. We only write what they do not cover.

- [ ] **Step 5.1: Delete default Laravel migrations**

```bash
del database\migrations\0001_01_01_000000_create_users_table.php
del database\migrations\0001_01_01_000001_create_cache_table.php
del database\migrations\0001_01_01_000002_create_jobs_table.php
```

- [ ] **Step 5.2: Write Auth module migration**

Create `database/migrations/2026_06_24_080000_create_auth_module_tables.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', ['pending','active','suspended'])->default('pending');
            $table->string('locale', 5)->default('fr');
            $table->string('avatar_url')->nullable();
            $table->string('timezone', 50)->default('Africa/Douala');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['email', 'status']);
        });

        Schema::create('two_factor_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->boolean('totp_enabled')->default(false);
            $table->text('totp_secret')->nullable();
            $table->text('totp_recovery_codes')->nullable();
            $table->boolean('sms_enabled')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');
        });

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('identifier');
            $table->enum('type', ['phone_login','email_login','phone_verify','email_verify','payment','withdrawal']);
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->tinyInteger('attempts')->default(0);
            $table->string('ip_address', 45);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['identifier', 'type', 'verified_at']);
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['identifier', 'created_at']);
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('device_type', ['ios','android','web'])->default('web');
            $table->string('device_token')->unique();
            $table->string('device_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('marketing_emails')->default(false);
            $table->string('locale', 5)->default('fr');
            $table->string('timezone', 50)->default('Africa/Douala');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');
        });

        Schema::create('social_logins', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('provider', ['google','linkedin']);
            $table->string('provider_id');
            $table->text('provider_token')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('user_id')->nullable();
            $table->string('name');
            $table->string('key_prefix', 8);
            $table->string('key_hash')->unique();
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('social_logins');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('two_factor_settings');
        Schema::dropIfExists('users');
    }
};
```

- [ ] **Step 5.3: Commit**

```bash
git add database/migrations/2026_06_24_080000_create_auth_module_tables.php
git commit -m "feat: auth module migration — users, OTP, 2FA, sessions"
```

---

## Task 6: Directory Module Migration (20 tables)

**File:** `database/migrations/2026_06_24_080100_create_directory_module_tables.php`

- [ ] **Step 6.1: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->string('name_fr'); $table->string('name_en');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en');
            $table->string('code', 10)->unique();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name_fr'); $table->string('name_en');
            $table->string('slug')->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('trade_name')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->enum('legal_form', ['sarl','sa','snc','scs','ge','association','cooperative','other'])->default('sarl');
            $table->enum('status', ['draft','pending_verification','active','suspended','dissolved'])->default('draft');
            $table->enum('verification_status', ['unverified','basic','verified','certified'])->default('unverified');
            $table->string('rccm_number', 50)->nullable()->unique();
            $table->string('niu_number', 20)->nullable()->unique();
            $table->string('anor_number', 30)->nullable();
            $table->string('cnps_number', 30)->nullable();
            $table->string('cmf_license', 30)->nullable();
            $table->date('incorporation_date')->nullable();
            $table->bigInteger('share_capital')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('employee_count_min')->nullable();
            $table->integer('employee_count_max')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedSmallInteger('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status','verification_status']);
            $table->index('is_featured');
        });

        Schema::create('company_industry', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id','industry_id']);
        });

        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id'); $table->uuid('user_id');
            $table->enum('role', ['owner','admin','member','viewer'])->default('member');
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['company_id','user_id']);
        });

        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->enum('type', ['rccm','niu','statuts','ifu','cnps','cmf_license','annual_report','other']);
            $table->string('title');
            $table->string('file_path');
            $table->string('file_hash', 64)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->enum('visibility', ['private','verified_only','public'])->default('private');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_photos', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('url');
            $table->string('caption_fr')->nullable(); $table->string('caption_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name');
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_headquarters')->default(false);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable(); $table->text('description_en')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_services', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable(); $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_certifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name'); $table->string('issued_by')->nullable();
            $table->date('issued_at')->nullable(); $table->date('expires_at')->nullable();
            $table->string('certificate_url')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_awards', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->string('awarded_by')->nullable();
            $table->year('year');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('company_social_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->enum('platform', ['linkedin','twitter','facebook','instagram','youtube','tiktok']);
            $table->string('url');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id','platform']);
        });

        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id'); $table->uuid('user_id');
            $table->tinyInteger('rating');
            $table->text('comment_fr')->nullable(); $table->text('comment_en')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['company_id','user_id']);
        });

        Schema::create('company_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id'); $table->uuid('user_id')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('viewed_at')->useCurrent();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id','viewed_at']);
        });

        Schema::create('company_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); $table->uuid('company_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['user_id','company_id']);
        });

        Schema::create('company_contact_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id'); $table->uuid('user_id')->nullable();
            $table->string('name'); $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->text('message');
            $table->enum('status', ['new','read','replied','closed'])->default('new');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $tables = [
            'company_contact_requests','company_bookmarks','company_views','company_reviews',
            'company_social_links','company_awards','company_certifications','company_services',
            'company_products','company_branches','company_photos','company_documents',
            'company_users','company_industry','companies','cities','regions','sectors','industries',
        ];
        foreach ($tables as $t) { Schema::dropIfExists($t); }
    }
};
```

- [ ] **Step 6.2: Commit**

```bash
git add database/migrations/2026_06_24_080100_create_directory_module_tables.php
git commit -m "feat: directory module migration — companies, taxonomy, reviews"
```

---

## Task 7: Verification, Trading, Investors Migrations

**Files:** migrations 080200, 080300, 080400

- [ ] **Step 7.1: Write Verification migration** — `database/migrations/2026_06_24_080200_create_verification_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verification_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // unverified, basic, verified, certified
            $table->string('slug')->unique();
            $table->text('description_fr')->nullable(); $table->text('description_en')->nullable();
            $table->json('requirements')->nullable();
            $table->tinyInteger('level')->default(0); // 0-3
            $table->timestamps();
        });

        Schema::create('verification_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('submitted_by');
            $table->foreignId('target_tier_id')->constrained('verification_tiers');
            $table->enum('status', ['draft','submitted','in_review','approved','rejected','expired'])->default('draft');
            $table->text('rejection_reason_fr')->nullable(); $table->text('rejection_reason_en')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users');
            $table->index(['company_id','status']);
        });

        Schema::create('verification_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('verification_applications')->cascadeOnDelete();
            $table->enum('registry', ['rccm','niu','anor','cnps','cmf']);
            $table->enum('status', ['pending','running','passed','failed','skipped'])->default('pending');
            $table->json('result_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('registry_lookups', function (Blueprint $table) {
            $table->id();
            $table->enum('registry', ['rccm','niu','anor','cnps','cmf']);
            $table->string('query_value');
            $table->json('response_data')->nullable();
            $table->boolean('matched')->default(false);
            $table->integer('response_code')->nullable();
            $table->integer('response_ms')->nullable();
            $table->timestamp('looked_up_at')->useCurrent();
            $table->index(['registry','query_value']);
        });

        // One denormalized record per registry for fast access
        Schema::create('rccm_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->unique();
            $table->string('rccm_number', 50);
            $table->string('company_name'); $table->string('legal_form')->nullable();
            $table->date('registration_date')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('niu_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->unique();
            $table->string('niu_number', 20);
            $table->string('taxpayer_name'); $table->string('tax_center')->nullable();
            $table->boolean('is_compliant')->default(false);
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('anor_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->unique();
            $table->string('anor_number', 30);
            $table->string('standard_name')->nullable();
            $table->date('certification_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('cnps_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->unique();
            $table->string('cnps_number', 30);
            $table->boolean('contributions_current')->default(false);
            $table->date('last_payment_date')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('cmf_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->unique();
            $table->string('cmf_license', 30);
            $table->enum('license_type', ['broker','issuer','fund_manager','custodian','other'])->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('verification_applications')->cascadeOnDelete();
            $table->enum('type', ['rccm','niu','statuts','ifu','cnps','cmf_license','id_card','other']);
            $table->string('file_path');
            $table->string('original_name');
            $table->boolean('is_accepted')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('verification_documents');
        Schema::dropIfExists('cmf_records');
        Schema::dropIfExists('cnps_records');
        Schema::dropIfExists('anor_records');
        Schema::dropIfExists('niu_records');
        Schema::dropIfExists('rccm_records');
        Schema::dropIfExists('registry_lookups');
        Schema::dropIfExists('verification_checks');
        Schema::dropIfExists('verification_applications');
        Schema::dropIfExists('verification_tiers');
    }
};
```

- [ ] **Step 7.2: Write Trading migration** — `database/migrations/2026_06_24_080300_create_trading_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('share_offerings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->text('summary_fr')->nullable(); $table->text('summary_en')->nullable();
            $table->enum('instrument_type', ['ordinary_shares','preference_shares','bonds','convertible_notes'])->default('ordinary_shares');
            $table->enum('status', ['draft','pending_cmf','cmf_approved','open','paused','closed','cancelled','completed'])->default('draft');
            $table->bigInteger('target_amount');
            $table->bigInteger('minimum_amount')->nullable();
            $table->bigInteger('maximum_amount')->nullable();
            $table->bigInteger('amount_raised')->default(0);
            $table->decimal('share_price', 15, 2);
            $table->bigInteger('total_shares');
            $table->bigInteger('shares_sold')->default(0);
            $table->decimal('equity_offered', 5, 2)->nullable();
            $table->integer('min_investment')->default(10000);
            $table->integer('max_investment')->nullable();
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->string('currency', 3)->default('XAF');
            $table->decimal('platform_fee_pct', 4, 2)->default(2.50);
            $table->uuid('cmf_reviewer_id')->nullable();
            $table->timestamp('cmf_approved_at')->nullable();
            $table->text('cmf_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['status','open_date','close_date']);
        });

        Schema::create('offering_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id');
            $table->enum('type', ['prospectus','financial_statement','business_plan','investor_presentation','term_sheet','other']);
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->string('file_path');
            $table->enum('visibility', ['public','investors_only','cmf_only'])->default('investors_only');
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
        });

        Schema::create('offering_updates', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id');
            $table->uuid('author_id');
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->text('body_fr'); $table->text('body_en')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
        });

        Schema::create('offering_faqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id');
            $table->string('question_fr'); $table->string('question_en')->nullable();
            $table->text('answer_fr'); $table->text('answer_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
        });

        Schema::create('share_classes', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name'); // Ordinary A, Preference B, etc.
            $table->text('rights')->nullable();
            $table->decimal('nominal_value', 15, 2)->default(1000);
            $table->bigInteger('total_issued')->default(0);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offering_id');
            $table->uuid('investor_id');
            $table->enum('type', ['buy','sell'])->default('buy');
            $table->enum('status', ['pending','processing','filled','partially_filled','cancelled','expired','refunded'])->default('pending');
            $table->bigInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->bigInteger('total_amount');
            $table->bigInteger('filled_quantity')->default(0);
            $table->string('payment_reference')->nullable();
            $table->enum('payment_method', ['mtn_momo','orange_money','bank_transfer'])->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
            $table->index(['offering_id','status']);
            $table->index(['investor_id','status']);
        });

        Schema::create('trades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offering_id');
            $table->uuid('buy_order_id');
            $table->uuid('sell_order_id')->nullable();
            $table->uuid('buyer_id');
            $table->uuid('seller_id')->nullable();
            $table->bigInteger('quantity');
            $table->decimal('price', 15, 2);
            $table->bigInteger('total_amount');
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->enum('settlement_status', ['pending','settled','failed'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
            $table->index(['offering_id','settlement_status']);
        });

        Schema::create('share_allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id');
            $table->uuid('investor_id');
            $table->uuid('trade_id');
            $table->bigInteger('quantity');
            $table->decimal('price_per_share', 15, 2);
            $table->bigInteger('total_cost');
            $table->enum('status', ['pending','allocated','cancelled'])->default('pending');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
            $table->index(['investor_id','status']);
        });

        Schema::create('escrow_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id')->unique();
            $table->bigInteger('balance')->default(0);
            $table->bigInteger('held_amount')->default(0);
            $table->bigInteger('released_amount')->default(0);
            $table->bigInteger('refunded_amount')->default(0);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['active','frozen','closed'])->default('active');
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
        });

        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_account_id')->constrained('escrow_accounts')->cascadeOnDelete();
            $table->uuid('order_id')->nullable();
            $table->enum('type', ['hold','release','refund','fee_deduction']);
            $table->bigInteger('amount');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('dividend_declarations', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('offering_id')->nullable();
            $table->decimal('amount_per_share', 15, 4);
            $table->date('record_date');
            $table->date('payment_date');
            $table->enum('status', ['declared','processing','paid','cancelled'])->default('declared');
            $table->bigInteger('total_payout')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('dividend_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')->constrained('dividend_declarations')->cascadeOnDelete();
            $table->uuid('investor_id');
            $table->bigInteger('shares_held');
            $table->decimal('amount_per_share', 15, 4);
            $table->bigInteger('gross_amount');
            $table->bigInteger('net_amount');
            $table->enum('status', ['pending','paid','failed'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shareholder_register', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id'); $table->uuid('investor_id');
            $table->uuid('share_class_id')->nullable();
            $table->bigInteger('shares_held')->default(0);
            $table->decimal('avg_cost_per_share', 15, 2)->default(0);
            $table->bigInteger('total_invested')->default(0);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id','investor_id']);
        });

        Schema::create('cmf_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('offering_id'); $table->uuid('reviewer_id');
            $table->enum('decision', ['approved','rejected','more_info_required']);
            $table->text('notes_fr')->nullable(); $table->text('notes_en')->nullable();
            $table->json('required_docs')->nullable();
            $table->timestamp('decided_at')->useCurrent();
            $table->timestamps();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
        });
    }

    public function down(): void {
        $tables = [
            'cmf_approvals','shareholder_register','dividend_payments','dividend_declarations',
            'escrow_transactions','escrow_accounts','share_allocations','trades','orders',
            'share_classes','offering_faqs','offering_updates','offering_documents','share_offerings',
        ];
        foreach ($tables as $t) { Schema::dropIfExists($t); }
    }
};
```

- [ ] **Step 7.3: Write Investors migration** — `database/migrations/2026_06_24_080400_create_investors_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('investor_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->unique();
            $table->enum('investor_type', ['individual','institutional'])->default('individual');
            $table->enum('accreditation_level', ['retail','qualified','institutional'])->default('retail');
            $table->string('national_id', 30)->nullable();
            $table->string('id_type')->nullable(); // CNI, passport
            $table->date('dob')->nullable();
            $table->string('nationality', 3)->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->bigInteger('annual_income')->nullable();
            $table->bigInteger('net_worth')->nullable();
            $table->enum('risk_tolerance', ['conservative','moderate','aggressive'])->default('moderate');
            $table->boolean('is_pep')->default(false);
            $table->boolean('is_sanctioned')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_rib')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('kyc_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('tier', ['basic','standard','enhanced'])->default('basic');
            $table->enum('status', ['draft','submitted','in_review','approved','rejected','expired'])->default('draft');
            $table->uuid('reviewed_by')->nullable();
            $table->text('rejection_reason_fr')->nullable(); $table->text('rejection_reason_en')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_application_id')->constrained('kyc_applications')->cascadeOnDelete();
            $table->enum('type', ['national_id_front','national_id_back','passport','selfie','proof_of_address','bank_statement','other']);
            $table->string('file_path');
            $table->string('original_name');
            $table->boolean('is_accepted')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('investment_pledges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('investor_id');
            $table->uuid('offering_id');
            $table->bigInteger('amount');
            $table->bigInteger('shares_requested')->nullable();
            $table->enum('status', ['pending','payment_initiated','payment_received','order_created','cancelled','expired'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_initiated_at')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('investor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('offering_id')->references('id')->on('share_offerings')->cascadeOnDelete();
            $table->index(['investor_id','status']);
        });

        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->uuid('investor_id')->unique();
            $table->bigInteger('total_invested')->default(0);
            $table->bigInteger('current_value')->default(0);
            $table->bigInteger('total_dividends_received')->default(0);
            $table->integer('companies_count')->default(0);
            $table->timestamps();
            $table->foreign('investor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('portfolio_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('investor_id');
            $table->uuid('offering_id')->nullable();
            $table->enum('type', ['investment','dividend','fee','refund','withdrawal']);
            $table->bigInteger('amount');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('transacted_at')->useCurrent();
            $table->foreign('investor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['investor_id','type','transacted_at']);
        });

        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('investor_id');
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->foreign('investor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->constrained('watchlists')->cascadeOnDelete();
            $table->uuid('company_id'); $table->uuid('offering_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['watchlist_id','company_id']);
        });

        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('investor_id')->unique();
            $table->json('answers');
            $table->enum('result', ['conservative','moderate','aggressive']);
            $table->integer('score');
            $table->timestamp('assessed_at')->useCurrent();
            $table->timestamps();
            $table->foreign('investor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void {
        $tables = [
            'risk_assessments','watchlist_items','watchlists','portfolio_transactions',
            'portfolios','investment_pledges','kyc_documents','kyc_applications','investor_profiles',
        ];
        foreach ($tables as $t) { Schema::dropIfExists($t); }
    }
};
```

- [ ] **Step 7.4: Commit**

```bash
git add database/migrations/2026_06_24_0802*.php database/migrations/2026_06_24_0803*.php database/migrations/2026_06_24_0804*.php
git commit -m "feat: verification, trading, investors module migrations"
```

---

## Task 8: Payments, Compliance, Notifications, Support, CMS, API, Admin Migrations

- [ ] **Step 8.1: Write Payments migration** â€” `database/migrations/2026_06_24_080500_create_payments_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('type', ['main','escrow','fees'])->default('main');
            $table->bigInteger('balance')->default(0);
            $table->bigInteger('pending_balance')->default(0);
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id','type']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit','debit','hold','release']);
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->string('reference')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['wallet_id','created_at']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->enum('provider', ['mtn_momo','orange_money','bank_transfer','internal']);
            $table->enum('type', ['payment','refund','payout','fee']);
            $table->enum('status', ['pending','processing','completed','failed','cancelled','reversed'])->default('pending');
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('XAF');
            $table->string('provider_reference')->nullable();
            $table->string('platform_reference')->unique();
            $table->string('phone_number', 20)->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id','status','created_at']);
        });

        Schema::create('payment_provider_callbacks', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['mtn_momo','orange_money']);
            $table->string('external_id');
            $table->json('payload');
            $table->string('signature_header')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false);
            $table->text('processing_error')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->unique(['provider','external_id']);
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id'); $table->uuid('requested_by');
            $table->uuid('offering_id')->nullable();
            $table->bigInteger('gross_amount');
            $table->bigInteger('platform_fee');
            $table->bigInteger('vat_amount');
            $table->bigInteger('net_amount');
            $table->enum('status', ['pending','approved','processing','completed','failed','rejected'])->default('pending');
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('payment_reference')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('invoice_number')->unique();
            $table->enum('type', ['platform_fee','subscription','custom']);
            $table->enum('status', ['draft','sent','paid','overdue','cancelled'])->default('draft');
            $table->bigInteger('subtotal');
            $table->bigInteger('vat_amount');
            $table->bigInteger('total');
            $table->string('currency', 3)->default('XAF');
            $table->date('issue_date'); $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description_fr'); $table->string('description_en')->nullable();
            $table->integer('quantity')->default(1);
            $table->bigInteger('unit_price');
            $table->bigInteger('total_price');
            $table->decimal('vat_rate', 4, 2)->default(19.25);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('payment_transaction_id');
            $table->uuid('requested_by');
            $table->bigInteger('amount');
            $table->enum('status', ['pending','approved','processing','completed','rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('payment_transaction_id')->references('id')->on('payment_transactions');
        });
    }

    public function down(): void {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('payment_provider_callbacks');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
```

- [ ] **Step 8.2: Write Compliance migration** â€” `database/migrations/2026_06_24_080600_create_compliance_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aml_screenings', function (Blueprint $table) {
            $table->id();
            $table->uuid('subject_id'); $table->string('subject_type');
            $table->enum('type', ['pep','sanctions','adverse_media']);
            $table->enum('result', ['clear','hit','potential_hit','error'])->default('clear');
            $table->json('match_data')->nullable();
            $table->decimal('match_score', 5, 2)->nullable();
            $table->boolean('is_false_positive')->default(false);
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('screened_at')->useCurrent();
            $table->timestamps();
            $table->index(['subject_id','subject_type','type']);
        });

        Schema::create('suspicious_activity_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('reported_by'); $table->uuid('subject_id'); $table->string('subject_type');
            $table->string('sar_number')->nullable()->unique();
            $table->text('description_fr'); $table->text('description_en')->nullable();
            $table->json('evidence')->nullable();
            $table->enum('status', ['draft','submitted','under_review','filed_with_anif','closed'])->default('draft');
            $table->string('anif_reference')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('compliance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->string('category');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('compliance_rules');
            $table->uuid('subject_id'); $table->string('subject_type');
            $table->enum('result', ['passed','failed','warning','skipped'])->default('skipped');
            $table->json('details')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->index(['subject_id','subject_type','rule_id']);
        });

        Schema::create('data_retention_records', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); $table->string('model_id');
            $table->enum('action', ['archived','anonymized','deleted']);
            $table->json('metadata')->nullable();
            $table->timestamp('action_at')->useCurrent();
            $table->index(['model_type','action_at']);
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); $table->string('event');
            $table->string('auditable_type')->nullable(); $table->uuid('auditable_id')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable(); $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['auditable_type','auditable_id']);
            $table->index(['user_id','event','created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('data_retention_records');
        Schema::dropIfExists('compliance_checks');
        Schema::dropIfExists('compliance_rules');
        Schema::dropIfExists('suspicious_activity_reports');
        Schema::dropIfExists('aml_screenings');
    }
};
```

- [ ] **Step 8.3: Write Notifications migration** â€” `database/migrations/2026_06_24_080700_create_notifications_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); $table->string('name');
            $table->enum('channel', ['email','sms','push','in_app']);
            $table->string('subject_fr')->nullable(); $table->string('subject_en')->nullable();
            $table->text('body_fr'); $table->text('body_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type');
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->text('body_fr'); $table->text('body_en')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id','read_at','created_at']);
        });

        Schema::create('push_registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('token')->unique();
            $table->enum('platform', ['ios','android','web'])->default('web');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); $table->string('to_email');
            $table->string('template_code')->nullable(); $table->string('subject');
            $table->enum('status', ['queued','sent','failed','bounced'])->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['to_email','status','created_at']);
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); $table->string('to_phone', 20);
            $table->string('template_code')->nullable();
            $table->text('body');
            $table->enum('status', ['queued','sent','failed','delivered'])->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('push_registrations');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_templates');
    }
};
```

- [ ] **Step 8.4: Write Support migration** â€” `database/migrations/2026_06_24_080800_create_support_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->string('slug')->unique(); $table->string('icon', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreignId('category_id')->nullable()->constrained('support_categories')->nullOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->enum('status', ['open','in_progress','waiting_user','resolved','closed'])->default('open');
            $table->enum('priority', ['low','normal','high','urgent'])->default('normal');
            $table->uuid('assigned_to')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['status','priority','created_at']);
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_id'); $table->uuid('author_id');
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_from_staff')->default(false);
            $table->timestamps();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
        });

        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('ticket_messages')->cascadeOnDelete();
            $table->string('file_path'); $table->string('original_name');
            $table->string('mime_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->string('slug')->unique(); $table->string('icon', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('knowledge_categories')->cascadeOnDelete();
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->longText('body_fr'); $table->longText('body_en')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedSmallInteger('helpful_yes')->default(0);
            $table->unsignedSmallInteger('helpful_no')->default(0);
            $table->timestamps();
        });

        Schema::create('support_ratings', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_id')->unique(); $table->uuid('user_id');
            $table->tinyInteger('score');
            $table->text('comment')->nullable();
            $table->timestamp('rated_at')->useCurrent();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('support_ratings');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('knowledge_categories');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('support_categories');
    }
};
```

- [ ] **Step 8.5: Write CMS migration** â€” `database/migrations/2026_06_24_080900_create_cms_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->longText('body_fr'); $table->longText('body_en')->nullable();
            $table->string('meta_title_fr')->nullable(); $table->string('meta_title_en')->nullable();
            $table->text('meta_description_fr')->nullable(); $table->text('meta_description_en')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->uuid('author_id');
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->text('excerpt_fr')->nullable(); $table->text('excerpt_en')->nullable();
            $table->longText('body_fr'); $table->longText('body_en')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps(); $table->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title_fr'); $table->string('title_en')->nullable();
            $table->text('body_fr'); $table->text('body_en')->nullable();
            $table->enum('type', ['info','warning','success','critical'])->default('info');
            $table->enum('audience', ['all','investors','companies','admin'])->default('all');
            $table->boolean('is_published')->default(false);
            $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string','integer','boolean','json'])->default('string');
            $table->string('group')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uploaded_by')->nullable();
            $table->string('original_name'); $table->string('file_path');
            $table->string('disk')->default('s3-public');
            $table->string('mime_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('width')->nullable(); $table->integer('height')->nullable();
            $table->string('alt_text_fr')->nullable(); $table->string('alt_text_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('pages');
    }
};
```

- [ ] **Step 8.6: Write API module migration** â€” `database/migrations/2026_06_24_081000_create_api_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('api_consumers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); $table->string('slug')->unique();
            $table->string('contact_email');
            $table->enum('status', ['pending','active','suspended'])->default('pending');
            $table->text('description')->nullable(); $table->string('website')->nullable();
            $table->timestamps(); $table->softDeletes();
        });

        Schema::create('api_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); $table->string('slug')->unique();
            $table->integer('requests_per_minute')->default(60);
            $table->integer('requests_per_day')->default(10000);
            $table->integer('requests_per_month')->nullable();
            $table->bigInteger('price_monthly')->default(0);
            $table->json('allowed_scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('api_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained('api_consumers')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('api_plans');
            $table->enum('status', ['active','cancelled','expired'])->default('active');
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->nullable()->constrained('api_consumers')->nullOnDelete();
            $table->uuid('user_id')->nullable();
            $table->string('method', 10); $table->string('path');
            $table->string('api_version', 5)->default('v1');
            $table->integer('response_status'); $table->integer('response_ms');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->index(['consumer_id','requested_at']);
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->uuid('owner_id'); $table->string('owner_type');
            $table->string('url'); $table->json('events');
            $table->string('secret_hash');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event'); $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->integer('response_ms')->nullable();
            $table->enum('status', ['pending','delivered','failed'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['webhook_id','event','status','created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('api_subscriptions');
        Schema::dropIfExists('api_plans');
        Schema::dropIfExists('api_consumers');
    }
};
```

- [ ] **Step 8.7: Write Admin migration** â€” `database/migrations/2026_06_24_081100_create_admin_module_tables.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string','integer','decimal','boolean','json'])->default('string');
            $table->string('group')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_enabled')->default(false);
            $table->json('conditions')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); $table->string('session_id')->nullable();
            $table->string('event_name'); $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent_hash', 32)->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['event_name','occurred_at']);
            $table->index(['user_id','occurred_at']);
        });

        Schema::create('analytics_pageviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); $table->string('session_id')->nullable();
            $table->string('path'); $table->string('referrer')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['path','viewed_at']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr'); $table->string('name_en')->nullable();
            $table->enum('type', ['financial','verification','trading','compliance','usage']);
            $table->enum('format', ['pdf','xlsx','csv'])->default('pdf');
            $table->json('parameters')->nullable();
            $table->enum('status', ['queued','processing','completed','failed'])->default('queued');
            $table->string('file_path')->nullable();
            $table->uuid('generated_by');
            $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('admin_id'); $table->uuid('target_user_id');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('reason')->nullable();
            $table->index(['admin_id','started_at']);
        });

        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->enum('status', ['healthy','degraded','down'])->default('healthy');
            $table->integer('response_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->index(['service','checked_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('system_health_checks');
        Schema::dropIfExists('impersonation_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('analytics_pageviews');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('system_settings');
    }
};
```

- [ ] **Step 8.8: Run all migrations**

```bash
php artisan migrate --step
```

Expected: No errors. All tables created.

- [ ] **Step 8.9: Verify table count**

```bash
php artisan db:show --counts
```

Expected: ~156 tables, all with 0 rows.

- [ ] **Step 8.10: Commit**

```bash
git add database/migrations/
git commit -m "feat: complete all 12 module migrations â€” 156 tables"
```

---

## Task 9: Seeders â€” Roles, Permissions, Taxonomy, Settings

**Files:** `database/seeders/RolesAndPermissionsSeeder.php`, `database/seeders/TaxonomySeeder.php`, `database/seeders/SystemSettingsSeeder.php`, `database/seeders/DatabaseSeeder.php`

- [ ] **Step 9.1: Write RolesAndPermissionsSeeder**

Create `database/seeders/RolesAndPermissionsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Companies
            'companies.create', 'companies.edit', 'companies.delete', 'companies.view',
            'companies.verify', 'companies.feature', 'companies.suspend',
            // Offerings
            'offerings.create', 'offerings.edit', 'offerings.publish', 'offerings.close',
            'offerings.cmf_review', 'offerings.cmf_approve', 'offerings.cmf_reject',
            // Investors
            'investors.kyc_review', 'investors.kyc_approve', 'investors.kyc_reject',
            'investors.pledge', 'investors.portfolio_view',
            // Payments
            'payments.view', 'payments.refund', 'payments.payout_approve',
            // Compliance
            'compliance.view', 'compliance.sar_create', 'compliance.sar_file',
            'compliance.aml_screen', 'compliance.rule_manage',
            // Users
            'users.view', 'users.suspend', 'users.impersonate',
            // Admin
            'admin.dashboard', 'admin.settings', 'admin.reports', 'admin.feature_flags',
            // Support
            'tickets.view', 'tickets.reply', 'tickets.close', 'tickets.assign',
            // CMS
            'cms.pages', 'cms.blog', 'cms.announcements', 'cms.media',
            // API
            'api.consumers_manage', 'api.webhooks_manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Roles
        $roles = [
            'super_admin'    => $permissions,
            'govt_reviewer'  => ['companies.view','companies.verify','compliance.view','compliance.aml_screen'],
            'cmf_reviewer'   => ['offerings.cmf_review','offerings.cmf_approve','offerings.cmf_reject','companies.view','compliance.view'],
            'company_owner'  => ['companies.create','companies.edit','offerings.create','offerings.edit','offerings.publish','payments.view'],
            'company_member' => ['companies.edit'],
            'investor'       => ['offerings.view_public','investors.pledge','investors.portfolio_view','payments.view'],
            'public'         => [],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            if ($rolePerms) {
                $role->syncPermissions($rolePerms);
            }
        }
    }
}
```

- [ ] **Step 9.2: Write TaxonomySeeder**

Create `database/seeders/TaxonomySeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // Regions of Cameroon
        $regions = [
            ['name_fr' => 'Adamaoua', 'name_en' => 'Adamawa', 'code' => 'AD'],
            ['name_fr' => 'Centre', 'name_en' => 'Centre', 'code' => 'CE'],
            ['name_fr' => 'Est', 'name_en' => 'East', 'code' => 'ES'],
            ['name_fr' => 'ExtrÃªme-Nord', 'name_en' => 'Far North', 'code' => 'EN'],
            ['name_fr' => 'Littoral', 'name_en' => 'Littoral', 'code' => 'LT'],
            ['name_fr' => 'Nord', 'name_en' => 'North', 'code' => 'NO'],
            ['name_fr' => 'Nord-Ouest', 'name_en' => 'North West', 'code' => 'NW'],
            ['name_fr' => 'Ouest', 'name_en' => 'West', 'code' => 'OU'],
            ['name_fr' => 'Sud', 'name_en' => 'South', 'code' => 'SU'],
            ['name_fr' => 'Sud-Ouest', 'name_en' => 'South West', 'code' => 'SW'],
        ];

        foreach ($regions as $r) {
            DB::table('regions')->insertOrIgnore(array_merge($r, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Major cities
        $littoral = DB::table('regions')->where('code', 'LT')->value('id');
        $centre   = DB::table('regions')->where('code', 'CE')->value('id');
        $ouest    = DB::table('regions')->where('code', 'OU')->value('id');
        $sw       = DB::table('regions')->where('code', 'SW')->value('id');

        $cities = [
            ['region_id' => $littoral, 'name_fr' => 'Douala', 'name_en' => 'Douala', 'slug' => 'douala', 'latitude' => 4.0511, 'longitude' => 9.7679],
            ['region_id' => $centre,   'name_fr' => 'YaoundÃ©', 'name_en' => 'YaoundÃ©', 'slug' => 'yaounde', 'latitude' => 3.8480, 'longitude' => 11.5021],
            ['region_id' => $ouest,    'name_fr' => 'Bafoussam', 'name_en' => 'Bafoussam', 'slug' => 'bafoussam', 'latitude' => 5.4737, 'longitude' => 10.4162],
            ['region_id' => $sw,       'name_fr' => 'LimbÃ©', 'name_en' => 'Limbe', 'slug' => 'limbe', 'latitude' => 4.0186, 'longitude' => 9.1993],
        ];

        foreach ($cities as $c) {
            DB::table('cities')->insertOrIgnore(array_merge($c, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Industries
        $industries = [
            ['name_fr' => 'Agriculture & Agroalimentaire', 'name_en' => 'Agriculture & Food', 'slug' => 'agriculture', 'icon' => 'ti-plant'],
            ['name_fr' => 'Banque & Finance', 'name_en' => 'Banking & Finance', 'slug' => 'finance', 'icon' => 'ti-building-bank'],
            ['name_fr' => 'Construction & Immobilier', 'name_en' => 'Construction & Real Estate', 'slug' => 'construction', 'icon' => 'ti-building'],
            ['name_fr' => 'Commerce & Distribution', 'name_en' => 'Trade & Distribution', 'slug' => 'commerce', 'icon' => 'ti-shopping-cart'],
            ['name_fr' => 'Ã‰nergie & Mines', 'name_en' => 'Energy & Mining', 'slug' => 'energie', 'icon' => 'ti-bolt'],
            ['name_fr' => 'SantÃ©', 'name_en' => 'Healthcare', 'slug' => 'sante', 'icon' => 'ti-heart-rate-monitor'],
            ['name_fr' => 'Technologies & NumÃ©rique', 'name_en' => 'Tech & Digital', 'slug' => 'tech', 'icon' => 'ti-cpu'],
            ['name_fr' => 'TÃ©lÃ©communications', 'name_en' => 'Telecommunications', 'slug' => 'telecom', 'icon' => 'ti-wifi'],
            ['name_fr' => 'Transport & Logistique', 'name_en' => 'Transport & Logistics', 'slug' => 'transport', 'icon' => 'ti-truck'],
            ['name_fr' => 'Ã‰ducation & Formation', 'name_en' => 'Education & Training', 'slug' => 'education', 'icon' => 'ti-school'],
            ['name_fr' => 'Tourisme & HÃ´tellerie', 'name_en' => 'Tourism & Hospitality', 'slug' => 'tourisme', 'icon' => 'ti-beach'],
            ['name_fr' => 'Services aux Entreprises', 'name_en' => 'Business Services', 'slug' => 'services', 'icon' => 'ti-briefcase'],
        ];

        foreach ($industries as $i) {
            DB::table('industries')->insertOrIgnore(array_merge($i, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Verification tiers
        $tiers = [
            ['name' => 'Non VÃ©rifiÃ©', 'slug' => 'unverified', 'description_fr' => 'Entreprise non encore vÃ©rifiÃ©e', 'description_en' => 'Company not yet verified', 'level' => 0],
            ['name' => 'Basique', 'slug' => 'basic', 'description_fr' => 'VÃ©rification documentaire de base (RCCM)', 'description_en' => 'Basic document check (RCCM)', 'level' => 1],
            ['name' => 'VÃ©rifiÃ©', 'slug' => 'verified', 'description_fr' => 'VÃ©rification complÃ¨te (RCCM + NIU + CNPS)', 'description_en' => 'Full check (RCCM + NIU + CNPS)', 'level' => 2],
            ['name' => 'CertifiÃ©', 'slug' => 'certified', 'description_fr' => 'Certification gouvernementale complÃ¨te', 'description_en' => 'Full government certification', 'level' => 3],
        ];

        foreach ($tiers as $t) {
            DB::table('verification_tiers')->insertOrIgnore(array_merge($t, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
```

- [ ] **Step 9.3: Write SystemSettingsSeeder**

Create `database/seeders/SystemSettingsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_fee_pct',         'value' => '2.5',    'type' => 'decimal', 'group' => 'payments',    'is_public' => false],
            ['key' => 'vat_pct',                  'value' => '19.25',  'type' => 'decimal', 'group' => 'payments',    'is_public' => false],
            ['key' => 'min_pledge_amount',         'value' => '10000',  'type' => 'integer', 'group' => 'trading',     'is_public' => true],
            ['key' => 'max_pledge_amount',         'value' => '0',      'type' => 'integer', 'group' => 'trading',     'is_public' => true],  // 0 = unlimited
            ['key' => 'otp_expiry_minutes',        'value' => '10',     'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'max_login_attempts',        'value' => '5',      'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'lockout_minutes',           'value' => '30',     'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'default_locale',            'value' => 'fr',     'type' => 'string',  'group' => 'app',         'is_public' => true],
            ['key' => 'maintenance_mode',          'value' => 'false',  'type' => 'boolean', 'group' => 'app',         'is_public' => true],
            ['key' => 'registration_open',         'value' => 'true',   'type' => 'boolean', 'group' => 'app',         'is_public' => true],
            ['key' => 'cmf_required_for_offering', 'value' => 'true',   'type' => 'boolean', 'group' => 'trading',     'is_public' => false],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->insertOrIgnore(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
```

- [ ] **Step 9.4: Update DatabaseSeeder**

Write `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            TaxonomySeeder::class,
            SystemSettingsSeeder::class,
        ]);
    }
}
```

- [ ] **Step 9.5: Run seeders**

```bash
php artisan db:seed
```

Expected: No errors. Each seeder reports completion.

- [ ] **Step 9.6: Commit**

```bash
git add database/seeders/
git commit -m "feat: roles/permissions, taxonomy, and system settings seeders"
```

---

## Task 10: Laravel Passport + OAuth Scopes

**Files:** `config/passport.php`, `app/Providers/AppServiceProvider.php`

- [ ] **Step 10.1: Install Passport**

```bash
php artisan install:api --passport
```

Expected: Creates `oauth_clients`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_auth_codes`, `oauth_personal_access_clients` tables.

- [ ] **Step 10.2: Define OAuth scopes in AppServiceProvider**

Edit `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::tokensCan([
            // Public read
            'directory:read'         => 'View company directory',
            'offerings:read'         => 'View share offerings',

            // Company management
            'companies:write'        => 'Create and edit companies',
            'companies:documents'    => 'Upload company documents',
            'companies:verify'       => 'Submit verification applications',

            // Offerings
            'offerings:write'        => 'Create and manage share offerings',
            'offerings:cmf'          => 'CMF review and approval actions',

            // Investor
            'investor:profile'       => 'Manage investor profile and KYC',
            'investor:pledge'        => 'Submit investment pledges',
            'investor:portfolio'     => 'View investment portfolio',

            // Payments
            'payments:initiate'      => 'Initiate payments',
            'payments:view'          => 'View payment history',

            // Admin
            'admin:dashboard'        => 'Access admin dashboard',
            'admin:users'            => 'Manage users',
            'admin:compliance'       => 'Access compliance tools',
            'admin:reports'          => 'Generate reports',

            // Notifications
            'notifications:read'     => 'Read notifications',
            'notifications:write'    => 'Manage notification preferences',

            // Support
            'support:tickets'        => 'Create and view support tickets',
            'support:admin'          => 'Admin support management',
        ]);

        Passport::setDefaultScope([
            'directory:read',
            'offerings:read',
            'notifications:read',
        ]);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
```

- [ ] **Step 10.3: Create Passport clients**

```bash
php artisan passport:client --personal --name="CamDirectory Personal Access"
php artisan passport:client --client --name="CamDirectory Server Client"
```

Copy the client IDs and secrets to `.env`:

```ini
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=<id from above>
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=<secret from above>
```

- [ ] **Step 10.4: Verify OAuth endpoints exist**

```bash
php artisan route:list --path=oauth
```

Expected: Lists `/oauth/token`, `/oauth/authorize`, `/oauth/clients`, etc.

- [ ] **Step 10.5: Commit**

```bash
git add app/Providers/AppServiceProvider.php config/
git commit -m "feat: configure Passport OAuth 2.0 with all platform scopes"
```

---

## Task 11: Auth Module â€” User Model + Auth Service

**Files:** `app/Modules/Auth/Models/User.php`, `app/Modules/Auth/Models/OtpVerification.php`, `app/Modules/Auth/Models/TwoFactorSetting.php`, `app/Modules/Auth/Services/AuthService.php`

- [ ] **Step 11.1: Write User model**

Create `app/Modules/Auth/Models/User.php`:

```php
<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    protected $keyType    = 'string';
    public    $incrementing = false;
    protected $guard_name = 'api';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'password', 'status', 'locale', 'avatar_url', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'last_login_at'      => 'datetime',
            'password'           => 'hashed',
        ];
    }

    public function twoFactorSetting(): HasOne
    {
        return $this->hasOne(TwoFactorSetting::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }
}
```

- [ ] **Step 11.2: Write OtpVerification model**

Create `app/Modules/Auth/Models/OtpVerification.php`:

```php
<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'user_id', 'identifier', 'type', 'code',
        'expires_at', 'attempts', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'   => 'datetime',
            'verified_at'  => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= 5;
    }
}
```

- [ ] **Step 11.3: Write TwoFactorSetting model**

Create `app/Modules/Auth/Models/TwoFactorSetting.php`:

```php
<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorSetting extends Model
{
    protected $fillable = [
        'user_id', 'totp_enabled', 'totp_secret',
        'totp_recovery_codes', 'sms_enabled',
    ];

    protected function casts(): array
    {
        return [
            'totp_enabled'       => 'boolean',
            'sms_enabled'        => 'boolean',
            'totp_recovery_codes' => 'encrypted:array',
            'totp_secret'        => 'encrypted',
        ];
    }
}
```

- [ ] **Step 11.4: Write AuthService**

Create `app/Modules/Auth/Services/AuthService.php`:

```php
<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\LoginAttempt;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Models\UserPreference;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => $data['password'],
            'locale'     => $data['locale'] ?? 'fr',
            'status'     => 'pending',
        ]);

        // Create default preference record
        UserPreference::create(['user_id' => $user->id]);

        $user->assignRole('public');

        return $user;
    }

    public function attempt(string $email, string $password, string $ip, string $userAgent): ?User
    {
        $user = User::where('email', $email)->first();

        $success = $user && Hash::check($password, $user->password);

        LoginAttempt::create([
            'identifier'     => $email,
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'successful'     => $success,
            'failure_reason' => $success ? null : ($user ? 'wrong_password' : 'user_not_found'),
        ]);

        if (!$success) {
            return null;
        }

        $user->update(['last_login_at' => now(), 'last_login_ip' => $ip]);

        return $user;
    }

    public function issueToken(User $user, array $scopes = []): PersonalAccessTokenResult
    {
        if (empty($scopes)) {
            $scopes = ['directory:read', 'offerings:read', 'notifications:read'];
        }

        return $user->createToken('api-token', $scopes);
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->where('revoked', false)->each(fn($t) => $t->revoke());
    }
}
```

- [ ] **Step 11.5: Write LoginAttempt model**

Create `app/Modules/Auth/Models/LoginAttempt.php`:

```php
<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'identifier', 'ip_address', 'user_agent',
        'successful', 'failure_reason', 'created_at',
    ];

    protected function casts(): array
    {
        return ['successful' => 'boolean', 'created_at' => 'datetime'];
    }
}
```

- [ ] **Step 11.6: Write UserPreference model**

Create `app/Modules/Auth/Models/UserPreference.php`:

```php
<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id', 'email_notifications', 'sms_notifications',
        'push_notifications', 'marketing_emails', 'locale', 'timezone',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'sms_notifications'   => 'boolean',
            'push_notifications'  => 'boolean',
            'marketing_emails'    => 'boolean',
        ];
    }
}
```

- [ ] **Step 11.7: Commit**

```bash
git add app/Modules/Auth/Models/ app/Modules/Auth/Services/
git commit -m "feat: Auth module models and AuthService"
```

---

## Task 12: Auth Endpoints â€” Register + Login + Logout + Refresh

**Files:** Controllers, Requests, Resources, Routes

- [ ] **Step 12.1: Write failing tests**

Create `tests/Feature/Auth/RegisterTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name'            => 'Jean',
            'last_name'             => 'Dupont',
            'email'                 => 'jean@example.cm',
            'phone'                 => '+237612345678',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'locale'                => 'fr',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'data' => ['user' => ['id','email','first_name'], 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'jean@example.cm', 'status' => 'pending']);
    }

    public function test_register_validates_duplicate_email(): void
    {
        \App\Modules\Auth\Models\User::factory()->create(['email' => 'jean@example.cm']);

        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Jean', 'last_name' => 'Dupont',
            'email' => 'jean@example.cm', 'password' => 'Password1!', 'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(422)->assertJsonPath('errors.email.0', fn($v) => str_contains($v, 'taken') || str_contains($v, 'dÃ©jÃ '));
    }

    public function test_register_requires_strong_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Jean', 'last_name' => 'Dupont',
            'email' => 'jean@example.cm', 'password' => '123', 'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }
}
```

Create `tests/Feature/Auth/LoginTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.cm',
            'password' => bcrypt('Password1!'),
            'status'   => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.cm',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'token_type', 'expires_in', 'user']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.cm', 'password' => bcrypt('Password1!')]);

        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'wrong'])
             ->assertStatus(401);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create(['email' => 'test@example.cm', 'password' => bcrypt('Password1!'), 'status' => 'suspended']);

        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'Password1!'])
             ->assertStatus(403);
    }
}
```

- [ ] **Step 12.2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/Auth/ --stop-on-failure
```

Expected: FAIL â€” routes not found (404).

- [ ] **Step 12.3: Write RegisterRequest**

Create `app/Modules/Auth/Requests/RegisterRequest.php`:

```php
<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'email:rfc,dns', 'max:150', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password'   => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'locale'     => ['nullable', 'in:fr,en'],
        ];
    }
}
```

- [ ] **Step 12.4: Write LoginRequest**

Create `app/Modules/Auth/Requests/LoginRequest.php`:

```php
<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

- [ ] **Step 12.5: Write UserResource**

Create `app/Modules/Auth/Resources/UserResource.php`:

```php
<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'full_name'          => $this->full_name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'email_verified'     => $this->hasVerifiedEmail(),
            'phone_verified'     => $this->hasVerifiedPhone(),
            'status'             => $this->status,
            'locale'             => $this->locale,
            'avatar_url'         => $this->avatar_url,
            'roles'              => $this->getRoleNames(),
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 12.6: Write Auth Controllers**

Create `app/Modules/Auth/Controllers/RegisterController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Jobs\SendVerificationEmailJob;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user  = $this->auth->register($request->validated());
        $token = $this->auth->issueToken($user);

        SendVerificationEmailJob::dispatch($user)->onQueue('high');

        return $this->created([
            'user'       => new UserResource($user),
            'token'      => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.personal_access_tokens_expire_in'),
        ], __('auth.registered'));
    }
}
```

Create `app/Modules/Auth/Controllers/LoginController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->auth->attempt(
            $request->email,
            $request->password,
            $request->ip(),
            $request->userAgent() ?? '',
        );

        if (!$user) {
            return $this->error(__('auth.failed'), 401);
        }

        if ($user->status === 'suspended') {
            return $this->error(__('auth.suspended'), 403);
        }

        // If 2FA enabled, return partial token requiring 2FA step
        if ($user->twoFactorSetting?->totp_enabled || $user->twoFactorSetting?->sms_enabled) {
            $partial = $user->createToken('2fa-partial', ['2fa:pending']);
            return $this->success([
                'requires_2fa' => true,
                'partial_token' => $partial->accessToken,
            ], __('auth.two_factor_required'), 200);
        }

        $token = $this->auth->issueToken($user, $this->scopesForUser($user));

        return $this->success([
            'user'       => new UserResource($user),
            'token'      => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 15 * 24 * 60 * 60,
        ], __('auth.login_success'));
    }

    private function scopesForUser($user): array
    {
        $base = ['directory:read', 'offerings:read', 'notifications:read', 'support:tickets'];

        if ($user->hasRole('company_owner') || $user->hasRole('company_member')) {
            $base = array_merge($base, ['companies:write', 'companies:documents', 'companies:verify', 'offerings:write', 'payments:view']);
        }

        if ($user->hasRole('investor')) {
            $base = array_merge($base, ['investor:profile', 'investor:pledge', 'investor:portfolio', 'payments:initiate', 'payments:view']);
        }

        if ($user->hasRole('super_admin') || $user->hasRole('govt_reviewer') || $user->hasRole('cmf_reviewer')) {
            $base = array_merge($base, ['admin:dashboard', 'admin:users', 'admin:compliance', 'admin:reports', 'offerings:cmf']);
        }

        return $base;
    }
}
```

Create `app/Modules/Auth/Controllers/LogoutController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return $this->success(null, __('auth.logout_success'));
    }
}
```

- [ ] **Step 12.7: Write Auth User factory**

Create `database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'phone'             => '+237' . fake()->numerify('#########'),
            'email_verified_at' => now(),
            'password'          => 'Password1!',
            'status'            => 'active',
            'locale'            => 'fr',
            'timezone'          => 'Africa/Douala',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null, 'status' => 'pending']);
    }

    public function suspended(): static
    {
        return $this->state(fn() => ['status' => 'suspended']);
    }
}
```

- [ ] **Step 12.8: Write Auth routes**

Write `app/Modules/Auth/Routes/api.php`:

```php
<?php

use App\Modules\Auth\Controllers\EmailVerificationController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\LogoutController;
use App\Modules\Auth\Controllers\OtpController;
use App\Modules\Auth\Controllers\PasswordController;
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Public
    Route::post('/register',       RegisterController::class);
    Route::post('/login',          LoginController::class);
    Route::post('/password/reset', [PasswordController::class, 'sendResetLink']);
    Route::post('/password/update', [PasswordController::class, 'resetPassword']);

    // OTP
    Route::post('/otp/send',       [OtpController::class, 'send']);
    Route::post('/otp/verify',     [OtpController::class, 'verify']);

    // Authenticated
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout',                 LogoutController::class);
        Route::post('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
        Route::post('/email/resend',           [EmailVerificationController::class, 'resend']);
        Route::post('/2fa/setup',              [TwoFactorController::class, 'setup']);
        Route::post('/2fa/verify',             [TwoFactorController::class, 'verify']);
        Route::post('/2fa/disable',            [TwoFactorController::class, 'disable']);
        Route::post('/2fa/recovery',           [TwoFactorController::class, 'useRecoveryCode']);
    });
});
```

- [ ] **Step 12.9: Run tests**

```bash
php artisan test tests/Feature/Auth/ -v
```

Expected: All tests pass (green).

- [ ] **Step 12.10: Commit**

```bash
git add app/Modules/Auth/ database/factories/ tests/Feature/Auth/
git commit -m "feat: register, login, and logout endpoints with tests"
```

---

## Task 13: OTP Service + 2FA Service + Email Verification

**Files:** `app/Modules/Auth/Services/OtpService.php`, `TwoFactorService.php`, Controllers, Jobs

- [ ] **Step 13.1: Write OtpService**

Create `app/Modules/Auth/Services/OtpService.php`:

```php
<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Jobs\SendOtpJob;
use App\Modules\Auth\Models\OtpVerification;
use Illuminate\Support\Str;

class OtpService
{
    public function send(string $identifier, string $type, string $ip, ?string $userId = null): OtpVerification
    {
        // Invalidate existing active OTPs
        OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        $code = (string) random_int(100000, 999999);

        $otp = OtpVerification::create([
            'user_id'    => $userId,
            'identifier' => $identifier,
            'type'       => $type,
            'code'       => $code,
            'expires_at' => now()->addMinutes((int) config('app.otp_expiry_minutes', 10)),
            'attempts'   => 0,
            'ip_address' => $ip,
        ]);

        SendOtpJob::dispatch($identifier, $type, $code)->onQueue('critical');

        return $otp;
    }

    public function verify(string $identifier, string $type, string $code): bool
    {
        $otp = OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->increment('attempts');

        if ($otp->hasExceededAttempts()) {
            $otp->update(['expires_at' => now()]);
            return false;
        }

        if (!hash_equals($otp->code, $code)) {
            return false;
        }

        $otp->update(['verified_at' => now()]);
        return true;
    }
}
```

- [ ] **Step 13.2: Write TwoFactorService**

Create `app/Modules/Auth/Services/TwoFactorService.php`:

```php
<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\TwoFactorSetting;
use App\Modules\Auth\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private Google2FA $google2fa) {}

    public function setupTotp(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $setting = TwoFactorSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['totp_enabled' => false, 'sms_enabled' => false]
        );

        $setting->update(['totp_secret' => $secret]);

        $qrUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );

        $qrSvg = $this->generateQrSvg($qrUrl);

        return [
            'secret'   => $secret,
            'qr_svg'   => $qrSvg,
            'qr_url'   => $qrUrl,
        ];
    }

    public function enableTotp(User $user, string $code): bool
    {
        $setting = $user->twoFactorSetting;

        if (!$setting || !$setting->totp_secret) {
            return false;
        }

        $valid = $this->google2fa->verifyKey($setting->totp_secret, $code);

        if ($valid) {
            $recoveryCodes = $this->generateRecoveryCodes();
            $setting->update([
                'totp_enabled'        => true,
                'totp_recovery_codes' => $recoveryCodes,
            ]);
        }

        return $valid;
    }

    public function verifyTotp(User $user, string $code): bool
    {
        $setting = $user->twoFactorSetting;
        if (!$setting?->totp_enabled || !$setting->totp_secret) {
            return false;
        }

        return $this->google2fa->verifyKey($setting->totp_secret, $code) !== false;
    }

    public function disableTotp(User $user, string $code): bool
    {
        if (!$this->verifyTotp($user, $code)) {
            return false;
        }

        $user->twoFactorSetting?->update([
            'totp_enabled' => false,
            'totp_secret'  => null,
            'totp_recovery_codes' => null,
        ]);

        return true;
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $setting = $user->twoFactorSetting;
        if (!$setting?->totp_recovery_codes) {
            return false;
        }

        $codes = $setting->totp_recovery_codes;
        $idx   = array_search(hash('sha256', $code), array_map(fn($c) => hash('sha256', $c), $codes), true);

        if ($idx === false) {
            return false;
        }

        // Consume the code
        unset($codes[$idx]);
        $setting->update(['totp_recovery_codes' => array_values($codes)]);

        return true;
    }

    private function generateRecoveryCodes(): array
    {
        return array_map(fn() => strtoupper(bin2hex(random_bytes(5))), range(1, 8));
    }

    private function generateQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $writer   = new Writer($renderer);
        return $writer->writeString($url);
    }
}
```

- [ ] **Step 13.3: Write OtpController**

Create `app/Modules/Auth/Controllers/OtpController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OtpController extends Controller
{
    public function __construct(
        private OtpService  $otp,
        private AuthService $auth,
    ) {}

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'type'       => ['required', Rule::in(['phone_verify','email_verify','phone_login'])],
        ]);

        $this->otp->send($data['identifier'], $data['type'], $request->ip());

        return $this->success(null, __('auth.otp_sent'));
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'type'       => ['required', Rule::in(['phone_verify','email_verify','phone_login'])],
            'code'       => ['required', 'string', 'size:6'],
        ]);

        $verified = $this->otp->verify($data['identifier'], $data['type'], $data['code']);

        if (!$verified) {
            return $this->error(__('auth.otp_invalid'), 422);
        }

        // If phone_verify, mark phone as verified
        if ($data['type'] === 'phone_verify') {
            User::where('phone', $data['identifier'])->update(['phone_verified_at' => now()]);
        }

        return $this->success(null, __('auth.otp_verified'));
    }
}
```

- [ ] **Step 13.4: Write TwoFactorController**

Create `app/Modules/Auth/Controllers/TwoFactorController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $tfa,
        private AuthService      $auth,
    ) {}

    public function setup(Request $request): JsonResponse
    {
        $data = $this->tfa->setupTotp($request->user());
        return $this->success($data, __('auth.2fa_setup'));
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();

        // Completing 2FA after login (partial token scenario)
        if ($user->tokenCan('2fa:pending')) {
            $valid = $this->tfa->verifyTotp($user, $request->code)
                     || $this->tfa->useRecoveryCode($user, $request->code);

            if (!$valid) {
                return $this->error(__('auth.2fa_invalid'), 422);
            }

            $user->token()->revoke(); // revoke partial token
            $token = $this->auth->issueToken($user);

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token->accessToken,
            ], __('auth.login_success'));
        }

        // Enabling 2FA
        $enabled = $this->tfa->enableTotp($user, $request->code);

        if (!$enabled) {
            return $this->error(__('auth.2fa_invalid'), 422);
        }

        return $this->success(null, __('auth.2fa_enabled'));
    }

    public function disable(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        if (!$this->tfa->disableTotp($request->user(), $request->code)) {
            return $this->error(__('auth.2fa_invalid'), 422);
        }

        return $this->success(null, __('auth.2fa_disabled'));
    }

    public function useRecoveryCode(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        if (!$this->tfa->useRecoveryCode($request->user(), $request->code)) {
            return $this->error(__('auth.recovery_code_invalid'), 422);
        }

        return $this->success(null, __('auth.recovery_code_used'));
    }
}
```

- [ ] **Step 13.5: Write SendOtpJob**

Create `app/Modules/Auth/Jobs/SendOtpJob.php`:

```php
<?php

namespace App\Modules\Auth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private string $identifier,
        private string $type,
        private string $code,
    ) {}

    public function handle(): void
    {
        $isPhone = str_starts_with($this->identifier, '+');

        if ($isPhone) {
            $this->sendSms();
        } else {
            $this->sendEmail();
        }
    }

    private function sendSms(): void
    {
        // Infobip / AfricasTalking integration
        // Replace with actual SMS provider
        Log::channel('sms')->info('OTP SMS', [
            'to'   => $this->identifier,
            'code' => $this->code,
            'type' => $this->type,
        ]);

        // Production: Http::post(config('services.sms.url'), [...])
    }

    private function sendEmail(): void
    {
        // Queued mail via Laravel Mail
        \Illuminate\Support\Facades\Mail::raw(
            "Votre code OTP : {$this->code} (valable 10 minutes)",
            fn($m) => $m->to($this->identifier)->subject('Code de vÃ©rification')
        );
    }
}
```

- [ ] **Step 13.6: Write SendVerificationEmailJob**

Create `app/Modules/Auth/Jobs/SendVerificationEmailJob.php`:

```php
<?php

namespace App\Modules\Auth\Jobs;

use App\Modules\Auth\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private User $user) {}

    public function handle(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)],
        );

        \Illuminate\Support\Facades\Mail::raw(
            "Bonjour {$this->user->first_name},\n\nVÃ©rifiez votre email : {$url}",
            fn($m) => $m->to($this->user->email)->subject('VÃ©rifiez votre adresse email')
        );
    }
}
```

- [ ] **Step 13.7: Write EmailVerificationController**

Create `app/Modules/Auth/Controllers/EmailVerificationController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Jobs\SendVerificationEmailJob;
use App\Modules\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->email), $hash)) {
            return $this->error(__('auth.verification_link_invalid'), 400);
        }

        if (!$request->hasValidSignature()) {
            return $this->error(__('auth.verification_link_expired'), 410);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, __('auth.already_verified'));
        }

        $user->update(['email_verified_at' => now(), 'status' => 'active']);
        $user->assignRole('company_owner'); // default role after email verify

        return $this->success(null, __('auth.email_verified'));
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->error(__('auth.already_verified'), 400);
        }

        SendVerificationEmailJob::dispatch($user)->onQueue('high');

        return $this->success(null, __('auth.verification_sent'));
    }
}
```

- [ ] **Step 13.8: Write PasswordController**

Create `app/Modules/Auth/Controllers/PasswordController.php`:

```php
<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->success(null, __($status))
            : $this->error(__($status), 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update(['password' => Hash::make($password), 'remember_token' => Str::random(60)]);
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(null, __($status))
            : $this->error(__($status), 422);
    }
}
```

- [ ] **Step 13.9: Run full auth test suite**

```bash
php artisan test tests/Feature/Auth/ -v
```

Expected: All tests pass.

- [ ] **Step 13.10: Commit**

```bash
git add app/Modules/Auth/
git commit -m "feat: complete Auth module â€” OTP, 2FA, email verification, password reset"
```

---

## Task 14: Health Check + Horizon + Meilisearch Config

- [ ] **Step 14.1: Write HealthController**

Create `app/Http/Controllers/HealthController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database'    => $this->checkDatabase(),
            'redis'       => $this->checkRedis(),
            'meilisearch' => $this->checkMeilisearch(),
        ];

        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'healthy');

        return $this->success($checks, 'ok', $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    private function checkMeilisearch(): array
    {
        try {
            $start = microtime(true);
            $client = new \MeiliSearch\Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
            $client->health();
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }
}
```

- [ ] **Step 14.2: Register health route in routes/api.php**

Edit `routes/api.php`:

```php
<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');
```

- [ ] **Step 14.3: Write health test**

Create `tests/Feature/HealthTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_200(): void
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['database', 'redis']]);
    }
}
```

- [ ] **Step 14.4: Configure Horizon**

Edit `config/horizon.php` â€” set queue names and worker pools:

```php
'environments' => [
    'production' => [
        'supervisor-critical' => [
            'connection' => 'redis',
            'queue'      => ['critical'],
            'balance'    => 'auto',
            'processes'  => 5,
            'tries'      => 3,
            'timeout'    => 60,
        ],
        'supervisor-high' => [
            'connection' => 'redis',
            'queue'      => ['high'],
            'balance'    => 'auto',
            'processes'  => 4,
            'tries'      => 3,
            'timeout'    => 120,
        ],
        'supervisor-default' => [
            'connection' => 'redis',
            'queue'      => ['default'],
            'balance'    => 'auto',
            'processes'  => 3,
            'tries'      => 3,
            'timeout'    => 180,
        ],
        'supervisor-low' => [
            'connection' => 'redis',
            'queue'      => ['low','maintenance'],
            'balance'    => 'simple',
            'processes'  => 2,
            'tries'      => 2,
            'timeout'    => 300,
        ],
    ],
    'local' => [
        'supervisor-local' => [
            'connection' => 'redis',
            'queue'      => ['critical','high','default','low','maintenance'],
            'balance'    => 'simple',
            'processes'  => 3,
            'tries'      => 1,
            'timeout'    => 60,
        ],
    ],
],
```

- [ ] **Step 14.5: Configure Scout/Meilisearch in config/scout.php**

```php
// In config/scout.php, the meilisearch section:
'meilisearch' => [
    'host'    => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key'     => env('MEILISEARCH_KEY'),
    'index-settings' => [
        // Will be configured per model in Day 2
    ],
],
```

- [ ] **Step 14.6: Run all Day 1 tests**

```bash
php artisan test --parallel
```

Expected: All tests pass. No errors.

- [ ] **Step 14.7: Final Day 1 commit**

```bash
git add .
git commit -m "feat: Day 1 complete â€” foundation, all migrations, auth module, health check"
```

---

## Task 15: CI/CD Setup

**File:** `.github/workflows/ci.yml`

- [ ] **Step 15.1: Write GitHub Actions workflow**

Create `.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: camerooncompany_test
        ports: [3306:3306]
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

      redis:
        image: redis:7-alpine
        ports: [6379:6379]
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_mysql, redis, bcmath
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction

      - name: Copy .env
        run: cp .env.example .env.testing

      - name: Generate key
        run: php artisan key:generate --env=testing

      - name: Run migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: camerooncompany_test
          DB_USERNAME: root
          DB_PASSWORD: password
          QUEUE_CONNECTION: sync
          CACHE_STORE: array
          SESSION_DRIVER: array
        run: php artisan migrate --env=testing --force

      - name: Run tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: camerooncompany_test
          DB_USERNAME: root
          DB_PASSWORD: password
          QUEUE_CONNECTION: sync
          CACHE_STORE: array
          SESSION_DRIVER: array
        run: php artisan test --parallel --coverage-clover coverage.xml

      - name: Run Pint
        run: vendor/bin/pint --test
```

- [ ] **Step 15.2: Commit CI config**

```bash
git add .github/
git commit -m "chore: add GitHub Actions CI pipeline"
```

---

## Day 1 Done âœ“

**Deliverables:**
- Laravel 11 modular monolith running at `http://camerooncompany.test`
- 12 module directories scaffolded under `app/Modules/`
- All 156 database tables created via 12 migration files
- Roles (7) + permissions (30+) seeded
- Cameroon taxonomy seeded (10 regions, cities, 12 industries, 4 verification tiers)
- Laravel Passport OAuth 2.0 with 20 scopes
- Auth endpoints: POST `/api/v1/auth/register`, `/login`, `/logout`, `/otp/send`, `/otp/verify`, `/2fa/setup`, `/2fa/verify`, `/2fa/disable`, `/email/verify`, `/email/resend`, `/password/reset`
- OTP service (phone + email), TOTP 2FA with recovery codes, email verification
- Health check: GET `/api/v1/health`
- Horizon configured (5 queues, 4 supervisor pools)
- GitHub Actions CI

**Next:** [Day 2 Plan](2026-06-24-day2-directory-verification.md) â€” Company directory CRUD, verification workflow, Meilisearch indexing, 86 directory/verification endpoints.
