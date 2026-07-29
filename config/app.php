<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    // Force-disabled in production for the same reason as demo_login below: the
    // .env on the server is the file most likely to have been copied from a
    // developer machine, and APP_DEBUG=true there does not merely show stack
    // traces — Laravel's error page prints the whole environment, which on this
    // deployment means the database password, the APP_KEY, the mail credentials
    // and the S3 keys, to whoever triggered the error. It must not be possible
    // to turn that on by editing one line, so it is not a setting in production.
    'debug' => env('APP_ENV') === 'production'
        ? false
        : (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | One-click demo logins
    |--------------------------------------------------------------------------
    |
    | Shows the pre-filled demo account cards on the login page. Useful when
    | showing the platform to someone; must stay off in production, where it
    | would hand every visitor a working account. Off unless explicitly enabled.
    |
    */

    // One-click sign-in as the oldest super_admin, with no password. Invaluable
    // locally, catastrophic in production — so it is force-disabled there rather
    // than trusted to the .env, which is the file most likely to be copied from
    // a developer machine.
    'demo_login' => env('APP_ENV') === 'production'
        ? false
        : (bool) env('APP_DEMO_LOGIN', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS in generated URLs
    |--------------------------------------------------------------------------
    |
    | The production site sits behind the host's TLS terminator and the origin
    | itself only ever sees plain HTTP. Trusting the forwarded headers (see
    | bootstrap/app.php) fixes request()->secure(); this fixes the other half,
    | URL generation, and pins the root to APP_URL so that a spoofed Host or
    | X-Forwarded-Host header cannot appear in a password-reset link, a
    | certificate verification URL or a QR code.
    |
    | Defaults on in production and off everywhere else, so the local checkout
    | and the test suite are unaffected. FORCE_HTTPS overrides either way.
    |
    */

    'force_https' => (bool) env('FORCE_HTTPS', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'platform_fee_percent' => env('PLATFORM_FEE_PERCENT', 2.5),
    'vat_percent'          => env('VAT_PERCENT', 19.25),

    /*
    |--------------------------------------------------------------------------
    | Host-imposed public document root
    |--------------------------------------------------------------------------
    |
    | See ah_resolve_public_path() in app/Support/route_helpers.php. This must
    | be read through config(), not env(), anywhere that runs on every request
    | (AppServiceProvider::register(), for one): once `config:cache` runs,
    | Laravel stops loading .env at boot, so a live env('APP_PUBLIC_PATH') call
    | silently returns null and public_path() reverts to the wrong directory.
    | Putting it here means config:cache bakes in the real value instead.
    |
    */

    'public_path' => env('APP_PUBLIC_PATH'),

];
