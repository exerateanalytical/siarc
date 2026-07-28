#!/usr/bin/env bash
# =============================================================================
#  package-release.sh — build the exact set of files to upload to the server.
#
#  WHY THIS EXISTS
#  DEPLOY.md tells you how to set the server up. It never told you *what to
#  send*. "Upload the project" is the wrong instruction: this working copy
#  contains a .env with live-ish credentials, a 100 MB+ dev vendor/ tree,
#  ~90 design PNGs, thousands of dev session files and a bootstrap/cache
#  compiled against THIS machine's paths. Any one of those either leaks
#  something or breaks the site in a way that is hard to diagnose.
#
#  This script builds a clean tree in build/release/ and a zip beside it.
#  Whatever comes out of here is safe to upload, and nothing else is.
#
#  USAGE (from the project root, Git Bash on Windows or any Linux shell):
#      bash scripts/package-release.sh                 # VPS layout (default)
#      RELEASE_LAYOUT=shared bash scripts/package-release.sh
#      bash scripts/build-release.sh                   # same as the line above
#
#  LAYOUTS
#      root    (default) one tree with public/ inside it. Point the vhost's
#              DocumentRoot at <app>/public. Right for a VPS or a container.
#
#      shared  two trees: an application directory and a public_html. For hosts
#              that own the document root and will not let you move it —
#              Namecheap shared hosting, which is what artisanhub237.com runs
#              on. See scripts/build-release.sh for the full layout.
#
#  OUTPUT
#      build/release/                                    <- inspect or rsync this
#      build/artisanhub237-release-YYYYmmdd-HHMM.zip     <- or upload this
#
#  The zip has NO top-level folder: its entries sit at the root, so
#  `unzip release.zip -d /var/www/artisanhub` drops the app straight in.
# =============================================================================
set -euo pipefail

# 'root' | 'shared' — see LAYOUTS above.
RELEASE_LAYOUT="${RELEASE_LAYOUT:-root}"
# Name of the application directory beside public_html in the shared layout.
APP_DIR_NAME="${APP_DIR_NAME:-artisanhub237}"
case "$RELEASE_LAYOUT" in
  root|shared) ;;
  *) echo "RELEASE_LAYOUT must be 'root' or 'shared', got '$RELEASE_LAYOUT'" >&2; exit 2 ;;
esac

# --- locate the project root regardless of where the script was called from ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

BUILD_DIR="$ROOT/build"
STAGE="$BUILD_DIR/release"
STAMP="$(date +%Y%m%d-%H%M)"
ZIP_PATH="$BUILD_DIR/artisanhub237-release-$STAMP.zip"

# Colour only when we are attached to a terminal, so piping to a file stays clean.
if [ -t 1 ]; then
  R=$'\033[31m'; G=$'\033[32m'; Y=$'\033[33m'; B=$'\033[1m'; N=$'\033[0m'
else
  R=''; G=''; Y=''; B=''; N=''
fi
say()  { printf '%s\n' "$*"; }
step() { printf '\n%s==> %s%s\n' "$B" "$*" "$N"; }
ok()   { printf '  %sok%s   %s\n' "$G" "$N" "$*"; }
warn() { printf '  %swarn%s %s\n' "$Y" "$N" "$*"; }
die()  { printf '\n%sFATAL%s %s\n' "$R" "$N" "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
#  0. Tooling
# -----------------------------------------------------------------------------
step "[0/6] Tooling"

PHP_BIN="${PHP_BIN:-php}"
command -v "$PHP_BIN" >/dev/null 2>&1 || {
  # Laragon keeps PHP off the PATH on this machine; try the usual spot.
  for c in /c/laragon/bin/php/php-8.3*/php.exe; do [ -x "$c" ] && PHP_BIN="$c" && break; done
}
command -v "$PHP_BIN" >/dev/null 2>&1 || die "php not found. Set PHP_BIN=/path/to/php and re-run."
ok "php      $("$PHP_BIN" -r 'echo PHP_VERSION;')"

# Composer may be a PATH binary, a phar in the repo, or Laragon's bundled copy.
COMPOSER_CMD=""
if [ -n "${COMPOSER_BIN:-}" ]; then
  COMPOSER_CMD="$COMPOSER_BIN"
elif command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD="composer"
elif [ -f "$ROOT/composer.phar" ]; then
  COMPOSER_CMD="$PHP_BIN $ROOT/composer.phar"
elif [ -f /c/laragon/bin/composer/composer.phar ]; then
  COMPOSER_CMD="$PHP_BIN /c/laragon/bin/composer/composer.phar"
fi
[ -n "$COMPOSER_CMD" ] || die "composer not found. Set COMPOSER_BIN=/path/to/composer and re-run."
ok "composer $($COMPOSER_CMD --version --no-interaction 2>/dev/null | head -1)"

# Zip: a real `zip` binary if present, otherwise PHP's ZipArchive, otherwise
# PowerShell. Git Bash on Windows ships none of the first, so the fallbacks matter.
ZIP_METHOD=""
if command -v zip >/dev/null 2>&1; then
  ZIP_METHOD="zip"
elif "$PHP_BIN" -r 'exit(class_exists("ZipArchive")?0:1);'; then
  ZIP_METHOD="php"
elif command -v powershell >/dev/null 2>&1; then
  ZIP_METHOD="powershell"
else
  die "No way to make a zip (no zip binary, no PHP zip extension, no powershell)."
fi
ok "zip via  $ZIP_METHOD"

# -----------------------------------------------------------------------------
#  1. Clean stage
# -----------------------------------------------------------------------------
step "[1/6] Clean staging directory"
rm -rf "$STAGE"
mkdir -p "$STAGE"
ok "$STAGE"

# -----------------------------------------------------------------------------
#  2. Copy the runtime tree
#
#  Deliberately an ALLOW-list, not a deny-list. A deny-list silently ships every
#  new file someone drops in the project root; an allow-list silently ships
#  nothing, which is the safe direction to fail in.
# -----------------------------------------------------------------------------
step "[2/6] Copy application files"

INCLUDE_DIRS="app bootstrap config database public resources routes"
INCLUDE_FILES="artisan composer.json composer.lock .env.production.example DEPLOY.md README.md deploy.sh"

for d in $INCLUDE_DIRS; do
  [ -d "$ROOT/$d" ] || die "missing expected directory: $d"
  # -R (not -L): copy symlinks as symlinks, never follow them. public/storage is
  # a symlink into storage/app/public on a dev box; following it would silently
  # bake 11 MB of local uploads into the bundle.
  cp -R "$ROOT/$d" "$STAGE/$d"
  ok "$d/"
done

for f in $INCLUDE_FILES; do
  if [ -f "$ROOT/$f" ]; then cp "$ROOT/$f" "$STAGE/$f"; ok "$f"; else warn "$f not found, skipped"; fi
done

# preflight.sh is the one thing under scripts/ that is meant to run ON THE
# SERVER, so it ships even though the rest of scripts/ is dev tooling.
mkdir -p "$STAGE/scripts"
cp "$SCRIPT_DIR/preflight.sh" "$STAGE/scripts/preflight.sh"
ok "scripts/preflight.sh (server-side check — the only script that ships)"

# --- storage/: directory skeleton only ---------------------------------------
# Laravel needs these directories to exist and be writable; it does not need one
# byte of what is in them here. Shipping dev sessions would hand a live visitor
# a dev session cookie's worth of state, and shipping laravel.log would ship
# whatever got logged during development, verification codes included.
step "[2b/6] Rebuild storage/ as an empty skeleton"
rm -rf "$STAGE/storage"
( cd "$ROOT" && find storage -type d -print0 ) | while IFS= read -r -d '' dir; do
  mkdir -p "$STAGE/$dir"
done
# Keep the .gitignore files: they are what makes the directories survive git and
# they cost nothing.
( cd "$ROOT" && find storage -name '.gitignore' -type f -print0 ) | while IFS= read -r -d '' gi; do
  cp "$ROOT/$gi" "$STAGE/$gi"
done
ok "$(find "$STAGE/storage" -type d | wc -l | tr -d ' ') directories, no data"

# --- bootstrap/cache: keep the directory, drop every compiled file ------------
# A config cache built here hard-codes THIS machine's absolute paths, database
# name and APP_URL. On the server it wins over .env and produces a site that
# ignores everything you just configured — with no error to tell you why.
rm -f "$STAGE"/bootstrap/cache/*.php
ok "bootstrap/cache emptied (compiled config from this machine must not travel)"

# --- public/storage symlink ---------------------------------------------------
# Recreated on the server by `php artisan storage:link`. A Windows symlink in a
# zip either fails to extract or extracts as a junk file.
rm -rf "$STAGE/public/storage"
ok "public/storage symlink dropped (php artisan storage:link recreates it)"

# -----------------------------------------------------------------------------
#  3. Prune anything that slipped in with the directory copies
# -----------------------------------------------------------------------------
step "[3/6] Prune excluded files"

# .env of any flavour except the documented example. This is the one that matters.
# (Parenthesised so the -type/-print apply to BOTH -name branches, not just the
# last one — find's implicit -a binds tighter than -o.)
find "$STAGE" -maxdepth 2 -type f \( -name '.env' -o -name '.env.*' \) \
  ! -name '.env.production.example' -print0 2>/dev/null \
  | xargs -0 -r rm -f
# Dev-only Markdown (design handoffs, presentations) — DEPLOY.md and README.md
# were copied explicitly above and are not under any of the copied directories.
find "$STAGE" -name '*.md' ! -path "$STAGE/DEPLOY.md" ! -path "$STAGE/README.md" \
  ! -path "$STAGE/vendor/*" -type f -delete 2>/dev/null || true
# Editor and OS droppings.
find "$STAGE" \( -name '.DS_Store' -o -name 'Thumbs.db' -o -name '*.swp' -o -name '*~' \) -type f -delete 2>/dev/null || true
find "$STAGE" -maxdepth 3 -type d \( -name '.idea' -o -name '.vscode' -o -name '.git' -o -name 'node_modules' \) -prune -exec rm -rf {} + 2>/dev/null || true
# Log files anywhere.
find "$STAGE" -name '*.log' -type f -delete 2>/dev/null || true
ok "pruned"

# -----------------------------------------------------------------------------
#  4. Production dependencies, installed INTO the bundle
#
#  We do not copy the dev vendor/ tree; we build a fresh one with dev packages
#  omitted. That is smaller, and more importantly it means phpunit, faker and
#  pint never reach the server.
#
#  --no-scripts: the post-autoload-dump hook runs `artisan package:discover`,
#  which needs an .env we deliberately did not stage. Laravel regenerates
#  bootstrap/cache/packages.php on the first request anyway.
# -----------------------------------------------------------------------------
step "[4/6] composer install --no-dev (into the bundle)"
INSTALL_LOG="$BUILD_DIR/composer-install.log"
( cd "$STAGE" && $COMPOSER_CMD install \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist 2>&1 ) | tee "$INSTALL_LOG"
# tee is the last command in the pipe, so check composer's own status, not tee's.
[ "${PIPESTATUS[0]}" -eq 0 ] || die "composer install failed — the bundle is incomplete."
rm -f "$STAGE"/bootstrap/cache/*.php    # composer may have re-created them
ok "vendor/ built without dev packages"

# --- PSR-4 case check ---------------------------------------------------------
# Composer prints "does not comply with psr-4 autoloading standard ... Skipping."
# when a class's namespace does not match its directory. On Windows this is
# invisible: NTFS is case-insensitive, so App\Modules\CMS\ still finds
# app/Modules/Cms/. On a Linux server it is not — the file is simply not found
# and every request that touches the class dies with "Class not found". You would
# see a working site here and a 500 there, with nothing in between to explain it.
# --classmap-authoritative makes it fail everywhere, which is at least honest.
#
# We read the warnings out of the install log rather than running a second
# `dump-autoload`. That is not just to save a slow step: a plain
# `dump-autoload --optimize` writes a NON-authoritative loader over the
# authoritative one we just built, silently downgrading the bundle and
# re-enabling the PSR-4 filesystem fallback that hides this very bug on Windows.
PSR4_OUT="$(grep -i 'does not comply with psr-4' "$INSTALL_LOG" || true)"
if [ -n "$PSR4_OUT" ]; then
  printf '\n%s  ############################################################%s\n' "$Y" "$N"
  printf '%s  #  PSR-4 MISMATCH — THIS WILL 500 ON A LINUX SERVER         #%s\n' "$Y" "$N"
  printf '%s  ############################################################%s\n' "$Y" "$N"
  printf '%s\n' "$PSR4_OUT" | sed 's/^/    /'
  printf '  These classes declare a namespace whose case does not match their\n'
  printf '  directory. Composer skipped them, so they are in no classmap and PSR-4\n'
  printf '  lookup will miss them on a case-sensitive filesystem.\n'
  printf '  Fix before uploading: make the directory name and the namespace segment\n'
  printf '  match exactly, then re-run this script.\n\n'
  PSR4_BROKEN=1
else
  ok "no PSR-4 namespace/directory mismatches"
  PSR4_BROKEN=0
fi

# -----------------------------------------------------------------------------
#  5. Zip
# -----------------------------------------------------------------------------
step "[5/6] Create archive"
mkdir -p "$BUILD_DIR"
rm -f "$ZIP_PATH"
case "$ZIP_METHOD" in
  zip)
    ( cd "$STAGE" && zip -r -q -9 "$ZIP_PATH" . )
    ;;
  php)
    "$PHP_BIN" -r '
      $stage = $argv[1]; $out = $argv[2];
      $zip = new ZipArchive();
      if ($zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { fwrite(STDERR, "cannot open zip\n"); exit(1); }
      $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
      );
      foreach ($it as $f) {
        $local = str_replace("\\", "/", substr($f->getPathname(), strlen($stage) + 1));
        if ($f->isDir()) { $zip->addEmptyDir($local); }
        else { $zip->addFile($f->getPathname(), $local); }
      }
      $zip->close();
    ' "$STAGE" "$ZIP_PATH" || die "zip creation failed"
    ;;
  powershell)
    powershell -NoProfile -Command "Compress-Archive -Path '$(cygpath -w "$STAGE")\\*' -DestinationPath '$(cygpath -w "$ZIP_PATH")' -Force"
    ;;
esac
[ -f "$ZIP_PATH" ] || die "archive was not created"
ok "$(basename "$ZIP_PATH")"

# -----------------------------------------------------------------------------
#  6. Safety net — refuse to hand over a bundle that leaks secrets
# -----------------------------------------------------------------------------
step "[6/6] Verify the bundle"
FAILED=0

if find "$STAGE" -name '.env' -o -name '.env.local' -o -name '.env.backup' -o -name '.env.production' | grep -q .; then
  printf '  %sFAIL%s an .env file is in the bundle\n' "$R" "$N"; FAILED=1
else
  ok "no .env file"
fi

# A real Laravel APP_KEY is `base64:` followed by 44 base64 characters. Matching
# that exact shape — rather than "APP_KEY= followed by anything" — is what makes
# this check useful: the loose version flags preflight.sh's own
# `APP_KEY="$(env_get APP_KEY)"` and deploy.sh's `grep "^APP_KEY=base64:"`, and a
# check that cries wolf gets switched off. This one only fires on a real key, in
# any file, whatever it is called.
KEY_HITS="$(grep -rIl --exclude-dir=vendor -E "base64:[A-Za-z0-9+/]{30,}={0,2}" "$STAGE" 2>/dev/null || true)"
if [ -n "$KEY_HITS" ]; then
  printf '  %sFAIL%s an APP_KEY value is present in:\n' "$R" "$N"
  printf '%s\n' "$KEY_HITS" | sed 's/^/         /'
  FAILED=1
else
  ok "no APP_KEY value"
fi

for bad in tests phpunit.xml node_modules .git SIARC build; do
  if [ -e "$STAGE/$bad" ]; then printf '  %sFAIL%s %s is in the bundle\n' "$R" "$N" "$bad"; FAILED=1; fi
done
[ "$FAILED" -eq 0 ] && ok "no tests/, node_modules/, .git/, SIARC/, build/"

if ls "$STAGE"/bootstrap/cache/*.php >/dev/null 2>&1; then
  printf '  %sFAIL%s bootstrap/cache still holds compiled PHP\n' "$R" "$N"; FAILED=1
else
  ok "bootstrap/cache is empty"
fi

[ -f "$STAGE/vendor/autoload.php" ] || { printf '  %sFAIL%s vendor/autoload.php missing\n' "$R" "$N"; FAILED=1; }

# Confirm the autoloader really is authoritative. Anything that re-runs
# dump-autoload without the flag downgrades it silently, and the bundle then
# resolves classes by hitting the filesystem — slower, and it papers over
# namespace/directory case bugs that a Linux server will not forgive.
if grep -q 'setClassMapAuthoritative(true)' "$STAGE/vendor/composer/autoload_real.php" 2>/dev/null; then
  ok "autoloader is classmap-authoritative"
else
  printf '  %sFAIL%s autoloader is not classmap-authoritative\n' "$R" "$N"; FAILED=1
fi
[ -d "$STAGE/vendor/phpunit" ] && { printf '  %sFAIL%s dev package phpunit shipped\n' "$R" "$N"; FAILED=1; }

if [ "$FAILED" -ne 0 ]; then
  rm -f "$ZIP_PATH"
  die "Bundle rejected and archive deleted. Fix the above before uploading."
fi

# -----------------------------------------------------------------------------
#  Summary
# -----------------------------------------------------------------------------
FILE_COUNT="$(find "$STAGE" -type f | wc -l | tr -d ' ')"
ZIP_BYTES="$(wc -c < "$ZIP_PATH" | tr -d ' ')"
ZIP_MB="$(awk -v b="$ZIP_BYTES" 'BEGIN{printf "%.1f", b/1048576}')"
TREE_MB="$(du -sm "$STAGE" 2>/dev/null | cut -f1)"

cat <<EOF

${B}Release bundle ready${N}
  archive     $ZIP_PATH
  size        ${ZIP_MB} MB compressed / ${TREE_MB:-?} MB on disk
  files       $FILE_COUNT
  unpacked    $STAGE

${B}Included${N}
  app/ bootstrap/ config/ database/ public/ resources/ routes/
  storage/            directory skeleton only, no data
  vendor/             freshly installed, --no-dev, classmap-authoritative
  artisan, composer.json, composer.lock, deploy.sh
  .env.production.example, DEPLOY.md, README.md
  scripts/preflight.sh  run this on the server before you open the site

${B}Excluded, and why${N}
  .env, .env.*             live credentials. Never leaves this machine.
  .git/                    full history, old secrets, 100s of MB.
  node_modules/            build-time only; this app ships no JS build step.
  tests/, phpunit.xml      dev-only, and a public test runner is an attack surface.
  dev packages in vendor/  phpunit/faker/pint/collision — never on a live box.
  bootstrap/cache/*.php    compiled against THIS machine's paths and DB. On the
                           server it overrides .env silently and the site behaves
                           as if your config edits did nothing.
  storage/logs/*.log       dev logs — MAIL_MAILER=log means these contain real
                           email verification codes.
  storage/framework/*      dev sessions, cached views and cache data.
  storage/app/public/*     uploads made on this dev machine.
  public/storage           symlink; recreate with 'php artisan storage:link'.
  *.md except DEPLOY/README, design PNGs, docker/, SIARC/, scripts/ (bar preflight),
  package.json, vite.config.js, .idea/, .vscode/, .DS_Store, Thumbs.db

${B}Next${N}
  1. Upload the zip (or rsync $STAGE/) to the server.
  2. Point the web root at ${B}public/${N} — see "What to upload" in DEPLOY.md.
  3. cp .env.production.example .env  &&  edit it
  4. php artisan key:generate && php artisan migrate --force && php artisan db:seed --force
  5. php artisan storage:link
  6. php artisan config:cache && php artisan route:cache && php artisan view:cache
  7. bash scripts/preflight.sh
EOF

if [ "${PSR4_BROKEN:-0}" -eq 1 ]; then
  printf '\n%sDO NOT UPLOAD THIS BUNDLE YET%s\n' "$R" "$N"
  printf 'The PSR-4 mismatch reported in step [4/6] will take the site down on a\n'
  printf 'Linux server even though everything works on this Windows machine. Fix the\n'
  printf 'namespace/directory casing first, then re-run this script.\n'
  exit 2
fi
