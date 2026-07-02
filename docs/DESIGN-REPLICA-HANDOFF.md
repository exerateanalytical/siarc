# Design Replica Handoff — Galerie Virtuelle Nationale de l'Artisanat du Cameroun

Status as of 2026-07-02. This documents the pixel-replica work done so far so a fresh
Claude Code session (or any developer) can continue without prior context.

## What is done

| Page | Design source (repo root) | View | Route | Commit |
|------|--------------------------|------|-------|--------|
| Landing | `Official landing page.png` (1024×1536) | `resources/views/pages/home.blade.php` | `/` (`home`) | `90c3735`, `1fbf5b8` |
| About | `about page.png` (884×1779) | `resources/views/about.blade.php` | `/about` (`about`) | `3b446ba` |
| Auth (login + signup) | `auth page.png` (1536×1024, both mockups side by side) | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, shared `resources/views/auth/partials/replica-bottom.blade.php` | `/login` (`login`), `/inscription` (`inscription`) | see git log |

Auth-page notes: the design canvas holds two page mockups (login left 784px-wide,
signup right 713px-wide) plus a shared full-width "Pourquoi rejoindre" band and footer;
the band + footer + mobile bottom nav live in the shared partial. All form logic was
preserved: POST `/login` (email+password+hidden `next` passthrough), POST `/inscription`
(name/email/phone/password/role) — the signup 3-step wizard is purely client-side inside
one `<form>`, and a server validation error reopens step 2. Passkey login and the demo
accounts were kept (demo accounts as a collapsed `<details>`; passkey as an extra button
under the Google/Facebook buttons, which are visual-only and show "Bientôt disponible").
The login card is absolutely positioned over the baked-in card in `auth-hero.png`.
New assets: `public/images/landing/auth-*.png` (hero, baskets, footer-band, footer-map,
footer-motif, band-motif-left/right). The web login accepts email only (`type="email"`
is correct — the "phone" login test targets the separate API endpoint `/api/v1/auth/login`).

## What is pending — build in this order

Design files already dropped in the repo root, not yet built. The user has set the order:

1. **`categories page.png` — NEXT.** Categories/sectors listing (nearest existing page:
   `resources/views/pages/industries/index.blade.php`, route `industries.index`
   at `/galerie/secteurs`). The PNG is still untracked in the repo root — commit it
   with the implementation.
2. **`contact page.png`** — no dedicated contact route exists yet; nearest existing
   targets are the `support.*` routes and `route('about')`. A new GET route/view will
   likely be needed; keep any form submission wired to an existing endpoint
   (e.g. `support.store`) rather than inventing a dead handler.

Also in the repo root, untracked: `default product images by ategory.png` [sic] —
purpose not yet scoped by the user; likely default product imagery per category for
the categories page. Ask before using.

## The replication process used (repeat for each new page)

1. Read the PNG with the Read tool; note pixel dimensions.
2. Zoom sections (System.Drawing crop + nearest-neighbor upscale into the scratchpad)
   to transcribe every text string and measure spacing at the PNG's native scale.
3. Sample flat-fill colors with `Bitmap.GetPixel` (9-point grids per element — single
   points often hit text/anti-aliasing).
4. Crop photo/pattern/logo assets from the PNG into `public/images/landing/`
   (naming: `about-*.png`, `partner-*.png`, `biz-*.png`, `event-*.png`, `auth-*.png`).
   - If baked-in text must be hidden, cover with a gradient fill and clone-patch
     from adjacent clean rows (see hero-bg.png history in `90c3735`).
   - When tiling a crop as a pattern, trim ~10px off the crop edges or the tile
     seam shows as a dark band (bug fixed in `3b446ba`).
5. Build the page as a **standalone blade view** (own `<head>`, header, footer —
   do NOT extend `layouts/app.blade.php`; other gallery pages still use that layout
   and must not be disturbed).
6. Verify with the preview server (`.claude/launch.json`, name `laravel`, port 8321;
   `laravel-alt` on 8322 exists for when another session holds 8321;
   PHP at `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`). The screenshot
   tool sometimes lags one action behind or times out — restart the preview if stuck.
   preview_click on a submit button may not fire the native submit — use
   `form.requestSubmit()` via preview_eval when testing form flows.
7. Run `php artisan test` (34 tests must stay green), then commit.

## Shared design system

- **Fonts** (all local, no CDN): Playfair Display (serif display), Poppins (UI sans),
  Inter (legacy layouts). Loaded via `public/vendor/fonts.css` + `public/vendor/fonts/*.woff2`.
- **Offline rule**: never reference a CDN. Tailwind Play runtime is at
  `public/vendor/tailwindcss.js`, lucide at `public/vendor/lucide.min.js`,
  qrcodejs at `public/vendor/qrcode.min.js`. CSP in
  `app/Http/Middleware/SecurityHeaders.php` still allows the old CDNs; local is 'self'.
- **Palette** (sampled from the designs):
  - Cream page bg `#F8F3ED` (landing) / `#F7F2EC` (about), card bg `#FBF9F3`/`#FDFBF7`, borders `#E7E1D4`
  - Deep greens: buttons `#164C28`, header button `#0F2D19`, about/stats/footer bands `#0E1D13`,
    landing mission strip `#0E261C`, landing footer `#0D0F0D`, event card gradient `#0A1F10`
  - Golds: bright text `#E5A82E` (landing) / `#D9A439` (about), mid `#C9942E`, border `#B0821A`,
    icon `#D79326`, CTA button fill `#E0A52F` with dark text `#4A3A0B`
  - Flag tricolor: green `#125527`, red `#C10913`, yellow `#EBAB1A` (about adds a gold star in the red segment)
  - Reds/accents: stat circle red `#A51717`, value-icon red `#B42025`; icon brown `#4A2E1E`; muted text `#8A857A`; footer sage `#A8B8AC`
- **Icons**: lucide via `data-lucide` (vendored file has amphora/handshake/etc.).
  Brand/social icons don't exist in lucide — inline SVG paths are duplicated in both
  pages (`$socialIcons` array: Facebook, Instagram, LinkedIn, YouTube, X).
- **Bilingual**: every string has FR/EN variants keyed off `$isFr`; `?lang=` +
  30-day cookie. `/about` was fixed in `3b446ba` to honor `?lang=` (terms/privacy
  routes still read the cookie only — same fix applies if those pages get rebuilt).
- **Fidelity convention**: pixel-match at the PNG's native width; content column is
  `max-w-[1280px]` centered, so wider screens just gain margin. Marketing numbers in
  the stats bands ("250+", "10 000+", "50 000+", "100+") are static from the designs;
  the regions count is live.

## Data seeded for the designs (admin-editable, not fixtures)

- **Events** (`events` table): `festival-national-du-textile` (15–18 Jul 2026, Bafoussam),
  `expo-artisanat-jeunesse` (5–8 Aug 2026, Douala), `semaine-nationale-du-bois`
  (22–27 Sep 2026, Ebolowa). Design showed 2025 dates; rolled forward so they display.
- **Partners** (`partners` table, sort_order 1–9): MINCOMMERCE, MINAC, UNESCO, ITC,
  CEPII Cameroun, OAPI, Banque Africaine de Développement, AFD, Union Européenne.
  Logo tiles are static crops mapped by `name_fr` in the home view (`$partnerTiles`);
  the DB `logo` column is empty. Pre-existing MINEPIA/OIDAC moved to sort_order 10/11.
- `FrontendController::home()` picks the spotlight event by slug prefix `siac%`,
  lists other upcoming events, and passes 9 partners + a regions count.

## Guardrails honored so far (keep honoring them)

- No existing route, dashboard, flow, or button was removed or renamed.
- `layouts/app.blade.php` / `layouts/dashboard.blade.php` changed only in their
  `<head>` (CDN → local vendor files).
- Landing/About sector & nav links map onto real routes (`businesses.index` with
  `?industry=`, `industries.index`, `events.index`, `partners.index`, `gallery.search`).
- Mobile bottom nav is replicated inside each standalone page so small screens keep it.
