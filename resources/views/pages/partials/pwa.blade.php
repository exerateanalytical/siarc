{{-- ============================================================
     PWA head wiring. One file, included once per page by
     `pages/partials/ui-kit.blade.php` — the same route the dark-mode
     foundation ships through, so every current and future page gets it
     without hand-editing heads.

     What it declares:
       • the web app manifest (public/manifest.json — plain .json on purpose:
         shared hosting serves it as application/json with no MIME surprises)
       • theme-color for both schemes (brand-deep light / dark from
         docs/DARK-MODE-CONTRACT.md); the first matching meta wins in Chrome,
         and this partial renders earlier in <head> than the legacy unscoped
         meta in `favicon.blade.php`, so these take effect
       • the 180px apple-touch-icon for iOS home screens
       • service-worker registration, deferred until `load` so it never
         competes with first paint.

     Update policy (documented choice): the worker in public/sw.js calls
     skipWaiting() at install and clients.claim() at activate, and the
     registration below asks for `registration.update()` on every page load.
     Net effect: ship a new sw.js by FTP, and the next navigation any user
     makes is served by the new worker with the old version's caches deleted.
     We accept the (harmless here) mixed-version window in exchange for a
     fix never being trapped behind the broken worker it fixes — on a host
     with no SSH, that guarantee matters more than cache continuity.

     CSP: everything here is same-origin ('self') or inline; the site's CSP
     sends script-src 'self' 'unsafe-inline' and no worker-src/child-src, so
     the worker falls back to script-src 'self' and is allowed. Verified
     against app/Http/Middleware/SecurityHeaders.php.
     ============================================================ --}}
@once
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#02411D">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0C3B1E">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/apple-touch-icon.png') }}">
<script>
/* Idempotent: registering an already-registered scope is a no-op that still
   returns the registration, which we use to check for a shipped update. */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(function (reg) {
                /* Look for a newer sw.js on every full page load, not only on
                   the browser's own 24h schedule — a shipped fix should land
                   on the next visit, not tomorrow. */
                if (reg.update) reg.update();
            })
            .catch(function () { /* Private mode or storage denied: the site works without it. */ });
    });
}
</script>
@endonce
