# Deploy Artisan Hub 237

Upload, point a database at it, run five commands. That is the whole install.

For the longer reference (queues, caching, backups, security notes) see
[`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## 1. What the server needs

| | |
|---|---|
| PHP | **8.3+** with `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip`, `bcmath` |
| Database | **MySQL 8.0+** (or MariaDB 10.6+) — create an empty database and a user with full rights on it |
| Web server | Apache or nginx, document root set to **`public/`** — not the project root |
| Composer | 2.x (only needed if `vendor/` is not in the upload) |

No Node build step. CSS and JS ship as vendored files under `public/vendor/`, so
there is nothing to compile.

---

## 2. Upload

Upload the whole project **except** these, which are environment-specific or
regenerated on the server:

```
.env
node_modules/
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
```

If your host gives you SSH and Composer, you can also skip `vendor/` and run
`composer install --no-dev --optimize-autoloader` there instead.

---

## 3. Configure

```bash
cp .env.production.example .env
```

Edit `.env` and set:

```ini
APP_NAME="Artisan Hub 237"
APP_ENV=production
APP_DEBUG=false                       # never true on a live site
APP_URL=https://artisanhub237.com     # must match the real host exactly

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Leave `MAIL_MAILER=log` for the first deploy — see [Mail](#5-mail) below.

---

## 4. Install

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

`migrate` and `db:seed` are safe to re-run — seeding is idempotent, so a repeat
run updates nothing and duplicates nothing.

Then make two directories writable by the web-server user:

```bash
chmod -R 775 storage bootstrap/cache
```

Open the site. You should get the artisan directory as the landing page.

### Create your admin account

Sign up normally at `/creer-mon-compte`, then promote yourself:

```bash
php artisan tinker --execute="\App\Modules\Auth\Models\User::where('email','you@example.com')->first()->assignRole('super_admin');"
```

Sign out and back in. The admin console is at `/tableau-de-bord/admin`.

---

## 5. Mail

Email is not optional decoration here: signup sends a 6-digit code, and until a
member confirms it they cannot create a business, publish a product, or send a
message.

Deploy with `MAIL_MAILER=log` first. Codes go to `storage/logs/laravel.log`, so
nobody is locked out while you sort the relay. Then set the SMTP values and
switch over:

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="noreply@artisanhub237.com"
```

Confirm delivery before you trust it:

```bash
php artisan tinker --execute="Mail::raw('test', fn(\$m) => \$m->to('you@example.com')->subject('test'));"
```

If a send fails at runtime the code is logged, the member sees a retry message
rather than an error page, and their send quota is not consumed. As a last
resort an admin can unblock anyone from **Admin → Utilisateurs → Marquer
l'email vérifié**.

### Notification emails and the queue

Verification codes send immediately — the member is waiting for them. Everything
else ("someone quoted your request", "your order shipped") goes through a queued
job so a slow relay can't make the platform feel broken.

`QUEUE_CONNECTION=sync` is the default and is always correct: queued work simply
runs inline. Nothing is lost, the triggering request is just a little slower.

To move that work off the request path, set `QUEUE_CONNECTION=database` **and run
a worker**:

```bash
php artisan queue:work --queue=default --tries=3 --sleep=3 --max-time=3600
```

Keep it alive with supervisor, systemd, or your host's process manager. If your
host gives you neither, use a cron entry every minute instead:

```bash
* * * * * cd /path/to/app && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**Never set `database` without one of those** — the jobs queue up in the
`jobs` table and no notification email ever goes out.

---

## 6. Verify the install

```bash
php artisan test
```

84 tests should pass. That covers every route as a guest, every seller / buyer /
admin page while signed in, and the full quote → order → invoice chain.

For a live check against the running site, with real signups and real writes:

```bash
bash scripts/smoke-e2e.sh
```

It walks signup → email verification → business → product → quote request →
proposal → acceptance → purchase order → status change → invoice → messaging,
then loads every dashboard and public page. 92 checks, and it prints the
accounts it created so you can remove them afterwards.

Edit `BASE` at the top of the script to point at your host. It needs a mailbox
it can read to pick up the verification code; locally that is Mailpit on
port 8025.

---

## 7. Before you announce it

- [ ] `APP_DEBUG=false` and `APP_ENV=production`
- [ ] HTTPS working, HTTP redirecting to it
- [ ] `APP_URL` matches the real domain (asset URLs and the sitemap use it)
- [ ] SMTP verified with a real test send
- [ ] `LEGAL_COMPANY_*` values filled in `.env` — they appear on the legal notice
- [ ] Demo logins off: leave `APP_DEMO_LOGIN` unset (it defaults to off)
- [ ] Database backups scheduled
- [ ] `storage/` and `bootstrap/cache` writable, and `storage/` **not** publicly reachable

---

## 8. Updating later

```bash
php artisan down
git pull                     # or re-upload
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

If a page renders stale after a deploy, clear the compiled views:
`php artisan view:clear && php artisan view:cache`.

---

## What this platform is

A marketplace operated by a **private company**. It introduces buyers to
Cameroonian artisans and hosts the quotation exchange between them:

- public directory of artisans, businesses and products
- request for quote → priced proposal → acceptance → purchase order → invoice
- direct messaging between buyer and seller
- artisan dashboard: products, orders, quotes, verification, certificate
- buyer dashboard: requests, orders, saved items, messages
- admin console: businesses, products, users, verifications, moderation, CMS

It is **not** affiliated with any ministry or public body, is not a party to the
sales concluded through it, and **processes no payments** — purchase orders and
invoices are documents the two parties settle between themselves. That position
is stated on `/legal/avertissement` and in the footer of every page.
