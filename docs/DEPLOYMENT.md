# Deployment guide — Galerie Virtuelle Nationale de l'Artisanat du Cameroun

Production checklist for deploying this Laravel application at national scale.

## Requirements

- PHP 8.3+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip`
- MySQL 8.0+ (development uses Laragon MySQL; tests run on in-memory SQLite)
- A web server pointing its document root at `public/` (nginx or Apache)
- Composer 2

## Environment (`.env`)

Copy `.env.example` and set at minimum:

```ini
APP_ENV=production
APP_DEBUG=false            # NEVER true in production
APP_URL=https://your-domain.cm   # must match the public host — asset() and the sitemap use it

DB_CONNECTION=mysql
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=file           # or redis if available

MAIL_MAILER=smtp           # 'log' in dev; contact-form mail for guests needs a real mailer
```

Then generate the key once: `php artisan key:generate`.

## Deploy steps

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link          # business logos / product images live on the public disk
php artisan config:cache
php artisan view:cache
php artisan event:cache
```

### Route caching is NOT possible

`routes/web.php` defines most pages as **closure routes**, and
`php artisan route:cache` refuses to serialize closures. Do **not** add
`route:cache` to the deploy pipeline — it will fail. `config:cache` and
`view:cache` work normally and give most of the win.

## Background workers

The queue uses the `database` driver. Run a worker under a supervisor:

```
php artisan queue:work --tries=3 --max-time=3600
```

## Scheduled tasks

If cron features are added later, register: `* * * * * php artisan schedule:run`.
Nothing currently requires the scheduler.

## Application specifics worth knowing

- **Auth**: session-based via `session('siac_user')` (id, name, email, role,
  is_admin). `users.id` is a **UUID**.
- **Uploads**: images are stored on the `public` disk and rendered with
  `asset('storage/…')` — `storage:link` is mandatory, and `APP_URL` must be
  correct or every image URL breaks.
- **SEO**: `/sitemap.xml` and `/robots.txt` are dynamic routes (no static
  files in `public/`). The sitemap includes published businesses/products
  and events automatically.
- **Rate limiting**: login, passkey, email-verification, newsletter and the
  quote write-endpoints are throttled; the contact form uses a manual
  RateLimiter (5 messages / 5 min / IP).
- **Frontend**: Tailwind runs from the vendored Play runtime
  (`public/vendor/tailwindcss.js`) — there is no npm build step; deploys
  need no Node.js.
- **Demo accounts** (change or remove before real launch):
  `admin@artisanatcameroun.cm`, `entrepreneur@siarc2026.cm`,
  `acheteur@siarc2026.cm`.

## Verifying a deployment

```bash
php artisan test          # full suite must be green (47 tests)
curl -I https://your-domain.cm/robots.txt
curl -I https://your-domain.cm/sitemap.xml
```

Then log in with a demo account and walk the quote flow:
buyer RFQ (`/tableau-de-bord/demandes/creer`) → seller proposal
(`/tableau-de-bord/devis` → « Créer la proposition ») → buyer accepts →
purchase order → invoice.
