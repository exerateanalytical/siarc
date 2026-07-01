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
            'method'  => $request->method(),
            'path'    => $request->path(),
            'status'  => $response->getStatusCode(),
            'ms'      => $ms,
            'ip'      => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        return $response;
    }
}
