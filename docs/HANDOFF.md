# SIARC Platform — Handoff

**Galerie Virtuelle Nationale de l'Artisanat du Cameroun** — *Notre Héritage, Notre Fierté, Notre Avenir*
Laravel 13 platform: public artisan gallery + quote/RFQ flow + full admin suite, built as pixel replicas of the design PNGs, wired to real backend data.

_Last updated: 2026-07-04 (local production deploy + pixel-audit pass)._

---

## 1. Current state on this machine

- **Branch:** `main` (created from `master`; both point at the same work). Working tree clean.
- **Served by:** Laragon (Apache) at **http://artisanatcameroun.test** — document root `public/`.
- **Mode:** `APP_ENV=production`, `APP_DEBUG=false` (a real production build, running locally).
- **Caches:** `config`, `event`, `route`, `view` all warmed. `route:cache` **does** work here (Laravel 13 serialises closures).
- **DB:** MySQL (Laragon), all **52 migrations applied**, seed data present. `storage:link` in place.
- **Smoke:** every key public + gallery + detail page returns 200; admin pages 302→login (correct).

### Run / manage it
```bash
PHP="C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe"   # php is not on PATH
$PHP artisan about                    # health
$PHP artisan migrate:status           # migrations
$PHP artisan optimize:clear           # drop caches (needed to see Blade edits)
$PHP artisan optimize                 # re-warm config+route+view (add event:cache/route:cache via deploy.sh)
$PHP artisan test                     # full suite
```
To return to **dev mode** for editing: set `APP_ENV=local`, `APP_DEBUG=true` in `.env`, then `optimize:clear`.
Blade edits do **not** show while `view:cache` is active — run `view:clear` first.

### Demo credentials
| Role | Email | Password |
|---|---|---|
| Admin | `admin@artisanatcameroun.cm` | `Admin@SIARC2026` |
| Entrepreneur | `entrepreneur@siarc2026.cm` | `Demo@SIARC2026` |
| Buyer | `acheteur@siarc2026.cm` | `Demo@SIARC2026` |

---

## 2. Architecture (what a maintainer must know)

- **Routes:** most pages are **closure routes** in `routes/web.php` (not controllers). `route:cache` works; `route:list` for the map.
- **Auth:** session-based via `session('siac_user')` = `{id, name, email, role, is_admin}`. **`users.id` is a UUID** — never `foreignId()` to users.
- **Brand name:** **SIARC** (Salon International de l'Artisanat du Cameroun). Never write "SIAC" in user-visible text (internal session key `siac_user` is kept intentionally).
- **Chrome rules (mandatory):**
  - *Public* pages use the canonical `pages/partials/directory-header` + `directory-footer`, **regardless** of what chrome a PNG shows.
  - *Admin* pages use `pages/partials/admin-sidebar` + `admin-topbar`, or the heritage header `pages/partials/admin-heritage-header` (mask + kente + medallion) for heritage-branded pages.
- **Frontend assets are all local** (no CDNs, ever): Tailwind Play runtime `public/vendor/tailwindcss.js`, vendored lucide icons (PascalCase keys; **Facebook/Twitter/Linkedin are absent** → use inline brand SVGs), Poppins/Playfair fonts local.
- **Images:** stored on the `public` disk, rendered with `asset('storage/…')`. `APP_URL` must match the host or every image URL breaks.
- **SEO/hardening:** dynamic `/sitemap.xml` + `/robots.txt` (no static robots.txt — it would shadow the route). Newsletter + quote writes are throttled.

## 3. Test suite
`$PHP artisan test` — **65 tests / 258 assertions**. Guards worth knowing:
- `RouteSmokeTest` — no parameterless GET route may 5xx for a guest.
- `ViewIntegrityTest` — every `route()` name referenced in a view exists; no `href="#"`.
- `AdminPagesRenderTest` / `CentresRenderTest` — admin + detail pages render 200 with seeded data.
- `QuoteFlowTest` — the RFQ → proposal → PO → invoice backend end-to-end.

> Note: some seed migrations (support tickets, verification apps) skip when no users exist at migration time, so those tests create their own records.

## 4. Deploy to a real server
Everything is packaged:
- **`deploy.sh`** — idempotent server-side release (maintenance mode → composer `--no-dev` → `migrate --force` → `storage:link` → cache warm incl. `route:cache` → `queue:restart`). Safe to re-run.
- **`.env.production.example`** — production env template, every key, REQUIRED markers.
- **`docs/DEPLOYMENT.md`** — full checklist (PHP extensions, web root, queue worker, caveats).
- Release tarballs were built in the session scratchpad (an 18 MB lean bundle excluding root design PNGs, and a full archive).

Steps: provision PHP 8.3 + MySQL 8 + web root at `public/`; copy `.env.production.example`→`.env` and fill it; `php artisan key:generate`; run `./deploy.sh`; run a supervised `php artisan queue:work`.

## 5. Known issues / in progress (pixel-audit pass)
- **3 admin views were routed but never created** (500 for an admin) — being built from their PNGs in this pass:
  - `admin-payments` (`Gestion de payment.png`), `admin-analytics` (`analytic dashboard.png`), `admin-product-detail` (`detail de produits.png`).
  - Follow-up: extend `AdminPagesRenderTest` to hit the `{id}` detail + analytics/payments routes so this can't regress.
- **~22 secondary pages still use legacy content styling** (generic `gray-*`/`forest-*`) inside the re-skinned chrome — no design PNG exists for them, so they need an identity pass, not a pixel replica: `dashboard/ministry`, `profile`, `security`, `messages/inbox`, `thread`, `notifications`, `notification-settings`, `regional-rep`, `technical-reviewer`, `technical-history`, `business-form`, `product-form`, `verification`, `support-index`, `support-show`, plus `privacy`, `terms`, `search`, and the auth secondary flows.
- A full **pixel-fidelity audit** of all ~80 built design pages vs their PNGs is running; discrepancies (text, sections, spacing, colours, icons, fonts, branding) are being fixed. Results appended here on completion.

## 6. Source-of-truth docs
- `docs/DESIGN-REPLICA-HANDOFF.md` — the replica build process (crop → transcribe → standalone view → verify → test), palette, guardrails.
- `docs/DEPLOYMENT.md` — production deploy.
- Auto-memory: `C:\Users\PC\.claude\projects\C--laragon-www-artisanatcameroun\memory\` (design-replica-workflow, pixel-perfect-mandate, windows-shell-quirks).
