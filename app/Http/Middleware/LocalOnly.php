<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A route that only exists on a developer's machine.
 *
 * 404, not 403. The difference matters for anything a stranger has no business
 * knowing exists: 403 confirms the endpoint is there and only says you may not
 * have it, which is a free answer to "is this app running Scramble?" — and the
 * next question is which version and what does its index enumerate. 404 is the
 * same answer the server gives for a URL nobody ever wrote.
 */
class LocalOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('local'), 404);

        return $next($request);
    }
}
