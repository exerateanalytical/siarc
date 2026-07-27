#!/usr/bin/env bash
# =============================================================================
#  preflight.sh — run this ON THE SERVER, after uploading, before you tell
#  anyone the site exists.
#
#  WHY THIS EXISTS
#  Every one of these checks corresponds to a way a Laravel deploy goes wrong
#  quietly: the site loads, looks fine, and is either leaking credentials or
#  half-broken. In particular the .env-over-HTTP check catches the single most
#  common and most damaging mistake — pointing the domain at the project root
#  instead of public/, which serves your database password as a text file.
#
#  USAGE (from the app root on the server):
#      bash scripts/preflight.sh
#
#  Exit code 0 = safe to open. Non-zero = at least one hard check failed.
#  WARN lines are judgement calls, not blockers.
# =============================================================================
set -uo pipefail   # deliberately NOT -e: we want every check to run and report.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

PHP_BIN="${PHP_BIN:-php}"
command -v "$PHP_BIN" >/dev/null 2>&1 || {
  for c in /c/laragon/bin/php/php-8.3*/php.exe; do [ -x "$c" ] && PHP_BIN="$c" && break; done
}

if [ -t 1 ]; then
  R=$'\033[31m'; G=$'\033[32m'; Y=$'\033[33m'; B=$'\033[1m'; N=$'\033[0m'
else
  R=''; G=''; Y=''; B=''; N=''
fi

FAILS=0
WARNS=0
pass() { printf '  %s PASS %s %s\n' "$G" "$N" "$1"; }
fail() { printf '  %s FAIL %s %s\n' "$R" "$N" "$1"; [ $# -gt 1 ] && printf '         %s\n' "$2"; FAILS=$((FAILS+1)); }
warn() { printf '  %s WARN %s %s\n' "$Y" "$N" "$1"; [ $# -gt 1 ] && printf '         %s\n' "$2"; WARNS=$((WARNS+1)); }
section() { printf '\n%s%s%s\n' "$B" "$1" "$N"; }

# Read a value out of .env. Strips surrounding quotes and ` # inline comments`
# (space-hash only, so a '#' inside a password survives).
env_get() {
  [ -f "$ROOT/.env" ] || return 1
  sed -n "s/^[[:space:]]*$1[[:space:]]*=//p" "$ROOT/.env" | head -1 \
    | sed -e 's/[[:space:]]\+#.*$//' -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' \
          -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/"
}

printf '%sPreflight — Artisan Hub 237%s   %s\n' "$B" "$N" "$(date '+%Y-%m-%d %H:%M')"
printf 'root: %s\n' "$ROOT"

# -----------------------------------------------------------------------------
section "PHP"
# -----------------------------------------------------------------------------
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  fail "php not found on PATH" "Set PHP_BIN=/path/to/php and re-run."
else
  PHP_VER="$("$PHP_BIN" -r 'echo PHP_VERSION;')"
  if "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then
    pass "PHP $PHP_VER (>= 8.3)"
  else
    fail "PHP $PHP_VER is below the required 8.3"
  fi

  # The exact list DEPLOY.md promises the server will have.
  MISSING=""
  for ext in pdo_mysql mbstring openssl gd fileinfo zip bcmath; do
    "$PHP_BIN" -r "exit(extension_loaded('$ext') ? 0 : 1);" || MISSING="$MISSING $ext"
  done
  if [ -z "$MISSING" ]; then
    pass "extensions: pdo_mysql mbstring openssl gd fileinfo zip bcmath"
  else
    fail "missing PHP extensions:$MISSING" "Install them, then restart php-fpm / Apache."
  fi
fi

# -----------------------------------------------------------------------------
section "Environment"
# -----------------------------------------------------------------------------
if [ ! -f "$ROOT/.env" ]; then
  fail ".env is missing" "cp .env.production.example .env, fill it, then php artisan key:generate"
  APP_URL=""
else
  APP_KEY="$(env_get APP_KEY)"
  APP_ENV="$(env_get APP_ENV)"
  APP_DEBUG="$(env_get APP_DEBUG)"
  APP_URL="$(env_get APP_URL)"

  case "$APP_KEY" in
    base64:*) pass "APP_KEY set" ;;
    "")       fail "APP_KEY is empty" "php artisan key:generate" ;;
    *)        warn "APP_KEY is set but not in the usual base64: form" ;;
  esac

  [ "$APP_ENV" = "production" ] \
    && pass "APP_ENV=production" \
    || fail "APP_ENV=${APP_ENV:-unset}, expected production"

  # APP_DEBUG=true on a live site renders the full stack trace, including .env
  # values, to anyone who can trigger an exception. This is a hard fail.
  case "$APP_DEBUG" in
    false|0|"") pass "APP_DEBUG=false" ;;
    *)          fail "APP_DEBUG=$APP_DEBUG" "A stack trace page exposes your DB password. Set false." ;;
  esac

  if [ -z "$APP_URL" ]; then
    fail "APP_URL is empty" "Asset URLs, emailed links and the sitemap all derive from it."
  else
    case "$APP_URL" in
      https://*) ;;
      *) fail "APP_URL=$APP_URL is not https" ;;
    esac
    case "$APP_URL" in
      *localhost*|*127.0.0.1*|*.test*|*.local*)
        fail "APP_URL=$APP_URL still points at a development host" ;;
      https://*)
        pass "APP_URL=$APP_URL" ;;
    esac
  fi

  MAIL_MAILER="$(env_get MAIL_MAILER)"
  if [ "$MAIL_MAILER" = "log" ] || [ -z "$MAIL_MAILER" ]; then
    warn "MAIL_MAILER=${MAIL_MAILER:-unset}" \
         "Signup codes land in storage/logs/laravel.log, not in inboxes. Fine for day one, not for launch."
  else
    pass "MAIL_MAILER=$MAIL_MAILER"
    for v in MAIL_HOST MAIL_USERNAME MAIL_FROM_ADDRESS; do
      val="$(env_get $v)"
      { [ -z "$val" ] || [ "$val" = "REPLACE" ]; } && warn "$v is not filled in"
    done
  fi

  # A worker-less database queue swallows every notification email silently.
  QUEUE="$(env_get QUEUE_CONNECTION)"
  if [ "$QUEUE" = "database" ]; then
    warn "QUEUE_CONNECTION=database" "A 'php artisan queue:work' process MUST be running, or no notification email is ever sent."
  fi
fi

# -----------------------------------------------------------------------------
section "Permissions"
# -----------------------------------------------------------------------------
# Laravel writes sessions, cached views, compiled config and logs. If these are
# not writable by the web-server user the site 500s on the first request — and
# with APP_DEBUG=false you get a blank error page and no clue why.
for dir in storage bootstrap/cache; do
  if [ ! -d "$ROOT/$dir" ]; then
    fail "$dir/ does not exist"
  elif [ -w "$ROOT/$dir" ] && touch "$ROOT/$dir/.preflight-write-test" 2>/dev/null; then
    rm -f "$ROOT/$dir/.preflight-write-test"
    pass "$dir/ writable"
  else
    fail "$dir/ is not writable" "chmod -R 775 $dir && chown -R <web-user>:<web-group> $dir"
  fi
done
[ -e "$ROOT/public/storage" ] \
  && pass "public/storage link exists" \
  || warn "public/storage missing" "php artisan storage:link — uploaded images will 404 without it."

# -----------------------------------------------------------------------------
section "Database"
# -----------------------------------------------------------------------------
if [ ! -f "$ROOT/.env" ] || ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  fail "database checks skipped (no .env or no php)"
else
  if DB_OUT="$("$PHP_BIN" artisan db:show --json 2>&1)"; then
    pass "database connection works"
  else
    fail "cannot connect to the database" "$(printf '%s' "$DB_OUT" | tr -d '\r' | head -2 | tail -1)"
  fi

  if MIG_OUT="$("$PHP_BIN" artisan migrate:status 2>&1)"; then
    PENDING="$(printf '%s' "$MIG_OUT" | grep -ci 'pending' || true)"
    if [ "$PENDING" -eq 0 ]; then
      pass "all migrations applied"
    else
      fail "$PENDING migration(s) pending" "php artisan migrate --force"
    fi
  else
    fail "migrate:status failed" "$(printf '%s' "$MIG_OUT" | tr -d '\r' | head -2 | tail -1)"
  fi
fi

# -----------------------------------------------------------------------------
section "Live HTTP"
# -----------------------------------------------------------------------------
if ! command -v curl >/dev/null 2>&1; then
  warn "curl not installed — HTTP checks skipped" "These are the checks that catch a wrong web root. Install curl and re-run."
elif [ -z "${APP_URL:-}" ]; then
  fail "HTTP checks skipped — APP_URL is not set"
else
  BASE="${APP_URL%/}"

  HOME_CODE="$(curl -sS -o /dev/null -w '%{http_code}' -L --max-time 20 "$BASE/" 2>/dev/null || echo 000)"
  case "$HOME_CODE" in
    200) pass "GET $BASE/ -> 200" ;;
    000) fail "GET $BASE/ did not respond" "DNS, TLS or the web server is down." ;;
    *)   fail "GET $BASE/ -> $HOME_CODE" ;;
  esac

  # THE important one. If the document root is the project root instead of
  # public/, this returns 200 and the body is your database password, mail
  # credentials and APP_KEY. Anyone can fetch it; crawlers do it automatically.
  ENV_CODE="$(curl -sS -o /tmp/preflight-env.$$ -w '%{http_code}' --max-time 20 "$BASE/.env" 2>/dev/null || echo 000)"
  case "$ENV_CODE" in
    403|404)
      pass "GET $BASE/.env -> $ENV_CODE (not served)" ;;
    000)
      warn "GET $BASE/.env did not respond — could not verify" ;;
    *)
      fail "GET $BASE/.env -> $ENV_CODE — YOUR .env IS PUBLIC" \
           "The web root is wrong. Point it at public/. Then rotate DB password, APP_KEY and mail credentials — assume they are compromised."
      grep -qE 'APP_KEY|DB_PASSWORD' "/tmp/preflight-env.$$" 2>/dev/null \
        && printf '         %sConfirmed: the response body contains APP_KEY/DB_PASSWORD.%s\n' "$R" "$N"
      ;;
  esac
  rm -f "/tmp/preflight-env.$$"

  # Same class of mistake, different file: composer.lock tells an attacker every
  # package version you run, which is a shopping list of known CVEs.
  LOCK_CODE="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 "$BASE/composer.lock" 2>/dev/null || echo 000)"
  case "$LOCK_CODE" in
    403|404|000) pass "GET $BASE/composer.lock -> ${LOCK_CODE} (not served)" ;;
    *)           fail "GET $BASE/composer.lock -> $LOCK_CODE — project root is being served" ;;
  esac
fi

# -----------------------------------------------------------------------------
printf '\n%s──────────────────────────────────────────%s\n' "$B" "$N"
if [ "$FAILS" -eq 0 ]; then
  printf '%sPREFLIGHT PASSED%s  %d warning(s)\n' "$G" "$N" "$WARNS"
  printf 'Safe to open the site.\n'
  exit 0
else
  printf '%sPREFLIGHT FAILED%s  %d failure(s), %d warning(s)\n' "$R" "$N" "$FAILS" "$WARNS"
  printf 'Fix the FAIL lines above before making the site public.\n'
  exit 1
fi
