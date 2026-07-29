@php
    // The service worker serves ONE cached copy of this page to everyone,
    // whatever language they browsed in — so the page carries both languages
    // itself, ordered by the language of the visit that cached it. $lang
    // only decides which one leads.
    $isFr = $lang === 'fr';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $isFr ? 'Hors ligne — Artisan Hub 237' : 'Offline — Artisan Hub 237' }}</title>

    {{-- Deliberately no Tailwind, no vendor JS, no webfont: this page is the
         service worker's offline fallback, so it must render from its own
         bytes alone. The ui-kit and theme partials below are inline <style>/
         <script>, which is exactly why they are safe to lean on here. --}}
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', system-ui, sans-serif;
            background: rgb(var(--t-bg));
            color: rgb(var(--t-ink));
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            box-sizing: border-box;
        }
        /* box-sizing explicitly: with no Tailwind there is no preflight, so
           without it the 100% width ADDS the 56px of padding and the card is
           wider than a 360px phone. */
        .offline-card { max-width: 460px; width: 100%; box-sizing: border-box; text-align: center; padding: 36px 28px; }
        .offline-icon {
            width: 64px; height: 64px; margin: 0 auto 18px;
            border-radius: 999px; display: grid; place-items: center;
            background: var(--ui-green-tint); color: var(--ui-green);
        }
        .offline-icon svg { width: 30px; height: 30px; }
        .offline-title { font-size: 20px; font-weight: 700; color: var(--ui-ink); margin: 0 0 6px; }
        .offline-sub   { font-size: 13.5px; font-weight: 600; color: var(--ui-muted); margin: 0 0 16px; }
        .offline-body  { font-size: 13px; line-height: 1.6; color: var(--ui-body); margin: 0 0 8px; }
        .offline-body--alt { color: var(--ui-muted); margin-bottom: 22px; }
        .offline-brand {
            margin-top: 26px; font-size: 11.5px; font-weight: 600;
            letter-spacing: .08em; text-transform: uppercase; color: var(--ui-label);
        }
        /* Phone type ramp, docs/RESPONSIVE-CONTRACT.md section 2. These are
           plain classes, so the kit's utility remap cannot reach them. */
        @media (max-width: 767.98px) {
            .offline-sub   { font-size: 14px; }
            .offline-body  { font-size: 15px; }
            .offline-brand { font-size: 12px; }
        }
    </style>
</head>
<body>
    <main class="ui-card offline-card">
        <div class="offline-icon" aria-hidden="true">
            {{-- wifi-off, drawn inline so it exists with an empty cache --}}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="2" y1="2" x2="22" y2="22"/>
                <path d="M8.5 16.5a5 5 0 0 1 7 0"/>
                <path d="M2 8.82a15 15 0 0 1 4.17-2.65"/>
                <path d="M10.66 5c4.01-.36 8.14.9 11.34 3.76"/>
                <path d="M16.85 11.25a10 10 0 0 1 2.22 1.68"/>
                <path d="M5 13a10 10 0 0 1 5.24-2.76"/>
                <line x1="12" y1="20" x2="12.01" y2="20"/>
            </svg>
        </div>

        @if ($isFr)
            <h1 class="offline-title">Vous êtes hors ligne</h1>
            <p class="offline-sub">You are offline</p>
            <p class="offline-body">
                Impossible de joindre Artisan&nbsp;Hub&nbsp;237 pour le moment.
                Vérifiez votre connexion internet, puis réessayez.
            </p>
            <p class="offline-body offline-body--alt">
                We cannot reach Artisan&nbsp;Hub&nbsp;237 right now.
                Check your internet connection, then try again.
            </p>
            <button type="button" class="ui-btn ui-btn-primary" onclick="window.location.reload()">
                Réessayer <span aria-hidden="true">·</span> Retry
            </button>
        @else
            <h1 class="offline-title">You are offline</h1>
            <p class="offline-sub">Vous êtes hors ligne</p>
            <p class="offline-body">
                We cannot reach Artisan&nbsp;Hub&nbsp;237 right now.
                Check your internet connection, then try again.
            </p>
            <p class="offline-body offline-body--alt">
                Impossible de joindre Artisan&nbsp;Hub&nbsp;237 pour le moment.
                Vérifiez votre connexion internet, puis réessayez.
            </p>
            <button type="button" class="ui-btn ui-btn-primary" onclick="window.location.reload()">
                Retry <span aria-hidden="true">·</span> Réessayer
            </button>
        @endif

        <p class="offline-brand">Artisan Hub 237</p>
    </main>
</body>
</html>
