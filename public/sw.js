/* ============================================================
   Artisan Hub 237 — service worker.

   Deliberately conservative: this ships to Namecheap shared hosting by FTP
   with no build step, and a service worker that caches the wrong thing is
   the hardest bug to ship a fix for — the broken worker serves the stale
   copy of everything, including the fix. So:

     • HTML navigations ....... network-first. This is a marketplace; a stale
                                price, stock line or artisan status misleads.
                                Offline fallback: the cached /hors-ligne page.
     • Same-origin static ..... cache-first (/vendor/*, /images/*, fonts).
                                Immutable-by-convention files, worst case one
                                version behind, evicted on version bump.
     • NEVER touched .......... anything non-GET; /tableau-de-bord*, /login,
                                /api/*, session/CSRF-dependent pages; and the
                                certificate/document routes — a revoked
                                certificate served from cache is a false
                                attestation, so documents are network-only
                                with NO offline fallback, not even the
                                branded offline page pretending to be one.

   Update path: bump CACHE_VERSION, upload this file. The new worker installs
   in the background, `skipWaiting()` promotes it immediately, activate
   deletes every cache that is not CACHE_NAME, and `clients.claim()` takes
   over open pages — so a shipped fix reaches users on their next
   navigation, never trapped behind the old worker's cache.

   No push, no background sync: this host has no infrastructure for them.
   ============================================================ */

'use strict';

var CACHE_VERSION = 'v1';
var CACHE_NAME = 'artisanhub237-' + CACHE_VERSION;
var OFFLINE_URL = '/hors-ligne';

/* Primed at install so the offline experience needs no luck: the fallback
   page and the icons it (and the manifest) reference. */
var PRECACHE = [
    OFFLINE_URL,
    '/manifest.json',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png'
];

/* ── What the worker must never serve from cache ─────────────────────────── */

/* Documents / attestations: always the live answer or nothing. A cached
   "valid" on a since-revoked certificate is a forgery we produced. */
function isDocumentRequest(path) {
    return path.indexOf('/certificat') === 0          /* /certificat/*, /certificat-*, … */
        || path.indexOf('/verification-certificat') === 0
        || path.indexOf('/verifier') === 0
        || path.indexOf('/.well-known/') === 0;
}

/* Session-, auth- or CSRF-dependent surfaces: caching these serves one
   member's page to another, or a dead CSRF token to everyone. */
function isSessionRequest(path) {
    return path.indexOf('/tableau-de-bord') === 0
        || path.indexOf('/login') === 0
        || path.indexOf('/logout') === 0
        || path.indexOf('/register') === 0
        || path.indexOf('/inscription') === 0
        || path.indexOf('/mot-de-passe') === 0
        || path.indexOf('/api/') === 0
        || path.indexOf('/sanctum/') === 0;
}

/* Static assets safe to serve cache-first. */
function isStaticAsset(path) {
    return path.indexOf('/vendor/') === 0
        || path.indexOf('/images/') === 0
        || /\.(css|js|woff2?|ttf|otf|png|jpe?g|gif|webp|svg|ico)$/.test(path);
}

/* ── Lifecycle ───────────────────────────────────────────────────────────── */

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function (cache) { return cache.addAll(PRECACHE); })
            /* A new version must not sit "waiting" behind the old one:
               waiting is exactly where a broken worker traps its own fix. */
            .then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys()
            .then(function (names) {
                return Promise.all(names.map(function (name) {
                    /* Only our own caches, only stale versions. */
                    if (name.indexOf('artisanhub237-') === 0 && name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                    return Promise.resolve();
                }));
            })
            /* Take over already-open tabs so the next navigation is served by
               this version, not by a page still owned by the old worker. */
            .then(function () { return self.clients.claim(); })
    );
});

/* ── Fetch ───────────────────────────────────────────────────────────────── */

self.addEventListener('fetch', function (event) {
    var request = event.request;

    /* Never touch anything that can mutate state. */
    if (request.method !== 'GET') return;

    var url = new URL(request.url);

    /* Cross-origin (CDNs, S3 media): the browser handles it. */
    if (url.origin !== self.location.origin) return;

    /* Documents and session surfaces: straight to the network, no cache
       write, no cache fallback. Falling through (no respondWith) gives the
       browser's default behaviour, which is exactly that. */
    if (isDocumentRequest(url.pathname) || isSessionRequest(url.pathname)) return;

    /* HTML navigations: network-first, offline page as last resort. */
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(function (response) {
                    /* Keep the offline page itself fresh as a side effect. */
                    if (response.ok && url.pathname === OFFLINE_URL) {
                        var copy = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(OFFLINE_URL, copy); });
                    }
                    return response;
                })
                .catch(function () {
                    return caches.match(OFFLINE_URL).then(function (cached) {
                        return cached || new Response(
                            'Vous êtes hors ligne. / You are offline.',
                            { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } }
                        );
                    });
                })
        );
        return;
    }

    /* Same-origin static assets: cache-first inside the versioned cache. */
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                if (cached) return cached;
                return fetch(request).then(function (response) {
                    if (response.ok && (response.type === 'basic' || response.type === 'default')) {
                        var copy = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(request, copy); });
                    }
                    return response;
                });
            })
        );
    }

    /* Everything else: untouched. */
});
