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

        // Allow exactly the external origins the layouts use: Tailwind Play
        // CDN + unpkg (lucide) + jsdelivr (qrcodejs on the security page)
        // scripts, Google Fonts styles/fonts. Inline scripts/styles are
        // required by the Blade templates and Tailwind's runtime style
        // injection. img-src allows https: for remote product media (S3).
        // No plugins, no framing, forms post only to ourselves.
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]));

        // HSTS only makes sense once served over TLS.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
