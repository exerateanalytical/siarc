<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────
function webUser(): ?object
{
    $u = session('siac_user');
    return $u ? (object) $u : null;
}

function requireAuth(Request $request)
{
    if (!session('siac_user')) {
        return redirect('/login?next=' . urlencode($request->fullUrl()));
    }
    return null;
}

/**
 * Establish the authenticated web session for a users row.
 * Single place where siac_user is written after a successful factor check —
 * regenerates the session id to prevent session fixation.
 */
function establishSiacSession(object $user, Request $request): void
{
    $siacRole = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $user->id)
        ->orderByRaw("FIELD(roles.name,'super_admin','admin','ministry','technical_reviewer','regional_rep','moderator','business_owner') DESC")
        ->value('roles.name');

    $displayName = $user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

    DB::table('users')->where('id', $user->id)->update([
        'last_login_at' => now(),
        'last_login_ip' => $request->ip(),
        'updated_at'    => now(),
    ]);

    $request->session()->regenerate();

    session(['siac_user' => [
        'id'       => $user->id,
        'name'     => $displayName,
        'email'    => $user->email,
        'role'     => $siacRole,
        'is_admin' => in_array($siacRole, ['super_admin', 'admin', 'moderator']),
    ]]);
}

// ─────────────────────────────────────────────
// SIAC Platform — API landing
// ─────────────────────────────────────────────
use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/galerie/entreprises', [FrontendController::class, 'businessIndex'])->name('businesses.index');
Route::get('/galerie/entreprises/{slug}', [FrontendController::class, 'businessShow'])->name('businesses.show');
Route::get('/galerie/secteurs', [FrontendController::class, 'industriesIndex'])->name('industries.index');
Route::get('/galerie/recherche', [FrontendController::class, 'search'])->name('gallery.search');
Route::get('/galerie/produits/{slug}', [FrontendController::class, 'productShow'])->name('products.show');

use App\Http\Controllers\MessagingWebController;

Route::post('/galerie/messages', [MessagingWebController::class, 'send'])->name('messages.send');
Route::get('/tableau-de-bord/messages', [MessagingWebController::class, 'inbox'])->name('messages.inbox');
Route::get('/tableau-de-bord/messages/{id}', [MessagingWebController::class, 'thread'])->name('messages.thread');
Route::post('/tableau-de-bord/messages/{id}/repondre', [MessagingWebController::class, 'reply'])->name('messages.reply');

use App\Http\Controllers\ReviewWebController;

Route::post('/galerie/avis', [ReviewWebController::class, 'store'])->name('reviews.store');
Route::post('/tableau-de-bord/messages/{id}/conclure', [ReviewWebController::class, 'markDeal'])->name('messages.mark-deal');

use App\Http\Controllers\ProductActionsWebController;

Route::post('/galerie/produits/{slug}/sauvegarder', [ProductActionsWebController::class, 'toggleSave'])->name('products.toggle-save');
Route::post('/galerie/produits/{slug}/signaler', [ProductActionsWebController::class, 'report'])->name('products.report');

Route::post('/galerie/entreprises/{slug}/sauvegarder', function (Request $request, string $slug) {
    $siacUser = session('siac_user');
    if (!$siacUser) {
        return $request->wantsJson()
            ? response()->json(['message' => 'unauthenticated'], 401)
            : redirect('/login?next=' . urlencode($request->input('return_to', '/')));
    }

    $business = DB::table('businesses')->where('slug', $slug)->whereNull('deleted_at')->first();
    abort_unless((bool) $business, 404);

    $existing = DB::table('saved_businesses')
        ->where('user_id', $siacUser['id'])
        ->where('business_id', $business->id)
        ->exists();

    if ($existing) {
        DB::table('saved_businesses')->where('user_id', $siacUser['id'])->where('business_id', $business->id)->delete();
        $saved = false;
    } else {
        DB::table('saved_businesses')->insert([
            'user_id'     => $siacUser['id'],
            'business_id' => $business->id,
            'created_at'  => now(),
        ]);
        $saved = true;
    }

    if ($request->wantsJson()) {
        return response()->json(['saved' => $saved]);
    }

    return redirect($request->input('return_to', '/'))
        ->with('success', $saved ? 'Entreprise sauvegardée.' : 'Entreprise retirée des favoris.');
})->name('businesses.toggle-save');

use App\Http\Controllers\BusinessWebController;

Route::get('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'create'])->name('business.create');
Route::post('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'store'])->name('business.store');
Route::get('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'edit'])->name('business.edit');
Route::post('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'update'])->name('business.update');
Route::get('/api-interne/villes/{regionId}', [BusinessWebController::class, 'citiesForRegion'])->name('business.cities-for-region');

use App\Http\Controllers\ProductWebController;

Route::get('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'create'])->name('products.web-create');
Route::post('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'store'])->name('products.web-store');
Route::get('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'edit'])->name('products.web-edit');
Route::post('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'update'])->name('products.web-update');
Route::post('/tableau-de-bord/produits/{slug}/images/{imageId}/supprimer', [ProductWebController::class, 'destroyImage'])->name('products.web-delete-image');

use App\Http\Controllers\VerificationWebController;

Route::get('/tableau-de-bord/entreprise/verification', [VerificationWebController::class, 'show'])->name('verification.show');
Route::post('/tableau-de-bord/entreprise/verification', [VerificationWebController::class, 'apply'])->name('verification.apply');

use App\Http\Controllers\AdminWebController;

Route::get('/tableau-de-bord/admin/entreprises', [AdminWebController::class, 'businesses'])->name('admin.businesses');
Route::get('/tableau-de-bord/admin/entreprises/{id}', [AdminWebController::class, 'businessDetail'])->name('admin.businesses.detail');
Route::post('/tableau-de-bord/admin/entreprises/{id}/statut', [AdminWebController::class, 'updateBusinessStatus'])->name('admin.businesses.update-status');
Route::get('/tableau-de-bord/admin/verifications', [AdminWebController::class, 'verifications'])->name('admin.verifications');
Route::post('/tableau-de-bord/admin/verifications/{id}/approuver', [AdminWebController::class, 'approveVerification'])->name('admin.verifications.approve');
Route::post('/tableau-de-bord/admin/verifications/{id}/rejeter', [AdminWebController::class, 'rejectVerification'])->name('admin.verifications.reject');
Route::get('/tableau-de-bord/admin/utilisateurs', [AdminWebController::class, 'users'])->name('admin.users');
Route::get('/tableau-de-bord/admin/utilisateurs/{id}', [AdminWebController::class, 'userDetail'])->name('admin.users.detail');
Route::post('/tableau-de-bord/admin/utilisateurs/{id}/statut', [AdminWebController::class, 'updateUserStatus'])->name('admin.users.update-status');
Route::post('/tableau-de-bord/admin/utilisateurs/{id}/role', [AdminWebController::class, 'updateUserRole'])->name('admin.users.update-role');
Route::get('/tableau-de-bord/admin/partenaires', [AdminWebController::class, 'partners'])->name('admin.partners');
Route::post('/tableau-de-bord/admin/partenaires', [AdminWebController::class, 'storePartner'])->name('admin.partners.store');
Route::post('/tableau-de-bord/admin/partenaires/{id}', [AdminWebController::class, 'updatePartner'])->name('admin.partners.update');
Route::post('/tableau-de-bord/admin/partenaires/{id}/supprimer', [AdminWebController::class, 'destroyPartner'])->name('admin.partners.destroy');
Route::get('/tableau-de-bord/admin/rapports', [AdminWebController::class, 'reports'])->name('admin.reports');
Route::get('/tableau-de-bord/admin/moderation', [AdminWebController::class, 'moderation'])->name('admin.moderation');
Route::get('/tableau-de-bord/admin/api-consommateurs', [AdminWebController::class, 'apiConsumers'])->name('admin.api-consumers');
Route::post('/tableau-de-bord/admin/api-consommateurs/{id}/statut', [AdminWebController::class, 'updateApiConsumerStatus'])->name('admin.api-consumers.update-status');
Route::get('/tableau-de-bord/admin/parametres', [AdminWebController::class, 'settings'])->name('admin.settings');
Route::post('/tableau-de-bord/admin/parametres', [AdminWebController::class, 'updateSettings'])->name('admin.settings.update');
Route::post('/tableau-de-bord/admin/parametres/twilio', [AdminWebController::class, 'saveTwilioSettings'])->name('admin.settings.twilio');
Route::post('/tableau-de-bord/admin/parametres/twilio/test', [AdminWebController::class, 'testTwilio'])->name('admin.settings.twilio.test');
Route::post('/tableau-de-bord/admin/signalements/{id}/traiter', [AdminWebController::class, 'resolveReport'])->name('admin.reports.resolve');
Route::post('/tableau-de-bord/admin/avis/{id}/supprimer', [AdminWebController::class, 'deleteReview'])->name('admin.reviews.destroy');
Route::get('/tableau-de-bord/admin/evenements', [AdminWebController::class, 'events'])->name('admin.events');
Route::post('/tableau-de-bord/admin/evenements', [AdminWebController::class, 'storeEvent'])->name('admin.events.store');
Route::post('/tableau-de-bord/admin/evenements/{id}', [AdminWebController::class, 'updateEvent'])->name('admin.events.update');
Route::post('/tableau-de-bord/admin/evenements/{id}/supprimer', [AdminWebController::class, 'destroyEvent'])->name('admin.events.destroy');

use App\Http\Controllers\EventWebController;

Route::get('/evenements', [EventWebController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventWebController::class, 'show'])->name('events.show');
Route::post('/evenements/{slug}/participer', [EventWebController::class, 'attend'])->name('events.attend');
Route::post('/evenements/{slug}/annuler', [EventWebController::class, 'cancelAttend'])->name('events.cancel-attend');
Route::post('/evenements/{slug}/exposer', [EventWebController::class, 'exhibit'])->name('events.exhibit');

use App\Http\Controllers\RegionalRepWebController;

Route::get('/tableau-de-bord/representant-regional', [RegionalRepWebController::class, 'dashboard'])->name('dashboard.regional-rep');

use App\Http\Controllers\MinistryWebController;

Route::get('/tableau-de-bord/ministere', [MinistryWebController::class, 'dashboard'])->name('dashboard.ministry');

use App\Http\Controllers\TechnicalReviewerWebController;

Route::get('/tableau-de-bord/technique', [TechnicalReviewerWebController::class, 'dashboard'])->name('dashboard.technical-reviewer');
Route::post('/tableau-de-bord/technique/verifications/{id}/approuver', [TechnicalReviewerWebController::class, 'approveVerification'])->name('technical.verifications.approve');
Route::post('/tableau-de-bord/technique/verifications/{id}/rejeter', [TechnicalReviewerWebController::class, 'rejectVerification'])->name('technical.verifications.reject');
Route::post('/tableau-de-bord/technique/certifications/{id}/approuver', [TechnicalReviewerWebController::class, 'approveCertification'])->name('technical.certifications.approve');
Route::post('/tableau-de-bord/technique/certifications/{id}/rejeter', [TechnicalReviewerWebController::class, 'rejectCertification'])->name('technical.certifications.reject');
Route::get('/tableau-de-bord/technique/historique', [TechnicalReviewerWebController::class, 'history'])->name('technical.history');
Route::get('/tableau-de-bord/admin/journal-audit', [AdminWebController::class, 'auditLog'])->name('admin.audit-log');

use App\Http\Controllers\SupportWebController;

Route::get('/tableau-de-bord/support', [SupportWebController::class, 'index'])->name('support.index');
Route::post('/tableau-de-bord/support', [SupportWebController::class, 'store'])->name('support.store');
Route::get('/tableau-de-bord/support/{id}', [SupportWebController::class, 'show'])->name('support.show');
Route::post('/tableau-de-bord/support/{id}/repondre', [SupportWebController::class, 'reply'])->name('support.reply');
Route::get('/tableau-de-bord/admin/support', [SupportWebController::class, 'adminIndex'])->name('admin.support');
Route::post('/tableau-de-bord/admin/support/{id}/fermer', [SupportWebController::class, 'close'])->name('admin.support.close');

use App\Http\Controllers\CmsWebController;

Route::get('/tableau-de-bord/admin/cms', [CmsWebController::class, 'index'])->name('admin.cms');
Route::post('/tableau-de-bord/admin/cms/pages', [CmsWebController::class, 'storePage'])->name('admin.cms.pages.store');
Route::post('/tableau-de-bord/admin/cms/pages/{id}', [CmsWebController::class, 'updatePage'])->name('admin.cms.pages.update');
Route::post('/tableau-de-bord/admin/cms/pages/{id}/supprimer', [CmsWebController::class, 'destroyPage'])->name('admin.cms.pages.destroy');
Route::post('/tableau-de-bord/admin/cms/faqs', [CmsWebController::class, 'storeFaq'])->name('admin.cms.faqs.store');
Route::post('/tableau-de-bord/admin/cms/faqs/{id}/supprimer', [CmsWebController::class, 'destroyFaq'])->name('admin.cms.faqs.destroy');

Route::get('/partenaires', function (Illuminate\Http\Request $request) {
    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $partners = \App\Modules\Cms\Models\Partner::active()->orderBy('tier')->orderBy('sort_order')->get();
    return view('pages.partners', compact('lang', 'partners'));
})->name('partners.index');

use App\Http\Controllers\NotificationWebController;

Route::get('/tableau-de-bord/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');

// ─────────────────────────────────────────────
// Language toggle
// ─────────────────────────────────────────────
Route::get('/lang/{locale}', function (string $locale, Request $request) {
    if (in_array($locale, ['en', 'fr'])) {
        session(['lang' => $locale]);
    }
    return redirect()->back()->withInput();
})->name('lang.switch');

// ─────────────────────────────────────────────
// Forgot / Reset password
// ─────────────────────────────────────────────
Route::get('/forgot-password', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : (in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr');
    return view('auth.forgot-password', compact('lang'));
})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');

    $request->validate(['email' => ['required', 'email']]);
    $email = strtolower(trim($request->input('email')));

    $user = DB::table('users')->where('email', $email)->whereNull('deleted_at')->first();

    // Always show the same message to prevent email enumeration
    $message = 'If an account with that email exists, a reset link has been sent.';

    if ($user) {
        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $plainToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password/' . $plainToken . '?email=' . urlencode($email));

        // Send email (goes to log when MAIL_MAILER=log)
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Hello {$user->first_name},\n\nClick the link below to reset your Galerie virtuelle de l'artisanat du Cameroun password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes. If you didn't request a reset, ignore this email.\n\n— Galerie virtuelle de l'artisanat du Cameroun",
                function ($mail) use ($email, $user) {
                    $mail->to($email)->subject('Reset your Galerie virtuelle de l\'artisanat du Cameroun password');
                }
            );
        } catch (\Exception $e) {
            // Mail failure is non-fatal; link is still logged
        }

        // In local dev: surface the reset URL so it can be tested without email
        if (app()->environment('local')) {
            return back()->with('status', $message)->with('dev_reset_url', $resetUrl);
        }
    }

    return back()->with('status', $message);
})->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    if (session('siac_user')) return redirect('/tableau-de-bord');

    $email = $request->query('email', '');
    $row   = DB::table('password_reset_tokens')->where('email', strtolower($email))->first();

    $tokenValid = $row
        && Hash::check($token, $row->token)
        && now()->diffInMinutes($row->created_at) <= 60;

    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : (in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr');

    return view('auth.reset-password', compact('token', 'email', 'tokenValid', 'lang'));
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $data = $request->validate([
        'token'                 => ['required'],
        'email'                 => ['required', 'email'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $email = strtolower(trim($data['email']));
    $row   = DB::table('password_reset_tokens')->where('email', $email)->first();

    if (!$row || !Hash::check($data['token'], $row->token) || now()->diffInMinutes($row->created_at) > 60) {
        return back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
    }

    $user = DB::table('users')->where('email', $email)->whereNull('deleted_at')->first();
    if (!$user) {
        return back()->withErrors(['email' => 'No account found with that email.']);
    }

    DB::table('users')->where('id', $user->id)->update([
        'password'   => Hash::make($data['password']),
        'updated_at' => now(),
    ]);

    DB::table('password_reset_tokens')->where('email', $email)->delete();

    return redirect('/login')->with('success', 'Password reset successfully. You can now log in.');
})->name('password.update');

// ─────────────────────────────────────────────
// Login
// ─────────────────────────────────────────────
Route::get('/login', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.login', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('login');

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    $email      = strtolower(trim($data['email']));
    $limiterKey = 'login:' . sha1($email . '|' . $request->ip());

    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        return back()->withErrors(['email' => $request->lang === 'en'
            ? "Too many attempts. Try again in {$seconds}s."
            : "Trop de tentatives. Réessayez dans {$seconds}s."])->withInput();
    }

    $user = DB::table('users')
        ->whereNull('deleted_at')
        ->where('email', $email)
        ->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);
        return back()->withErrors(['email' => $request->lang === 'en' ? 'Email or password is incorrect.' : 'Email ou mot de passe incorrect.'])->withInput();
    }

    if (isset($user->status) && $user->status === 'suspended') {
        return back()->withErrors(['email' => 'Compte suspendu.'])->withInput();
    }

    \Illuminate\Support\Facades\RateLimiter::clear($limiterKey);

    $next = $request->get('next', '/tableau-de-bord');

    // Second factor required? Password alone no longer grants a session.
    $hasTotp    = $user->two_factor_confirmed_at && $user->two_factor_secret;
    $hasChannel = (bool) $user->two_factor_channel;
    if ($hasTotp || $hasChannel) {
        session(['2fa_pending' => ['user_id' => $user->id, 'next' => $next]]);
        return redirect()->route('login.challenge');
    }

    establishSiacSession($user, $request);

    return redirect($next);
})->name('login.post');

// ─────────────────────────────────────────────
// Two-factor challenge (after password, before session)
// ─────────────────────────────────────────────
Route::get('/login/verification', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    return view('auth.two-factor-challenge', [
        'lang'       => $lang,
        'hasTotp'    => (bool) ($user->two_factor_confirmed_at && $user->two_factor_secret),
        'channel'    => $user->two_factor_channel,
        'maskedDest' => $user->two_factor_channel === 'email'
            ? preg_replace('/(?<=.).(?=[^@]*@)/', '•', $user->email)
            : ($user->phone ? substr($user->phone, 0, 4) . '••••' . substr($user->phone, -2) : null),
    ]);
})->name('login.challenge');

Route::post('/login/verification/send', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user || !$user->two_factor_channel) return redirect('/login');

    $lang       = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $identifier = $user->two_factor_channel === 'email' ? $user->email : (string) $user->phone;

    $sent = app(\App\Modules\Auth\Services\OtpService::class)
        ->send($identifier, 'login', $user->two_factor_channel, $user->id, $lang);

    return redirect()->route('login.challenge')->with(
        $sent ? 'success' : 'error',
        $sent
            ? ($lang === 'fr' ? 'Code envoyé.' : 'Code sent.')
            : ($lang === 'fr' ? 'Trop de codes demandés. Réessayez plus tard.' : 'Too many codes requested. Try again later.')
    );
})->name('login.challenge.send');

Route::post('/login/verification', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $data = $request->validate([
        'code'   => ['required', 'string', 'max:20'],
        'method' => ['required', 'in:totp,channel,recovery'],
    ]);

    $limiterKey = '2fa:' . sha1($user->id . '|' . $request->ip());
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        return redirect()->route('login.challenge')->withErrors(['code' => $lang === 'fr' ? "Trop de tentatives. Réessayez dans {$seconds}s." : "Too many attempts. Try again in {$seconds}s."]);
    }

    $ok = false;

    if ($data['method'] === 'totp' && $user->two_factor_secret && $user->two_factor_confirmed_at) {
        $secret = \Illuminate\Support\Facades\Crypt::decryptString($user->two_factor_secret);
        $ok = app(\App\Modules\Auth\Services\TotpService::class)->verify($secret, $data['code']);
    } elseif ($data['method'] === 'channel' && $user->two_factor_channel) {
        $identifier = $user->two_factor_channel === 'email' ? $user->email : (string) $user->phone;
        $ok = app(\App\Modules\Auth\Services\OtpService::class)->verify($identifier, $data['code'], 'login');
    } elseif ($data['method'] === 'recovery' && $user->two_factor_recovery_codes) {
        try {
            $hashes = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
            $needle = hash('sha256', strtoupper(trim($data['code'])));
            $idx = array_search($needle, $hashes, true);
            if ($idx !== false) {
                unset($hashes[$idx]); // recovery codes are single-use
                DB::table('users')->where('id', $user->id)->update([
                    'two_factor_recovery_codes' => \Illuminate\Support\Facades\Crypt::encryptString(json_encode(array_values($hashes))),
                    'updated_at'                => now(),
                ]);
                $ok = true;
            }
        } catch (\Throwable $e) {
        }
    }

    if (!$ok) {
        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);
        // Not back(): the previous URL may be a blocked page, which would dump
        // the user on /login even though the challenge is still pending.
        return redirect()->route('login.challenge')->withErrors(['code' => $lang === 'fr' ? 'Code invalide.' : 'Invalid code.']);
    }

    \Illuminate\Support\Facades\RateLimiter::clear($limiterKey);
    session()->forget('2fa_pending');
    establishSiacSession($user, $request);

    return redirect($pending['next'] ?? '/tableau-de-bord');
})->name('login.challenge.verify');

// ─────────────────────────────────────────────
// Register (legacy — kept for backward compat)
// ─────────────────────────────────────────────
Route::get('/register', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.register', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'first_name'            => ['required', 'string', 'max:50'],
        'last_name'             => ['required', 'string', 'max:50'],
        'email'                 => ['required', 'email', 'max:255'],
        'phone'                 => ['nullable', 'string', 'max:30'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $email = strtolower(trim($data['email']));
    $name  = trim($data['first_name'] . ' ' . $data['last_name']);

    if (DB::table('users')->where('email', $email)->exists()) {
        return back()->withErrors(['email' => 'An account with this email already exists. Try logging in instead.'])->withInput();
    }

    $userId = Str::uuid()->toString();
    try {
        DB::table('users')->insert([
            'id'                  => $userId,
            'name'                => $name,
            'email'               => $email,
            'phone'               => $data['phone'] ?? null,
            'password'            => Hash::make($data['password']),
            'status'              => 'active',
            'language_preference' => 'fr',
            'is_email_verified'   => 0,
            'is_phone_verified'   => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        // Race condition (e.g. double form submit) — the email was taken between
        // the check above and this insert. Fail gracefully instead of a 500.
        return back()->withErrors(['email' => 'An account with this email already exists. Try logging in instead.'])->withInput();
    }

    session(['siac_user' => [
        'id'       => $userId,
        'name'     => $name,
        'email'    => $email,
        'role'     => null,
        'is_admin' => false,
    ]]);

    return redirect('/tableau-de-bord');
})->name('register.post');

// ─────────────────────────────────────────────
// SIAC — Inscription (Register)
// ─────────────────────────────────────────────
Route::get('/inscription', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.register', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('inscription');

Route::post('/inscription', function (Request $request) {
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $data = $request->validate([
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'email', 'max:255'],
        'phone'                 => ['nullable', 'string', 'max:30'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
        'role'                  => ['nullable', 'in:buyer,business_owner'],
    ]);

    $email = strtolower(trim($data['email']));

    if (DB::table('users')->where('email', $email)->exists()) {
        return back()->withErrors(['email' => $lang === 'en' ? 'An account with this email already exists.' : 'Un compte avec cet email existe déjà.'])->withInput();
    }

    $userId = Str::uuid()->toString();
    DB::table('users')->insert([
        'id'                  => $userId,
        'name'                => $data['name'],
        'email'               => $email,
        'phone'               => $data['phone'] ?? null,
        'password'            => Hash::make($data['password']),
        'status'              => 'active',
        'language_preference' => $lang,
        'is_email_verified'   => 0,
        'is_phone_verified'   => 0,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    // Assign Spatie role if business_owner
    $role = $data['role'] ?? 'buyer';
    if ($role === 'business_owner') {
        $roleRecord = DB::table('roles')->where('name', 'business_owner')->where('guard_name', 'sanctum')->first();
        if ($roleRecord) {
            DB::table('model_has_roles')->insert([
                'role_id'    => $roleRecord->id,
                'model_type' => 'App\\Modules\\Auth\\Models\\User',
                'model_id'   => $userId,
            ]);
        }
    }

    session(['siac_user' => [
        'id'       => $userId,
        'name'     => $data['name'],
        'email'    => $email,
        'role'     => $role === 'business_owner' ? 'business_owner' : null,
        'is_admin' => false,
    ]]);

    return redirect('/tableau-de-bord');
})->name('inscription.post');

// ─────────────────────────────────────────────
// SIAC — Dashboards
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?lang=' . $request->cookie('lang', 'fr'));

    $role = $siacUser['role'] ?? null;
    if (in_array($role, ['super_admin', 'admin', 'moderator'])) return redirect('/tableau-de-bord/admin');
    if ($role === 'ministry') return redirect('/tableau-de-bord/ministere');
    if ($role === 'technical_reviewer') return redirect('/tableau-de-bord/technique');
    if ($role === 'regional_rep') return redirect('/tableau-de-bord/representant-regional');
    if ($role === 'business_owner') return redirect('/tableau-de-bord/entrepreneur');
    return redirect('/tableau-de-bord/acheteur');
})->name('dashboard.siac');

Route::get('/tableau-de-bord/admin', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $stats = [
        'businesses' => [
            'total'     => DB::table('businesses')->whereNull('deleted_at')->count(),
            'published' => DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count(),
            'verified'  => DB::table('businesses')->whereIn('verification_tier', ['verified', 'certified'])->whereNull('deleted_at')->count(),
        ],
        'products' => [
            'total'     => DB::table('products')->whereNull('deleted_at')->count(),
            'published' => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
        ],
        'users' => [
            'total'           => DB::table('users')->whereNull('deleted_at')->count(),
            'business_owners' => DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'business_owner')->count(),
        ],
    ];

    $recentBusinesses = DB::table('businesses')
        ->whereNull('deleted_at')
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    $pendingVerifications = DB::table('verification_applications')
        ->where('status', 'pending')
        ->count();

    return view('pages.dashboard.admin', compact('lang', 'siacUser', 'stats', 'recentBusinesses', 'pendingVerifications'));
})->name('dashboard.admin');

Route::get('/tableau-de-bord/entrepreneur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $business = DB::table('businesses')
        ->whereNull('deleted_at')
        ->where('user_id', $siacUser['id'])
        ->first();

    $productCount = $business
        ? DB::table('products')->where('business_id', $business->id)->whereNull('deleted_at')->count()
        : 0;

    $products = $business
        ? DB::table('products')->where('business_id', $business->id)->whereNull('deleted_at')->orderByDesc('created_at')->limit(6)->get()
        : collect();

    $messageCount = DB::table('conversations')
        ->where('buyer_id', $siacUser['id'])
        ->orWhere('business_id', $business->id ?? 0)
        ->count();

    $latestVerification = $business
        ? DB::table('verification_applications')->where('business_id', $business->id)->orderByDesc('created_at')->first()
        : null;

    $eventParticipations = $business
        ? DB::table('event_exhibitors')
            ->join('events', 'events.id', '=', 'event_exhibitors.event_id')
            ->where('event_exhibitors.business_id', $business->id)
            ->orderByDesc('events.starts_at')
            ->select('events.name_fr', 'events.name_en', 'events.starts_at', 'event_exhibitors.status')
            ->get()
        : collect();

    return view('pages.dashboard.entrepreneur', compact('lang', 'siacUser', 'business', 'productCount', 'products', 'messageCount', 'latestVerification', 'eventParticipations'));
})->name('dashboard.entrepreneur');

Route::get('/tableau-de-bord/acheteur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $savedBusinesses = DB::table('saved_businesses')
        ->join('businesses', 'businesses.id', '=', 'saved_businesses.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->where('saved_businesses.user_id', $siacUser['id'])
        ->whereNull('businesses.deleted_at')
        ->select(
            'saved_businesses.id',
            'saved_businesses.created_at',
            'businesses.id as business_id',
            'businesses.name_fr',
            'businesses.slug',
            'businesses.logo',
            'businesses.verification_tier',
            'industries.name_fr as industry_name'
        )
        ->orderByDesc('saved_businesses.created_at')
        ->limit(6)
        ->get();

    $conversations = DB::table('conversations')
        ->where('buyer_id', $siacUser['id'])
        ->orderByDesc('updated_at')
        ->limit(5)
        ->get();

    $stats = [
        'businesses' => DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count(),
        'products'   => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
        'industries' => DB::table('industries')->where('is_active', 1)->count(),
    ];

    return view('pages.dashboard.buyer', compact('lang', 'siacUser', 'savedBusinesses', 'conversations', 'stats'));
})->name('dashboard.buyer');

// ─────────────────────────────────────────────
// Saved items (buyer)
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/sauvegardes', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $savedProductRows = DB::table('saved_products')
        ->where('user_id', $siacUser['id'])
        ->orderByDesc('created_at')
        ->get();

    $savedProducts = \App\Modules\Products\Models\Product::with('images')
        ->whereIn('id', $savedProductRows->pluck('product_id'))
        ->whereNull('deleted_at')
        ->get()
        ->sortBy(fn ($p) => $savedProductRows->search(fn ($r) => $r->product_id === $p->id))
        ->values();

    $savedBusinesses = DB::table('saved_businesses')
        ->join('businesses', 'businesses.id', '=', 'saved_businesses.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->where('saved_businesses.user_id', $siacUser['id'])
        ->whereNull('businesses.deleted_at')
        ->select(
            'businesses.id as business_id',
            'businesses.name_fr',
            'businesses.name_en',
            'businesses.slug',
            'businesses.logo',
            'businesses.verification_tier',
            'industries.name_fr as industry_fr',
            'industries.name_en as industry_en',
            'saved_businesses.created_at as saved_at'
        )
        ->orderByDesc('saved_businesses.created_at')
        ->get();

    return view('pages.dashboard.saved', compact('lang', 'siacUser', 'savedProducts', 'savedBusinesses'));
})->name('saved.index');

// ─────────────────────────────────────────────
// Notification preferences
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/notifications/preferences', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    // channel × category matrix; anything not stored yet defaults to enabled (matches the column default).
    $stored = DB::table('notification_preferences')
        ->where('user_id', $siacUser['id'])
        ->get()
        ->keyBy(fn ($r) => $r->category . '.' . $r->channel);

    return view('pages.dashboard.notification-settings', compact('lang', 'siacUser', 'stored'));
})->name('notifications.settings');

Route::post('/tableau-de-bord/notifications/preferences', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $categories = ['messages', 'verification', 'business', 'events'];
    $channels   = ['email', 'sms', 'push'];
    $enabled    = (array) $request->input('prefs', []); // prefs[category][channel] = 1 when checked

    $rows = [];
    foreach ($categories as $category) {
        foreach ($channels as $channel) {
            $rows[] = [
                'user_id'    => $siacUser['id'],
                'category'   => $category,
                'channel'    => $channel,
                'is_enabled' => isset($enabled[$category][$channel]) ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }
    DB::table('notification_preferences')->upsert($rows, ['user_id', 'channel', 'category'], ['is_enabled', 'updated_at']);

    return redirect()->route('notifications.settings')
        ->with('success', $lang === 'fr' ? 'Préférences enregistrées.' : 'Preferences saved.');
})->name('notifications.settings.save');

// ─────────────────────────────────────────────
// Account security (2FA, OTP channels, passkeys)
// ─────────────────────────────────────────────
use App\Http\Controllers\SecurityWebController;

Route::get('/tableau-de-bord/securite', [SecurityWebController::class, 'show'])->name('security.show');
Route::post('/tableau-de-bord/securite/totp/activer', [SecurityWebController::class, 'startTotp'])->name('security.totp.start');
Route::post('/tableau-de-bord/securite/totp/confirmer', [SecurityWebController::class, 'confirmTotp'])->name('security.totp.confirm');
Route::post('/tableau-de-bord/securite/totp/desactiver', [SecurityWebController::class, 'disableTotp'])->name('security.totp.disable');
Route::post('/tableau-de-bord/securite/recuperation/regenerer', [SecurityWebController::class, 'regenerateRecoveryCodes'])->name('security.recovery.regenerate');
Route::post('/tableau-de-bord/securite/canal/activer', [SecurityWebController::class, 'startChannel'])->name('security.channel.start');
Route::post('/tableau-de-bord/securite/canal/confirmer', [SecurityWebController::class, 'confirmChannel'])->name('security.channel.confirm');
Route::post('/tableau-de-bord/securite/canal/desactiver', [SecurityWebController::class, 'disableChannel'])->name('security.channel.disable');
Route::post('/tableau-de-bord/securite/passkeys/options', [SecurityWebController::class, 'passkeyRegisterOptions'])->name('security.passkeys.options');
Route::post('/tableau-de-bord/securite/passkeys', [SecurityWebController::class, 'passkeyRegister'])->name('security.passkeys.register');
Route::post('/tableau-de-bord/securite/passkeys/{id}/supprimer', [SecurityWebController::class, 'passkeyDelete'])->name('security.passkeys.delete');

// Passkey login (guest)
Route::post('/webauthn/login/options', [SecurityWebController::class, 'passkeyLoginOptions'])->name('webauthn.login.options');
Route::post('/webauthn/login', [SecurityWebController::class, 'passkeyLogin'])->name('webauthn.login');

// ─────────────────────────────────────────────
// Profile / settings (all roles)
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/profil', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $user = DB::table('users')->where('id', $siacUser['id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    return view('pages.dashboard.profile', compact('lang', 'siacUser', 'user'));
})->name('profile.show');

Route::post('/tableau-de-bord/profil', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $data = $request->validate([
        'name'                => ['required', 'string', 'max:255'],
        'language_preference' => ['required', 'in:fr,en'],
    ]);

    DB::table('users')->where('id', $siacUser['id'])->update([
        'name'                => $data['name'],
        'language_preference' => $data['language_preference'],
        'updated_at'          => now(),
    ]);

    $siacUser['name'] = $data['name'];
    session(['siac_user' => $siacUser]);

    return redirect()->route('profile.show')
        ->with('success', $lang === 'fr' ? 'Profil mis à jour.' : 'Profile updated.')
        ->cookie('lang', $data['language_preference'], 60 * 24 * 30);
})->name('profile.update');

Route::post('/tableau-de-bord/profil/mot-de-passe', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    $data = $request->validate([
        'current_password'      => ['required'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $user = DB::table('users')->where('id', $siacUser['id'])->whereNull('deleted_at')->first();
    if (!$user || !Hash::check($data['current_password'], $user->password)) {
        return back()->withErrors(['current_password' => $lang === 'fr' ? 'Le mot de passe actuel est incorrect.' : 'Current password is incorrect.']);
    }

    DB::table('users')->where('id', $siacUser['id'])->update([
        'password'   => Hash::make($data['password']),
        'updated_at' => now(),
    ]);

    return redirect()->route('profile.show')
        ->with('success', $lang === 'fr' ? 'Mot de passe modifié.' : 'Password changed.');
})->name('profile.password');

// ─────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────
Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');

// ─────────────────────────────────────────────
// Static Pages
// ─────────────────────────────────────────────
Route::get('/about', function (Request $request) {
    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    return view('about', compact('lang'));
})->name('about');
Route::get('/terms', function (Request $request) {
    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    return view('terms', compact('lang'));
})->name('terms');
Route::get('/privacy', function (Request $request) {
    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';
    return view('privacy', compact('lang'));
})->name('privacy');

// ─────────────────────────────────────────────
// Developer / API Keys
// ─────────────────────────────────────────────
// A web user's API consumer record is matched by email (api_consumers has no user_id column).
function developerConsumer(object $user, bool $createIfMissing = false): ?object
{
    $consumer = DB::table('api_consumers')->where('email', $user->email)->first();
    if (!$consumer && $createIfMissing) {
        $id = DB::table('api_consumers')->insertGetId([
            'uuid'        => Str::uuid()->toString(),
            'name'        => $user->name,
            'email'       => $user->email,
            'status'      => 'approved',
            'approved_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $consumer = DB::table('api_consumers')->where('id', $id)->first();
    }
    return $consumer;
}

Route::get('/developer', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $consumer = developerConsumer(webUser());
    $keys     = $consumer
        ? DB::table('api_keys')->where('consumer_id', $consumer->id)->orderBy('created_at', 'desc')->get()
        : collect();
    $keyCount = $keys->where('is_active', 1)->count();
    return view('developer', compact('keys', 'keyCount'));
})->name('developer');

Route::post('/developer/keys', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $data     = $request->validate(['name' => 'required|string|max:60']);
    $consumer = developerConsumer(webUser(), createIfMissing: true);
    $plain    = 'siac_' . Str::random(40);
    DB::table('api_keys')->insert([
        'consumer_id'           => $consumer->id,
        'name'                  => $data['name'],
        'key_hash'              => hash('sha256', $plain),
        'key_prefix'            => substr($plain, 0, 8),
        'rate_limit_per_minute' => 60,
        'is_active'             => 1,
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);
    return back()->with('success', 'API key created: ' . $plain . ' — copy it now, it will not be shown again.');
})->name('developer.keys.create');

Route::post('/developer/keys/{id}/revoke', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $consumer = developerConsumer(webUser());
    if ($consumer) {
        DB::table('api_keys')->where('id', $id)->where('consumer_id', $consumer->id)->update(['is_active' => 0, 'updated_at' => now()]);
    }
    return back()->with('success', 'API key revoked.');
})->name('developer.keys.revoke');
