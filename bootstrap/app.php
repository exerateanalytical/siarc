<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // routes/api.php was written but never loaded — the modules each
        // register their own file, so nothing ever asked for this one. The
        // public verification API lives there and has to be reachable.
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /*
         * Trust the hosting proxy's forwarded headers.
         *
         * artisanhub237.com is served through Namecheap's edge, which
         * terminates TLS and forwards plain HTTP to the origin carrying
         * X-Forwarded-Proto: https. Untrusted, Symfony ignores that header,
         * so request()->secure() is false, url()/asset() emit http:// links
         * on an https page — which the browser then blocks as mixed content —
         * and request()->ip() reports the proxy rather than the visitor,
         * which quietly collapses every per-IP rate limiter in
         * AppServiceProvider onto a single bucket.
         *
         * `at: '*'` (the default when TRUSTED_PROXIES is unset) is the right
         * setting for shared hosting: the origin is not reachable except
         * through the host's own front end, so there is no path by which an
         * attacker could present a forged X-Forwarded-For to it. On a server
         * with a public IP, name the proxy CIDRs instead.
         *
         * The header mask is left at the framework default (the full
         * X-Forwarded-* set). Only `at` needs stating.
         *
         * Trusting X-Forwarded-Host from '*' would ordinarily let a visitor
         * dictate the host used to build absolute URLs — the classic
         * password-reset-link poisoning. It cannot here, because
         * AppServiceProvider pins the URL generator's root to config('app.url')
         * in production, so generated links ignore the request host entirely.
         */
        $middleware->trustProxies(at: '*');

        // Security response headers on every request (web + API)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Force JSON on all API routes
        $middleware->alias([
            'json'               => \App\Http\Middleware\ForceJsonResponse::class,
            'api.key'            => \App\Http\Middleware\AuthenticateApiKey::class,
            'verified.email'     => \App\Http\Middleware\EnsureEmailVerified::class,
            'locale'             => \App\Http\Middleware\SetLocale::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });
    })->create();
