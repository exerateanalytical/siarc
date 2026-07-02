<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking: the dashboard and auth pages must never be framed.
        $response->headers->set('X-Frame-Options', 'DENY');
        // Stop browsers from MIME-sniffing a response away from its declared type.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Don't leak full URLs (which may carry ids/tokens) to third parties.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Drop powerful features the platform never uses.
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // HSTS only makes sense once served over TLS.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
