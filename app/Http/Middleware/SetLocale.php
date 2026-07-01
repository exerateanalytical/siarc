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
        $locale    = strtolower(substr($locale, 0, 2));

        App::setLocale(in_array($locale, $supported, true) ? $locale : 'fr');

        $response = $next($request);
        $response->headers->set('Content-Language', App::getLocale());
        return $response;
    }
}
