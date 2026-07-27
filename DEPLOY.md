# Deploy Artisan Hub 237

Upload, point a database at it, run five commands. That is the whole install.

For the longer reference (queues, caching, backups, security notes) see
[`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## 1. What the server needs

| | |
|---|---|
| PHP | **8.3+** with `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip`, `bcmath` |
| PHP uploads | `upload_max_filesize` and `post_max_size` at **16M or more** — see below |
| Database | **MySQL 8.0+** (or MariaDB 10.6+) — create an empty database and a user with full rights on it |
| Web server | Apache or nginx, document root set to **`public/`** — not the project root |
| Composer | 2.x (only needed if `vendor/` is not in the upload) |

No Node build step. CSS and JS ship as vendored files under `public/vendor/`, so
there is nothing to compile.

### Upload limits — check this before launch

Most artisans will photograph their work on an Android phone, and a 12MP photo
is commonly 4–8 MB. Shared hosts frequently ship `upload_max_filesize = 2M`,
which rejects those silently: PHP discards the file before Laravel sees it, so
the artisan gets "the images field is required" for a photo they definitely
attached, with nothing in the log to explain it. Publishing a product is the one
thing the platform exists for, so this is worth two minutes.

Set both, in `.htaccess`, `.user.ini`, or your host's PHP settings panel:

```ini
upload_max_filesize = 16M
post_max_size = 20M          ; must exceed upload_max_filesize — the rest of the form rides along
```

The stored file is far smaller than the upload: images are downscaled to 1200px
and re-encoded as WebP, so a 3000×2000 / 235 KB JPEG lands at about 23 KB.
The generous limit is only about accepting what the phone produces.

`scripts/preflight.sh` checks this for you.

---

## 2. What to upload

"Upload the project" is the wrong instruction. This working copy contains a
`.env` with real credentials, a dev `vendor/` with the test runner in it, ~90
design PNGs, thousands of dev session files and a `bootstrap/cache` compiled
against a Windows path. Sending all of it either leaks something or produces a
site that ignores your configuration.

### The one-command way

On your machine, from the project root:

```bash
bash scripts/package-release.sh
```

That builds two things:

```
build/artisanhub237-release-YYYYmmdd-HHMM.zip   <- upload this
build/release/                                   <- the same tree, unzipped, for rsync or inspection
```

It runs `composer install --no-dev --optimize-autoloader --classmap-authoritative`
**into the bundle**, so the server needs no Composer at all. It prints exactly
what it left out and why, and it refuses to hand you an archive if:

- a `.env` file made it in,
- a real `APP_KEY` value appears anywhere,
- `tests/`, `node_modules/`, `.git/` or a compiled `bootstrap/cache` slipped through,
- or Composer reports a **PSR-4 namespace/directory mismatch**. That last one is
  the check worth understanding: Windows filesystems are case-insensitive, so a
  class declared `App\Modules\CMS\…` inside `app/Modules/Cms/` loads perfectly
  here and is simply *not found* on a Linux server. If a service provider is
  affected, every page of the site returns 500 and nothing about the local
  install predicts it.

Read the summary it prints. A non-zero exit means do not upload yet.

The zip has no top-level folder — its entries sit at the root, so:

```bash
unzip artisanhub237-release-*.zip -d /var/www/artisanhub
```

drops the app straight into place.

### The manual way (FTP / rsync)

If you would rather drag files across, this is the list.

**Upload**

| Path | Why |
|---|---|
| `app/` | the application |
| `bootstrap/` | framework bootstrap — but **empty `bootstrap/cache/`**, see below |
| `config/` | configuration |
| `database/` | migrations, seeders, factories |
| `public/` | **this is the web root** — index.php, images, vendored CSS/JS |
| `resources/` | Blade views and assets |
| `routes/` | route definitions |
| `storage/` | **directory tree only**, no files (see below) |
| `vendor/` | dependencies — or install them on the server if you have Composer |
| `artisan` | the CLI entry point |
| `composer.json`, `composer.lock` | needed if you install dependencies server-side |
| `.env.production.example` | the template you copy to `.env` |
| `deploy.sh`, `scripts/preflight.sh` | the two scripts meant to run on the server |

**Do not upload**

| Path | Why not |
|---|---|
| `.env`, `.env.*` (except `.env.production.example`) | your live credentials. The server gets its own. |
| `.git/` | full history, every secret ever committed, hundreds of MB |
| `node_modules/` | build-time only; this app ships no JS build step |
| `tests/`, `phpunit.xml` | dev-only, and a reachable test runner is attack surface |
| `bootstrap/cache/*.php` | **the quiet killer.** A config cache built on your machine hard-codes your paths, your database name and your `APP_URL`. On the server it silently wins over `.env`, so the site behaves as though your configuration changes did nothing — and there is no error to point at. Ship the *directory*, never the files. |
| `storage/logs/*.log` | dev logs. With `MAIL_MAILER=log` these contain real email verification codes. |
| `storage/framework/cache/*`, `sessions/*`, `views/*` | dev sessions and compiled views. Keep the directories and their `.gitignore`; drop the contents. |
| `storage/app/public/*` | images uploaded on your dev machine |
| `public/storage` | a symlink. `php artisan storage:link` recreates it on the server. |
| `*.md` except `DEPLOY.md` / `README.md`, design PNGs, `docker/`, `SIARC/`, `package.json`, `vite.config.js`, the rest of `scripts/` | development material with no runtime role |
| `.DS_Store`, `Thumbs.db`, `.idea/`, `.vscode/` | editor and OS junk |

If your host gives you SSH and Composer, you can skip `vendor/` and run
`composer install --no-dev --optimize-autoloader` there instead.

### The web root MUST be `public/`

**This is the single most important line in this document.**

Point the domain at `/path/to/artisanhub/public`, not at
`/path/to/artisanhub`.

If the document root is the project root, then `https://yourdomain.com/.env` is
a plain text file that anyone — and every automated scanner within hours of DNS
propagating — can download. It contains your database password, your mail
credentials and your `APP_KEY`. With the `APP_KEY` an attacker can forge session
cookies and any signed URL the app issues. This is not a theoretical risk; it is
the most common way a Laravel site gets breached, and it fails *silently*: the
site works perfectly while it is leaking.

Apache:

```apache
<VirtualHost *:443>
    ServerName artisanhub237.com
    DocumentRoot /var/www/artisanhub/public

    <Directory /var/www/artisanhub/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

nginx:

```nginx
server {
    server_name artisanhub237.com;
    root /var/www/artisanhub/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }   # belt and braces
}
```

Then prove it, from anywhere:

```bash
curl -I https://yourdomain.com/.env        # must be 403 or 404, never 200
```

`scripts/preflight.sh` runs exactly this check for you.

#### Fallback for shared hosts that will not let you move the web root

Some cPanel-style hosts serve `public_html/` and give you no way to change it.
The workaround is to put the application **outside** `public_html` and move only
`public/` into it:

```
/home/you/artisanhub/          <- app/, config/, vendor/, .env … everything else
/home/you/public_html/         <- the contents of the project's public/ folder
```

Then edit `public_html/index.php` — two lines near the top:

```php
require __DIR__.'/../artisanhub/vendor/autoload.php';
$app = require_once __DIR__.'/../artisanhub/bootstrap/app.php';
```

**The honest tradeoff:** this works, and the `.env` is genuinely out of reach,
but you now have the application split across two directories that must be kept
in step. Every future upload has to remember to send `public/`'s contents to one
place and everything else to another, and `index.php` is a hand-edited file that
a careless re-upload will overwrite and take the site down. It is strictly worse
than a correct web root. Ask your host — most will change it — and only fall
back to this if they refuse.

If you cannot even do that, you must at minimum block the sensitive paths in
`.htaccess` at the project root:

```apache
<FilesMatch "^\.env|composer\.(json|lock)|artisan$">
    Require all denied
</FilesMatch>
RedirectMatch 404 ^/(app|bootstrap|config|database|resources|routes|storage|vendor)/
```

Treat that as damage control, not a solution: it is a deny-list, and deny-lists
leak.

### Directory permissions

Only two paths need to be writable by the web server. Everything else should be
read-only — the application never writes to its own code, and a writable code
directory is how a single upload bug becomes a persistent backdoor.

```bash
# from the app root, as the owning user
chown -R youruser:www-data .          # 'www-data' is 'apache' or 'nginx' on some hosts
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# the two exceptions
chmod -R 775 storage bootstrap/cache
chown -R youruser:www-data storage bootstrap/cache
```

`775` (not `777`): the web-server *group* needs to write, the rest of the world
does not. If you ever find yourself typing `chmod 777`, the ownership is wrong —
fix that instead.

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

Run these **in this order** — each one depends on the one before it.

```bash
composer install --no-dev --optimize-autoloader   # SKIP if you uploaded the bundle: vendor/ is already in it
php artisan key:generate                          # writes APP_KEY into .env; without it every encrypted value fails
php artisan migrate --force                       # creates the schema; --force because production prompts otherwise
php artisan db:seed --force                       # reference data: craft taxonomy, regions, CMS pages
php artisan storage:link                          # public/storage -> storage/app/public, or every upload 404s
php artisan config:cache && php artisan route:cache && php artisan view:cache   # last, once .env is final
```

Cache **last**. `config:cache` freezes `.env` into a compiled file, so anything
you change in `.env` afterwards is ignored until you re-run it. If you edit
`.env` later, always follow with `php artisan config:cache`.

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

Run this **on the server**, before you give anyone the address:

```bash
bash scripts/preflight.sh
```

It checks the things that fail quietly: PHP version and extensions, `APP_KEY` /
`APP_DEBUG` / `APP_ENV` / `APP_URL`, whether `storage/` and `bootstrap/cache/`
are writable, whether the database connects and every migration has run, whether
mail is really configured — and, most importantly, it fetches
`APP_URL/.env` over HTTP and fails if the server hands it over. That last check
is the one that catches a wrong web root. Exit code is non-zero if any hard
check fails.

From a full development checkout (not the release bundle, which ships no tests):

```bash
php artisan test
```

115 tests should pass. That covers every route as a guest, every seller / buyer /
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

- [ ] `bash scripts/preflight.sh` exits 0
- [ ] `curl -I https://yourdomain.com/.env` returns 403 or 404 — **never 200**
- [ ] `APP_DEBUG=false` and `APP_ENV=production`
- [ ] HTTPS working, HTTP redirecting to it
- [ ] `APP_URL` matches the real domain (asset URLs and the sitemap use it)
- [ ] SMTP verified with a real test send
- [ ] `LEGAL_COMPANY_*` values filled in `.env` — they appear on the legal notice
- [ ] Demo logins off: leave `APP_DEMO_LOGIN` unset (it defaults to off)
- [ ] Database backups scheduled
- [ ] `storage/` and `bootstrap/cache` writable, and `storage/` **not** publicly reachable

---

## 8. Updating an existing install

Shorter than a first install, and with two things you must **not** do: do not
run `key:generate` (a new `APP_KEY` invalidates every session and makes every
already-encrypted value unreadable), and do not run `db:seed` (the seeders are
idempotent, but there is no reason to touch reference data the admin may have
edited since).

On your machine:

```bash
bash scripts/package-release.sh
```

On the server:

```bash
php artisan down --retry=15          # short maintenance page instead of half-deployed pages

# upload / extract over the existing install, keeping .env and storage/ intact:
#   rsync -av --delete \
#     --exclude .env --exclude storage/ --exclude bootstrap/cache \
#     build/release/ you@server:/var/www/artisanhub/

php artisan migrate --force          # NOT db:seed, NOT key:generate
php artisan config:clear && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart            # only matters if a worker is running
php artisan up
```

`--exclude storage/` matters: without it you overwrite live uploads and logs
with the bundle's empty skeleton.

`deploy.sh` in the project root does all of the above (bar the upload itself)
with maintenance mode and a failure trap, if you would rather run one command.

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
