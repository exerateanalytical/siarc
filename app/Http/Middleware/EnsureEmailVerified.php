<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates write actions (business/product edits, sending messages) behind a
 * verified email address. Reads are deliberately left open. The flag is
 * checked against the database, not the session copy, so verifying in one
 * tab immediately unlocks every other session.
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $verified = DB::table('users')->where('id', $siacUser['id'])->value('is_email_verified');
        if (! $verified) {
            $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

            return redirect()->route('email.verify')->with('info', $lang === 'fr'
                ? 'Veuillez vérifier votre adresse email pour continuer.'
                : 'Please verify your email address to continue.');
        }

        return $next($request);
    }
}
