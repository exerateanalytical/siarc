# Platform Gap Remediation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the dead legacy application coexisting in this repo, fix the bugs it causes, and close every functional gap in the SIAC platform found in the 2026-07-01 full-platform scan.

**Architecture:** The SIAC platform (modules in `app/Modules/*`, web controllers in `app/Http/Controllers`, session key `siac_user`) is the product. A legacy job-board/directory app (~3,000 lines of route closures in `routes/web.php`, session key `auth_user`, views in `resources/views/pages/{jobs,cv,collabcam,...}`) references ~40 database tables that no longer exist and crashes on every data access. It gets deleted. New features follow the established patterns: route closures or thin controllers, Blade views extending `layouts/dashboard.blade.php` (dashboard) or `layouts/app.blade.php` (public), Lucide icons, semantic color system, bilingual FR/EN via `$lang`.

**Tech Stack:** Laravel 12, MySQL, Blade + Tailwind CDN, Lucide icons, session-based web auth (`siac_user`), Spatie roles (guard `sanctum`).

**Verification methodology:** This codebase has no unit-test culture; every task is verified by (1) `php artisan route:list` succeeding, (2) the route-reference audit script (every `route('...')` usage resolves), and (3) live browser/HTTP smoke checks against `php artisan serve` using the six demo accounts. This matches how the platform has been verified all along.

**Decisions locked by user (2026-07-01):**
- Legacy app: **delete entirely** (keep shared auth: login, register, logout, password reset, lang switch, storage routes).
- Orphaned tables: **keep schema, wire the useful ones** (`business_views`, `product_views`); do not drop anything.

---

## Phase 1 — Legacy purge

### Task 1: Delete legacy routes from routes/web.php

**Files:**
- Modify: `routes/web.php` (4,529 lines → target ~1,800)

Legacy route groups to DELETE (identified by references to nonexistent tables — `companies`, `company_users`, `job_postings`, `job_applications`, `user_cvs`, `cover_letters`, `collabcam_*`, `communities`, `federations`, `tenders`, `wallets`, `kyc_applications`, `invest_*`, `share_offerings`, `salary_reports`, `digital_cards`, `associations`, `supplier_reviews`, `logistics_listings`, `innovation_projects`, `knowledge_resources`, `shared_assets`, `employee_profiles`, `tickets`, `notifications` (legacy table), `investor_profiles`, `watchlist_items`, `investment_pledges`):

| Path prefix | Route names |
|---|---|
| `/jobs`, `/saved-jobs`, `/job-alerts`, `/recruiter` | jobs.*, saved.jobs, job.alerts.*, recruiter.* |
| `/cv`, `/cv-templates`, `/cover-letters`, `/cover-letter` | cv.*, cover.letters.* |
| `/collabcam/**` + `api/v1/collabcam` group | collabcam.* |
| `/communities/**`, `/federations/**`, `/associations/**` | (mostly unnamed closures) |
| `/invest-hub/**`, `/invest/**`, `/investor-profile`, `/pay/**`, `/offerings/**`, `/portfolio` | invest.*, investor.*, pay, offerings.*, offering.show, portfolio |
| `/esg/**`, `/export-hub/**`, `/compliance/**`, `/prm/**`, `/salaries/**`, `/health-score/**`, `/logistics/**`, `/innovation/**`, `/knowledge/**` | unnamed closures |
| `/assets/**`, `/cards`, `/card/**` | unnamed closures |
| `/blog/**` | blog.* |
| `/companies/**`, `/directory`, `/search`, `/search/suggest`, `/analytics` | company.*, directory, search, search.suggest, analytics |
| `/admin/**` (legacy panel: announcements, claims, companies, kyc, tickets, users) | admin.announcements.*, admin.claims.*, admin.companies.*, admin.kyc.*, admin.tickets.*, admin.users (LEGACY duplicate), admin.users.toggle*, admin.dashboard |
| `/dashboard`, `/messages`, `/messages/{userId}`, `/notifications` (legacy), `/notifications/count`, `/notifications/mark-read`, `/settings/notifications`, `/profile`, `/my-profile`, `/support` (legacy GET+POST at root), `/help/**`, `/how-it-works`, `/applications/**` | dashboard, messages, messages.thread(legacy), notifications, notifications.count, notifications.mark-read, notifications.settings*, profile*, my.profile*, support, support.create, help.*, how-it-works, applications.* |

Routes to KEEP: `home` (SIAC landing), `about/terms/privacy`, `login/login.post`, `register/register.post` + `inscription*`, `logout`, `password.request/email/reset/update`, `lang.switch`, `storage.local*`, `developer*` (API-as-a-product portal — port off `auth_user` in Task 3), `docs/api` (Scramble), `horizon`, everything under `galerie/`, `tableau-de-bord/`, `evenements/`, `partenaires`, and the SIAC helper endpoints (`api-interne/villes`, gallery search, SIAC notification count if present).

- [ ] **Step 1:** Delete legacy blocks section by section (work bottom-up so line numbers stay valid). After each section removal run: `php artisan route:list > /dev/null && echo OK`
- [ ] **Step 2:** Grep for stragglers: `grep -nE "job_postings|user_cvs|collabcam|communities|federations|kyc_applications|wallets|tenders|share_offerings|salary_reports|digital_cards|associations|supplier_reviews|logistics_listings|innovation_projects|knowledge_resources|shared_assets|employee_profiles|invest_|cover_letters|company_users" routes/web.php` — expected: no matches.
- [ ] **Step 3:** Confirm the duplicate `admin.users` name is gone: `php artisan route:list 2>/dev/null | grep -c "admin.users "` → exactly 1.
- [ ] **Step 4:** Run the route-reference audit (used routes vs defined routes diff) — expected: empty diff. Any view still referencing a deleted route name gets handled in Task 2.
- [ ] **Step 5:** Commit: `git commit -m "Remove dead legacy app routes (jobs/CV/collabcam/invest/old admin)"`

### Task 2: Delete legacy views and fix dangling references

**Files:**
- Delete: legacy view directories/files under `resources/views/` (identify by rendering only from deleted routes): `pages/jobs/`, `pages/cv/`, `pages/cover-letters/`, `pages/collabcam/`, `pages/communities/`, `pages/federations/`, `pages/invest/`, `pages/offerings/`, `pages/companies/`, `pages/admin/` (legacy panel views), `pages/blog/`, `pages/help/`, and any others found via the audit below.
- Modify: any surviving view/layout that links to deleted route names.

- [ ] **Step 1:** Build the deletion list mechanically: for every blade file, check whether any surviving route or view references it (`view('...')` in routes/controllers + `@include`/`@extends` in blades). Delete only unreferenced ones tied to legacy features.
- [ ] **Step 2:** Re-run the route audit; fix any surviving view that links to a deleted route (e.g., old navbars in `layouts/app.blade.php` linking `/jobs`).
- [ ] **Step 3:** Browser smoke: load `/`, `/login`, `/galerie/entreprises`, `/evenements`, `/partenaires` — 200s, no missing-view exceptions.
- [ ] **Step 4:** Commit: `git commit -m "Remove legacy views orphaned by route purge"`

### Task 3: Unify sessions — eliminate auth_user

**Files:**
- Modify: `routes/web.php` (login/register/logout closures, `/developer` portal, storage route guard — all 17 `auth_user` usages)

- [ ] **Step 1:** `grep -n "auth_user" routes/web.php app resources -r` — for each survivor, port to `siac_user` (same keys: id, name, email, role, is_admin) or delete if it was legacy-only.
- [ ] **Step 2:** Port `/developer` portal (uses existing `api_consumers`/`api_keys` tables) to `siac_user` auth; keep functionality (create/revoke API keys).
- [ ] **Step 3:** Verify login as each demo account still works; verify `/developer` works logged in as entrepreneur; verify grep for `auth_user` returns zero hits.
- [ ] **Step 4:** Commit: `git commit -m "Unify web auth on siac_user session; port developer portal"`

---

## Phase 2 — Missing SIAC features

### Task 4: Forgot-password flow wired to SIAC login

**Files:**
- Modify: `resources/views/auth/login.blade.php` (add link), password-reset views (restyle onto SIAC design if legacy-styled)
- Verify: `password.request/email/reset/update` routes work against `users` table

- [ ] **Step 1:** Add "Mot de passe oublié ? / Forgot password?" link on the login form pointing to `route('password.request')`.
- [ ] **Step 2:** Walk the flow in browser: request reset for `acheteur@siac2026.cm`, confirm token row created (`password_reset_tokens` table) and reset page renders. (Mail driver is `log` — read token from `storage/logs/laravel.log`.)
- [ ] **Step 3:** Reset the password, log in with the new one, reset it back to `Demo@SIAC2026`.
- [ ] **Step 4:** Commit.

### Task 5: Profile/settings page in the dashboard shell (all roles)

**Files:**
- Create: `resources/views/pages/dashboard/profile.blade.php`
- Modify: `routes/web.php` (GET/POST `tableau-de-bord/profil` → `profile.show`, `profile.update`, `profile.password`), `resources/views/layouts/dashboard.blade.php` (add nav item to every role's group + link the topbar avatar)

Page contents: update name + preferred language; change password (current + new + confirm, validated against hash); read-only email + role badge.

- [ ] **Step 1:** Add routes (session-guarded closures following existing dashboard closure pattern; refresh `siac_user` session array after name change).
- [ ] **Step 2:** Build the view on `layouts.dashboard` (two cards: "Informations" and "Mot de passe"; semantic colors; Lucide `user-cog` icon).
- [ ] **Step 3:** Add `profile.show` to `$navGroups` for every role in the dashboard layout and make the topbar avatar link to it.
- [ ] **Step 4:** Browser-verify as buyer: change name (topbar updates), change password, log back in. Restore demo values.
- [ ] **Step 5:** Commit.

### Task 6: Buyer saved-items page

**Files:**
- Create: `resources/views/pages/dashboard/saved.blade.php`
- Modify: `routes/web.php` (GET `tableau-de-bord/sauvegardes` → `saved.index`), `layouts/dashboard.blade.php` (buyer nav), mobile bottom nav in `layouts/app.blade.php` ("Saved" tab → `saved.index`)

Page: two sections — saved products (from `saved_products` joined to products, 2-col card grid) and saved businesses (`saved_businesses`), each with unsave buttons reusing `products.toggle-save` (and a new `businesses.toggle-save` POST if missing).

- [ ] **Step 1:** Check whether a business-save web route exists; create if missing (mirror `products.toggle-save`).
- [ ] **Step 2:** Add `saved.index` route + view; wire navs.
- [ ] **Step 3:** Browser-verify as buyer: save a product from the gallery, see it on the page, unsave it.
- [ ] **Step 4:** Commit.

### Task 7: Admin moderation — product reports + reviews

**Files:**
- Create: `resources/views/pages/dashboard/admin-moderation.blade.php`
- Modify: `app/Http/Controllers/AdminWebController.php` (methods `moderation()`, `resolveReport()`, `deleteReview()`), `routes/web.php` (GET `admin.moderation`, POST `admin.reports.resolve`, POST `admin.reviews.destroy`), `layouts/dashboard.blade.php` (admin nav item "Modération")

Page: tab pills — "Signalements" (product_reports with status pending, product + reporter + reason, actions: resolve/dismiss + link to product) and "Avis" (latest business_reviews with delete action). All actions AuditLog::record()-ed.

- [ ] **Step 1:** Controller methods + routes (guarded by `requireAdmin`).
- [ ] **Step 2:** View on the dashboard shell.
- [ ] **Step 3:** Seed one test report via tinker, verify it appears, resolve it, check audit log entry.
- [ ] **Step 4:** Commit.

### Task 8: Notification preferences page

**Files:**
- Create: `resources/views/pages/dashboard/notification-settings.blade.php`
- Modify: `routes/web.php` (GET/POST `tableau-de-bord/notifications/preferences` → `notifications.settings`), link from notifications page header

Uses existing `notification_preferences` table (check real columns first; wire only channels that exist — likely per-type email/in-app toggles). `NotificationPreference` model already exists.

- [ ] **Step 1:** Inspect table schema (`SHOW COLUMNS`), map to toggle UI.
- [ ] **Step 2:** Routes + view (toggle switches, save button).
- [ ] **Step 3:** Browser-verify persistence across reload.
- [ ] **Step 4:** Commit.

### Task 9: Events API endpoints (API-first parity)

**Files:**
- Create: `app/Modules/Events/Controllers/PublicEventController.php`, `app/Modules/Events/Routes/api.php`, `app/Modules/Events/Providers/EventsServiceProvider.php`
- Modify: `bootstrap/providers.php` (register provider — match how other modules register)

Endpoints (mirror other public modules): `GET /api/v1/events` (published, upcoming/past filter), `GET /api/v1/events/{slug}` (with exhibitors), `POST /api/v1/events/{slug}/attend` + `DELETE` (Sanctum-authed), `POST /api/v1/events/{slug}/exhibit` (Sanctum-authed, business owners).

- [ ] **Step 1:** Copy the provider/routes wiring pattern from `app/Modules/Cms` (smallest example).
- [ ] **Step 2:** Controller with the five endpoints, reusing logic from `EventWebController`.
- [ ] **Step 3:** Verify: `php artisan route:list | grep api/v1/events` shows 5 routes; `curl localhost:8000/api/v1/events` returns JSON.
- [ ] **Step 4:** Commit.

### Task 10: View tracking + repo hygiene

**Files:**
- Modify: `app/Http/Controllers/FrontendController.php` (or wherever business/product show pages render) — insert rows into `business_views` / `product_views` (check real columns first) alongside existing `views_count` increments
- Delete: empty dirs `app/Modules/Search/`, `app/Modules/Api/`

- [ ] **Step 1:** Inspect `business_views`/`product_views` schemas; add inserts on public show pages (fire-and-forget, wrapped in try/catch so tracking never breaks a page).
- [ ] **Step 2:** Delete the two empty module directories.
- [ ] **Step 3:** Load a business page, confirm a row lands in `business_views`.
- [ ] **Step 4:** Commit.

---

## Phase 3 — Final verification

### Task 11: Full-platform regression pass

- [ ] **Step 1:** Route audit script — every `route()` reference resolves; zero missing.
- [ ] **Step 2:** `grep -rn "auth_user"` → zero. `wc -l routes/web.php` → report the reduction.
- [ ] **Step 3:** Browser smoke as all six demo accounts: dashboard renders, no console errors, sidebar links all land on 200s.
- [ ] **Step 4:** Public pages: home, gallery, business detail, product detail, events, partners, about/terms/privacy, login, register, forgot-password.
- [ ] **Step 5:** Final commit.
