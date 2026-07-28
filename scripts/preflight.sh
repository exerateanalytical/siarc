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

  # ---------------------------------------------------------------------------
  # Extensions. This list is derived from what the code actually calls, not
  # from a generic Laravel checklist — each entry names the file that breaks
  # without it, so a future reader can re-verify the claim instead of trusting
  # it. `grep -rhoE 'sodium_[a-z_]+' app/` and friends reproduce the derivation.
  # ---------------------------------------------------------------------------
  ext_missing() { ! "$PHP_BIN" -r "exit(extension_loaded('$1') ? 0 : 1);"; }

  # HARD — the site is broken, or a whole module is, without these.
  #   sodium     app/Support/CertificationAuthority.php signs every certificate
  #              with Ed25519 (sodium_crypto_sign_detached / _keypair /
  #              _verify_detached). No sodium, no certificate module at all —
  #              issuing, the public verification page and revocation all fail.
  #   gd         app/Support/ImageWatermark.php, ImageFingerprint.php,
  #              ProductCertificate.php and the two upload services
  #              (imagecreatetruecolor, imagecopyresampled, imagecolorat …).
  #   bcmath     app/Support/PlatformFees.php uses bccomp for money comparison.
  #   pdo_mysql  the only database driver configured.
  #   mbstring   Laravel framework baseline; used throughout for UTF-8 slugs.
  #   openssl    APP_KEY encryption, HTTPS clients, and SMTP over SSL/TLS.
  #   fileinfo   UploadedFile::getMimeType() — every image upload is validated
  #              through it, so without fileinfo no member can upload anything.
  #   iconv      used directly in app/ for transliteration.
  #   ctype/json/tokenizer/dom/xml/curl  framework baseline.
  HARD_EXT="sodium gd bcmath pdo_mysql mbstring openssl fileinfo iconv ctype json tokenizer dom xml curl"
  MISSING=""
  for ext in $HARD_EXT; do ext_missing "$ext" && MISSING="$MISSING $ext"; done
  if [ -z "$MISSING" ]; then
    pass "required extensions present ($(echo $HARD_EXT | tr ' ' ','))"
  else
    fail "MISSING REQUIRED PHP EXTENSIONS:$MISSING" \
         "cPanel → Select PHP Version → Extensions, tick them, then wait ~1 min for php-fpm to recycle."
  fi

  # Called out separately because it is the one people forget and the failure
  # is not a 500 — certificates simply stop being issuable, quietly.
  if ext_missing sodium; then
    fail "sodium is NOT loaded — the certificate module cannot work" \
         "Ed25519 signing (CertificationAuthority) has no fallback. Enable ext-sodium before launch."
  else
    "$PHP_BIN" -r 'exit(function_exists("sodium_crypto_sign_detached") ? 0 : 1);' \
      && pass "sodium loaded and sodium_crypto_sign_detached callable" \
      || fail "sodium is loaded but sodium_crypto_sign_detached is missing" "Unusual build; certificates will fail."
  fi

  # Uploads are re-encoded to .webp (ImageUploadService), so a GD without WebP
  # support passes extension_loaded('gd') and then throws on every single image.
  if ! ext_missing gd; then
    GD_CAPS="$("$PHP_BIN" -r '$i=gd_info(); $m=[]; foreach(["WebP Support"=>"webp","JPEG Support"=>"jpeg","PNG Support"=>"png"] as $k=>$v){ if(empty($i[$k])) $m[]=$v; } echo implode(" ", $m);' 2>/dev/null)"
    if [ -z "$GD_CAPS" ]; then
      pass "gd has webp, jpeg and png support"
    else
      fail "gd is missing:$GD_CAPS support" \
           "Uploads are stored as .webp — without WebP support every logo, cover and product photo fails to save."
    fi
  fi

  # SOFT — real dependencies, but nothing a launched site needs on the hot path.
  #   zip   only app/Console/Commands/ImportSiarcArtisans.php (reads the .xlsx).
  #         The 510 artisans ship in the SQL import, so production does not need
  #         it unless the roster is ever re-imported from a spreadsheet.
  #   intl  NOTE: grepped for and NOT found — no NumberFormatter, IntlDateFormatter,
  #         Collator or \Locale call exists anywhere in app/, routes/, database/
  #         or config/. Listed as a nicety (Carbon can use it for localised dates)
  #         and never as a requirement, because claiming otherwise would send the
  #         owner chasing an extension nothing reads.
  for pair in "zip:re-importing the SIARC roster from .xlsx" "intl:nothing in this codebase calls it; Carbon uses it for localised dates if present"; do
    e="${pair%%:*}"; why="${pair#*:}"
    ext_missing "$e" && warn "$e not loaded — optional ($why)" || pass "$e loaded (optional)"
  done

  # Publishing a product is the one thing the platform exists for, and a 2M
  # default silently discards phone photos before Laravel ever sees them — the
  # artisan is told the image field is required for a file they did attach.
  # These ini values carry a unit suffix (2M, 1G, 512K). Stripping non-digits
  # turns "2G" into 2, which reads as a failure on a perfectly fine server —
  # so convert through the suffix rather than guessing.
  ini_mb() {
    "$PHP_BIN" -r '
      $v = trim(ini_get($argv[1]));
      $n = (float) $v;
      $u = strtoupper(substr($v, -1));
      if ($u === "G") $n *= 1024; elseif ($u === "K") $n /= 1024;
      elseif (ctype_digit(substr($v, -1))) $n /= 1048576;  // plain bytes
      echo (int) $n;
    ' "$1" 2>/dev/null
  }
  UPLOAD_MB="$(ini_mb upload_max_filesize)"
  POST_MB="$(ini_mb post_max_size)"

  if [ "${UPLOAD_MB:-0}" -ge 16 ] 2>/dev/null; then
    pass "upload_max_filesize ${UPLOAD_MB}M"
  else
    fail "upload_max_filesize is ${UPLOAD_MB:-?}M — phone photos will be rejected" \
         "Set upload_max_filesize = 16M and post_max_size = 20M (see DEPLOY.md section 1)."
  fi

  # Equal is fine and common; only a smaller post_max_size is a real fault,
  # because the file plus the rest of the form must fit inside it.
  if [ "${POST_MB:-0}" -ge "${UPLOAD_MB:-0}" ] 2>/dev/null; then
    pass "post_max_size ${POST_MB}M"
  else
    fail "post_max_size (${POST_MB:-?}M) is below upload_max_filesize (${UPLOAD_MB:-?}M)" \
         "The file is sent with the rest of the form, so the whole request is dropped. Set post_max_size higher."
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
# public/storage is where every uploaded logo, cover and product photo is
# served from. `storage:link` makes it a symlink; some shared hosts disable
# symlink() entirely, in which case the accepted fallback is a real directory
# bind-mounted or rsynced from storage/app/public. Either is fine — what
# matters is that a file written to storage/app/public is readable through
# public/storage. So prove it end-to-end rather than checking for a symlink.
mkdir -p "$ROOT/storage/app/public" 2>/dev/null
PROBE=".preflight-storage-probe"
if printf 'ok' > "$ROOT/storage/app/public/$PROBE" 2>/dev/null; then
  if [ ! -e "$ROOT/public/storage" ]; then
    fail "public/storage does not exist" \
         "php artisan storage:link. If the host forbids symlinks, see docs/DEPLOYMENT.md step 9 for the directory fallback."
  elif [ "$(cat "$ROOT/public/storage/$PROBE" 2>/dev/null)" = "ok" ]; then
    if [ -L "$ROOT/public/storage" ]; then
      pass "public/storage -> storage/app/public (symlink, verified by round-trip)"
    else
      pass "public/storage resolves to storage/app/public (directory fallback, verified by round-trip)"
    fi
  else
    fail "public/storage exists but does not resolve to storage/app/public" \
         "A file written to storage/app/public was not readable through public/storage. Every uploaded image will 404. Delete public/storage and re-run php artisan storage:link."
  fi
  rm -f "$ROOT/storage/app/public/$PROBE"
else
  fail "storage/app/public is not writable" "Uploads cannot be saved at all."
fi

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

  # Did the production import actually land? Each of these is a table the app
  # reads on a path a visitor hits within the first minute, and each of them is
  # silently empty-able: an empty taxonomy renders a browse page with zero
  # tiles and an "industry" dropdown nobody can submit; PlatformFees THROWS
  # (DomainException, by design — it refuses to invent a price) if no active
  # subscription plan exists, which takes down signup entirely.
  dbq() { "$PHP_BIN" -r '
      require "vendor/autoload.php";
      $app = require "bootstrap/app.php";
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
      try { echo (int) Illuminate\Support\Facades\DB::selectOne($argv[1])->c; }
      catch (Throwable $e) { echo "ERR"; }
    ' "$1" 2>/dev/null; }

  N_IND="$(dbq 'SELECT COUNT(*) c FROM industries')"
  [ "${N_IND:-0}" -ge 400 ] 2>/dev/null \
    && pass "craft taxonomy loaded ($N_IND rows)" \
    || fail "craft taxonomy has ${N_IND:-0} rows, expected 413" \
            "Import database/production/artisanhub237-production.sql. Browse pages and the trade picker are empty without it."

  N_PLAN="$(dbq 'SELECT COUNT(*) c FROM subscription_plans WHERE is_active = 1')"
  [ "${N_PLAN:-0}" -ge 1 ] 2>/dev/null \
    && pass "$N_PLAN active subscription plan(s)" \
    || fail "no active subscription plan" \
            "app/Support/PlatformFees.php throws rather than inventing a price — signup and every payment page will 500."

  N_REG="$(dbq 'SELECT COUNT(*) c FROM regions')"
  [ "${N_REG:-0}" -ge 10 ] 2>/dev/null \
    && pass "regions loaded ($N_REG)" || fail "regions table has ${N_REG:-0} rows, expected 10"

  N_SIARC="$(dbq 'SELECT COUNT(*) c FROM businesses WHERE siarc_code IS NOT NULL')"
  [ "${N_SIARC:-0}" -ge 1 ] 2>/dev/null \
    && pass "SIARC founding directory present ($N_SIARC artisans)" \
    || warn "no SIARC artisans" "The directory will be empty on launch day."

  # The one thing that must never be true on a live marketplace.
  N_DEMO="$(dbq 'SELECT COUNT(*) c FROM businesses WHERE is_demo = 1')"
  N_REV="$(dbq 'SELECT COUNT(*) c FROM business_reviews')"
  if [ "${N_DEMO:-0}" = "0" ] && [ "${N_REV:-0}" = "0" ]; then
    pass "no demo artisan, no fabricated reviews"
  else
    fail "found $N_DEMO demo business(es) and $N_REV review(s)" \
         "Fabricated reviews on a live marketplace are a consumer-deception problem. Re-import from the production export."
  fi

  # An admin account is created on the server, not shipped in the export.
  N_ADMIN="$(dbq "SELECT COUNT(*) c FROM model_has_roles m JOIN roles r ON r.id = m.role_id WHERE r.name = 'super_admin'")"
  [ "${N_ADMIN:-0}" -ge 1 ] 2>/dev/null \
    && pass "$N_ADMIN administrator account(s)" \
    || fail "no super_admin account exists" \
            "Set ADMIN_EMAIL/ADMIN_PASSWORD in .env, then: php artisan db:seed --class=\"Database\\Seeders\\Siac\\SiacAdminSeeder\""

  # A database queue with nothing draining it swallows every notification.
  N_FAILED="$(dbq 'SELECT COUNT(*) c FROM failed_jobs')"
  [ "${N_FAILED:-0}" = "0" ] \
    && pass "no failed queue jobs" \
    || warn "$N_FAILED failed queue job(s)" "php artisan queue:failed to inspect. A growing count means the worker cron is misconfigured."
fi

# -----------------------------------------------------------------------------
section "Mail (real SMTP handshake)"
# -----------------------------------------------------------------------------
# Signup issues a 6-digit OTP by email and the `verified.email` middleware
# blocks business creation, publishing and messaging until it is confirmed. If
# SMTP is wrong, nobody who registers can ever do anything — and the failure is
# invisible from the browser. So actually open the socket, speak EHLO, and (if
# credentials are configured) authenticate. Nothing is sent.
if [ ! -f "$ROOT/.env" ] || ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  warn "mail checks skipped (no .env or no php)"
else
  M_MAILER="$(env_get MAIL_MAILER)"
  M_HOST="$(env_get MAIL_HOST)"
  M_PORT="$(env_get MAIL_PORT)"
  M_ENC="$(env_get MAIL_SCHEME)"; [ -z "$M_ENC" ] && M_ENC="$(env_get MAIL_ENCRYPTION)"
  M_USER="$(env_get MAIL_USERNAME)"
  M_PASS="$(env_get MAIL_PASSWORD)"
  M_FROM="$(env_get MAIL_FROM_ADDRESS)"

  if [ "$M_MAILER" != "smtp" ]; then
    warn "MAIL_MAILER=${M_MAILER:-unset} — SMTP handshake skipped" \
         "With 'log', verification codes go to storage/logs/laravel.log and no member ever receives one."
  elif [ -z "$M_HOST" ]; then
    fail "MAIL_MAILER=smtp but MAIL_HOST is empty"
  else
    [ -z "$M_PORT" ] && M_PORT=465
    # Port 465 is implicit TLS from the first byte (ssl://). 587 is plaintext
    # then STARTTLS. Getting this backwards is the usual cause of a hang.
    case "$M_PORT" in 465) SCHEME="ssl" ;; *) SCHEME="tcp" ;; esac

    # Credentials go in via the environment, never argv — argv is world-readable
    # through `ps` on a shared host, which is exactly where this runs.
    SMTP_OUT="$(SMTP_HOST="$M_HOST" SMTP_PORT="$M_PORT" SMTP_SCHEME="$SCHEME" \
                SMTP_USER="$M_USER" SMTP_PASS="$M_PASS" \
      "$PHP_BIN" -r '
      $host = getenv("SMTP_HOST"); $port = (int) getenv("SMTP_PORT");
      $scheme = getenv("SMTP_SCHEME"); $user = getenv("SMTP_USER"); $pass = getenv("SMTP_PASS");
      $ctx = stream_context_create(["ssl" => ["verify_peer" => true, "verify_peer_name" => true]]);
      $fp = @stream_socket_client("$scheme://$host:$port", $errno, $errstr, 12,
              STREAM_CLIENT_CONNECT, $ctx);
      if (! $fp) { echo "CONNECT_FAIL|$errstr ($errno)"; exit; }
      stream_set_timeout($fp, 12);
      $read = function () use ($fp) {
          $out = "";
          while (($l = fgets($fp, 1024)) !== false) { $out .= $l; if (! isset($l[3]) || $l[3] !== "-") break; }
          return trim($out);
      };
      $say = function ($c) use ($fp, $read) { fwrite($fp, $c . "\r\n"); return $read(); };
      $greet = $read();
      if (strncmp($greet, "220", 3) !== 0) { echo "GREET_FAIL|$greet"; exit; }
      $ehlo = $say("EHLO artisanhub237.com");
      if (strncmp($ehlo, "250", 3) !== 0) { echo "EHLO_FAIL|$ehlo"; exit; }
      if ($scheme === "tcp" && stripos($ehlo, "STARTTLS") !== false) {
          $r = $say("STARTTLS");
          if (strncmp($r, "220", 3) !== 0) { echo "STARTTLS_FAIL|$r"; exit; }
          if (! @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { echo "TLS_FAIL|handshake refused"; exit; }
          $ehlo = $say("EHLO artisanhub237.com");
      }
      if ($user === "" || $pass === "") { $say("QUIT"); echo "OK_NOAUTH|$greet"; exit; }
      if (stripos($ehlo, "AUTH") === false) { $say("QUIT"); echo "NOAUTH_OFFERED|server advertises no AUTH"; exit; }
      $r = $say("AUTH LOGIN");
      if (strncmp($r, "334", 3) !== 0) { $say("QUIT"); echo "AUTH_UNSUPPORTED|$r"; exit; }
      $r = $say(base64_encode($user));
      if (strncmp($r, "334", 3) !== 0) { $say("QUIT"); echo "AUTH_FAIL|$r"; exit; }
      $r = $say(base64_encode($pass));
      $say("QUIT"); fclose($fp);
      echo (strncmp($r, "235", 3) === 0) ? "OK_AUTH|authenticated as $user" : "AUTH_FAIL|$r";
    ' 2>&1)"

    SMTP_CODE="${SMTP_OUT%%|*}"; SMTP_MSG="${SMTP_OUT#*|}"
    case "$SMTP_CODE" in
      OK_AUTH)   pass "SMTP $SCHEME://$M_HOST:$M_PORT — connected and authenticated" ;;
      OK_NOAUTH) warn "SMTP $SCHEME://$M_HOST:$M_PORT — connected, but MAIL_USERNAME/MAIL_PASSWORD are empty" \
                      "The relay will almost certainly reject the send. Fill them in and re-run." ;;
      CONNECT_FAIL)
        fail "cannot open $SCHEME://$M_HOST:$M_PORT" \
             "$SMTP_MSG — check the host name, the port (465 = SSL, 587 = STARTTLS), and that outbound SMTP is not firewalled." ;;
      AUTH_FAIL|AUTH_UNSUPPORTED|NOAUTH_OFFERED)
        fail "SMTP authentication failed at $M_HOST:$M_PORT" \
             "$SMTP_MSG — no member will ever receive a verification code. Re-check MAIL_USERNAME (usually the full address) and MAIL_PASSWORD." ;;
      *)
        fail "SMTP handshake failed at $M_HOST:$M_PORT" "$SMTP_CODE: $SMTP_MSG" ;;
    esac

    # cPanel mail servers reject a From: that is not a mailbox they host.
    case "$M_FROM" in
      ""|REPLACE*) fail "MAIL_FROM_ADDRESS is not set" "Every send will be rejected." ;;
      *@*) pass "MAIL_FROM_ADDRESS=$M_FROM" ;;
      *)   fail "MAIL_FROM_ADDRESS=$M_FROM is not an address" ;;
    esac
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
