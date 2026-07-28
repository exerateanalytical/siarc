<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        /*
         * Private documents: KYC scans, payment proofs, verification files.
         *
         * 'serve' is OFF. Laravel registers a GET|PUT /storage/{path} route for
         * every local disk with serve => true, deriving the URI from the disk's
         * 'url' — and this disk has none, so it defaults to '/storage', the
         * exact path the public disk uses. Two served disks on one URI is a
         * hard InvalidArgumentException at boot, so only one of these two may
         * carry the flag, and it has to be the public one (see below).
         *
         * Nothing is lost: this disk is never reached by URL. The one place it
         * is read from the web (the payment-proof download in routes/web.php)
         * goes through its own authorised route and Storage::response().
         */
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        /*
         * Public uploads: business logos, product images, article covers.
         *
         * 'serve' => true is the fallback for hosts where `php artisan
         * storage:link` cannot create the public/storage symlink — Namecheap
         * shared hosting among them, where symlink() is sometimes disabled and
         * cPanel's file manager will not make one.
         *
         * When the symlink exists, Apache finds a real file at
         * public_html/storage/... and serves it directly; PHP is never entered
         * and this route costs nothing. When it does not exist, the request
         * falls through to Laravel and this route streams the same bytes out of
         * storage/app/public. Either way asset('storage/x.jpg') resolves, which
         * is what every image accessor on the models depends on.
         *
         * It is not an upload hole: the PUT half of the pair requires a valid
         * signed URL, and the GET half serves without a signature only because
         * this disk's visibility is genuinely 'public'.
         *
         * Slower than the symlink (every image becomes a PHP request, and the
         * framework sends no-store on it), so the symlink is still the thing to
         * want. This is the floor, not the target.
         */
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    // ah_resolve_public_path() (app/Support/route_helpers.php, autoloaded via
    // composer "files") returns the host-imposed document root when
    // APP_PUBLIC_PATH is set, and null otherwise. Without it, `storage:link` on
    // shared hosting creates the symlink inside the application's own public/
    // directory — which is not the document root there, so it is never
    // requested and every uploaded image 404s while the command reports success.
    'links' => [
        (ah_resolve_public_path() ?: rtrim(public_path(), '/\\')) . '/storage' => storage_path('app/public'),
    ],

];
