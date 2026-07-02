# Design Replica Handoff — Galerie Virtuelle Nationale de l'Artisanat du Cameroun

Status as of 2026-07-02. This documents the pixel-replica work done so far so a fresh
Claude Code session (or any developer, on any account) can continue without prior context.

## THE OVERRIDING RULE — 100% pixel-perfect fidelity (user mandate, 2026-07-02)

The user's explicit instruction: **every image they provide must be reproduced 100%
pixel-perfect. Not a single letter, spacing, image, section, icon or color may be left
out — even if the content (a category, a count, a product) does not exist in the app.**

Concretely, this means:

- Reproduce ALL design content **verbatim**: exact text strings, exact numbers
  (even mock/marketing numbers like "124 produits" or "1 245 résultats trouvés"),
  exact section order, all sections.
- If the design shows entities that don't exist in the DB (categories, products…),
  **create them**: seed them as admin-editable rows where they are functional taxonomy
  (see the industries seeded below), AND render the design's names/numbers as static
  view data so the display matches the PNG regardless of DB state.
- For custom artwork (illustrated icons, patterns, photos, maps): **crop the actual
  pixels from the design PNG** into `public/images/landing/` and use `<img>` — do not
  approximate with lucide icons unless the design itself clearly uses a generic icon.
- Links still map onto real routes (never dead handlers / 404s) — pick the closest
  real route for each design element.
- This mandate arrived AFTER the landing/about/auth pages were built. Those pages
  contain all their design content, but auth additionally shows a few small
  preserved-logic extras below the card content (collapsed "Comptes de démonstration",
  a passkey button, "Continuer sans compte"). If the user asks for strict pixel purity
  on auth, hide those behind a keyboard shortcut or remove them after confirming.

## What is done

| Page | Design source (repo root) | View | Route | Commit |
|------|--------------------------|------|-------|--------|
| Landing | `Official landing page.png` (1024×1536) | `resources/views/pages/home.blade.php` | `/` (`home`) | `90c3735`, `1fbf5b8` |
| About | `about page.png` (884×1779) | `resources/views/about.blade.php` | `/about` (`about`) | `3b446ba` |
| Auth (login + signup) | `auth page.png` (1536×1024, both mockups side by side) | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, shared `resources/views/auth/partials/replica-bottom.blade.php` | `/login` (`login`), `/inscription` (`inscription`) | `fad3992` |
| Categories | `categories page.png` (1536×1024) | `resources/views/pages/industries/index.blade.php` + shared `resources/views/pages/partials/gallery-header.blade.php` / `gallery-footer.blade.php` | `/galerie/secteurs` (`industries.index`) | `c1645fc` + fidelity rework (see git log) |

### Categories-page notes

- The page renders the design's 10 categories **verbatim as static view data**
  (`$designCategories` in the view): Arts & Décoration 124, Mode & Textile 112,
  Bois & Sculpture 96, Poterie & Céramique 88, Bijouterie & Accessoires 76,
  Cuir & Maroquinerie 65, Musique & Instruments 58, Produits Naturels 73,
  Agroalimentaire 59, Technologies & Innovation 42; sidebar pill "1245";
  results line "1 245 résultats trouvés".
- All category artwork is cropped from the PNG: `cat-icon-1..10.png` (card icon
  circles), `cat-side-0..10.png` (sidebar row icons), `cat-trust-1..5.png` (trust
  strip icons), plus `cat-region-map.png`, `cat-footer-kente-left/right.png`,
  `cat-footer-map.png`, `cat-sidebar-icon.png`.
- 8 industries were **seeded** so every design category filters a real route
  (slugs: `arts-decoration`, `bois-sculpture`, `poterie-ceramique`,
  `bijouterie-accessoires`, `cuir-maroquinerie`, `musique-instruments`,
  `produits-naturels`, `technologies-innovation`, sort_order 6–13, is_active 1).
  "Mode & Textile" links to the pre-existing `textile-mode`, "Agroalimentaire" to
  the pre-existing `agroalimentaire`. Do NOT rename the pre-existing industries.
- `FrontendController::industriesIndex` also computes live public product counts
  (published product + published business) and passes `$sort` — the view currently
  displays the design's static numbers (per the fidelity mandate) but the live
  counts remain available if the user ever asks to switch.
- Sort select works on the static array (`Populaires` = design order, `Nom (A–Z)`,
  `Produits`); grid/list toggle is client-side (localStorage `catView`).
- The `gallery-header` / `gallery-footer` partials are reusable for the upcoming
  product/vendor/events pages (`$galleryActive` picks the underlined nav item;
  expects `$lang`, `$isFr`, `$siacUser` in scope). Header includes the tricolor bar
  (green 28% / red 33% with two gold stars at 51.4% and 67% / gold gradient
  `#F2B01C→#E6C89A` with the heritage tagline right-aligned), search box wired to
  `gallery.search`, language dropdown, Connexion/Dashboard button (`#0A3020`).

### Auth-page notes

- The design canvas holds two page mockups (login left 784px wide, signup right
  713px wide) plus a shared full-width "Pourquoi rejoindre" band and footer; the
  band + footer + mobile bottom nav live in `auth/partials/replica-bottom.blade.php`.
- All form logic preserved: POST `/login` (email+password+hidden `next`
  passthrough), POST `/inscription` (name/email/phone/password/role). The signup
  3-step wizard is purely client-side inside one `<form>`; a server validation
  error reopens step 2. Google/Facebook buttons are visual-only ("Bientôt
  disponible" note on click). The login card is absolutely positioned over the
  baked-in card in `auth-hero.png`.
- Web login accepts email only (`type="email"` is correct — the "phone" login test
  targets the separate API endpoint `/api/v1/auth/login`).
- Assets: `auth-*.png` (hero, baskets, footer-band, footer-map, footer-motif,
  band-motif-left/right).

## What is pending — build in this order

1. **`contact page.png` — NEXT.** No dedicated contact route exists yet; nearest
   existing targets are the `support.*` routes and `route('about')`. A new GET
   route/view will likely be needed; wire any form submission to an existing
   endpoint (e.g. `support.store`) rather than inventing a dead handler.
2. Then, not yet ordered by the user — ask which is next:
   `Product diretory.png` [sic], `Product detail page.png`, `vendors directory.png`,
   `vendors detail page.png`, `events page.png`, `events detail page.png`,
   `events ticket.png`, `default product images by ategory.png` [sic]
   (the last is likely default product imagery per category).
   The product/vendor/events pages should reuse `pages/partials/gallery-header` /
   `gallery-footer`.

## The replication process (repeat for each new page)

1. Read the PNG with the Read tool; note pixel dimensions (most are 1536×1024 —
   treat design px as CSS px at a 1536 viewport; content column `max-w-[1472px]`
   for gallery pages, `max-w-[1280px]` for marketing pages).
2. Zoom sections (System.Drawing crop + nearest-neighbor upscale into the
   scratchpad) to transcribe EVERY text string and measure spacing at native scale.
3. Sample flat-fill colors with `Bitmap.GetPixel` (9-point grids per element —
   single points often hit text/anti-aliasing).
4. Crop ALL custom artwork from the PNG into `public/images/landing/`
   (naming: `about-*.png`, `partner-*.png`, `biz-*.png`, `event-*.png`,
   `auth-*.png`, `cat-*.png`). Per the fidelity mandate, crop illustrated icons
   too — don't substitute lucide for custom drawings.
   - If baked-in text must be hidden, cover with a gradient fill and clone-patch
     from adjacent clean rows (see hero-bg.png history in `90c3735`).
   - When tiling a crop as a pattern, trim ~10px off the crop edges or the tile
     seam shows as a dark band (fixed in `3b446ba`).
5. Build the page as a **standalone blade view** (own `<head>` with the Tailwind
   Play config block, include the shared partials — do NOT extend
   `layouts/app.blade.php`; dashboard/gallery legacy pages still use that layout
   and must not be disturbed).
6. Verify with the preview server (`.claude/launch.json`: `laravel` on port 8321,
   `laravel-alt` on 8322 for when another session holds 8321; PHP at
   `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`). Known quirks:
   the screenshot tool lags one action behind or times out — restart the preview
   if stuck; preview screenshots render small (structure checks only) — use
   preview_eval/snapshot/inspect for text and CSS verification; preview_click on
   a submit button may not fire the native submit — use `form.requestSubmit()`
   via preview_eval.
7. Check FR (`?lang=fr`) and EN (`?lang=en`), mobile (375px, no horizontal
   overflow, bottom nav present).
8. Run `php artisan test` (34 tests must stay green), then commit
   (PowerShell 5.1: multiline commit messages need `git commit -F <file>`).

## Shared design system

- **Fonts** (all local, no CDN): Playfair Display (serif display), Poppins (UI
  sans), Inter (legacy layouts). Loaded via `public/vendor/fonts.css` +
  `public/vendor/fonts/*.woff2`.
- **Offline rule**: never reference a CDN. Tailwind Play runtime is
  `public/vendor/tailwindcss.js`, lucide `public/vendor/lucide.min.js`, qrcodejs
  `public/vendor/qrcode.min.js`. CSP in
  `app/Http/Middleware/SecurityHeaders.php` still allows the old CDNs; local is 'self'.
- **Palette** (sampled from the designs):
  - Cream page bg `#F8F3ED` (landing) / `#F7F2EC` (about) / `#F6F4F2` (auth) /
    `#FEFDFC` (categories); card bg `#FBF9F3`/`#FDFBF7`/`#F9F6F1`; borders `#E7E1D4`
  - Deep greens: buttons `#164C28`, auth submit `#0A331C`, header button `#0A3020`,
    auth brand panel `#091C10`, sidebar header `#0A2C1D`, region-card button `#0E3022`,
    about/stats bands `#0E1D13`, landing mission strip `#0E261C`,
    landing footer `#0D0F0D`, auth footer `#0B2014`, categories footer `#0B2C1E`
  - Golds: bright text `#E5A82E`/`#D9A439`, mid `#C9942E`, sidebar active bar `#D9991F`,
    icon `#D79326`/`#D49B2D`, subscribe button `#E9A830`, CTA fill `#E0A52F` with
    dark text `#4A3A0B`/`#3A2E08`
  - Categories tricolor: green `#034226`, red `#B70415`, gold grad `#F2B01C→#E6C89A`,
    stars `#F5C33B` (landing/about tricolor: `#125527`/`#C10913`/`#EBAB1A`)
  - Accents: stat red `#A51717`, value red `#B42025`, icon brown `#4A2E1E`,
    muted `#8A857A`, sage `#A8B8AC`, trust strip bg `#F6F6EF`,
    icon circle bg `#F7F1E9` with gold arc `#E9C989`
- **Icons**: lucide via `data-lucide` for generic UI icons only; custom illustrated
  icons are cropped from the PNGs. Brand/social icons are inline SVG paths
  (`$socialIcons`: Facebook, Instagram, LinkedIn, YouTube, X — duplicated per page/partial).
- **Bilingual**: every string has FR/EN variants keyed off `$isFr`; `?lang=` +
  30-day cookie (terms/privacy routes still read the cookie only — same fix as
  `3b446ba` applies if those pages get rebuilt).

## Data seeded for the designs (admin-editable, not fixtures)

- **Industries** (see categories notes): 8 new categories seeded 2026-07-02 to make
  the design's category links functional. Pre-existing 5: `artisanat`, `aquaculture`,
  `agriculture`, `textile-mode`, `agroalimentaire`.
- **Events**: `festival-national-du-textile` (15–18 Jul 2026, Bafoussam),
  `expo-artisanat-jeunesse` (5–8 Aug 2026, Douala), `semaine-nationale-du-bois`
  (22–27 Sep 2026, Ebolowa). Design showed 2025 dates; rolled forward so they display.
- **Partners** (sort_order 1–9): MINCOMMERCE, MINAC, UNESCO, ITC, CEPII Cameroun,
  OAPI, Banque Africaine de Développement, AFD, Union Européenne. Logo tiles are
  static crops mapped by `name_fr` in the home view (`$partnerTiles`); the DB `logo`
  column is empty. Pre-existing MINEPIA/OIDAC moved to sort_order 10/11.
- `FrontendController::home()` picks the spotlight event by slug prefix `siac%`,
  lists other upcoming events, and passes 9 partners + a regions count.

## Guardrails honored so far (keep honoring them)

- No existing route, dashboard, flow, or button was removed or renamed.
- `layouts/app.blade.php` / `layouts/dashboard.blade.php` changed only in their
  `<head>` (CDN → local vendor files).
- Every replica link maps onto a real route (`businesses.index` with `?industry=`,
  `industries.index`, `events.index`, `partners.index`, `gallery.search`,
  `support.index`, `terms`, `privacy`) — no dead handlers, no 404s.
- Mobile bottom nav is replicated inside each standalone page so small screens keep it.
- When design content conflicts with live data, the design wins on DISPLAY
  (fidelity mandate above) while the underlying routes/flows stay real.
