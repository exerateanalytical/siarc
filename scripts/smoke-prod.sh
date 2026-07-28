#!/usr/bin/env bash
# =============================================================================
#  smoke-prod.sh — post-deploy verification against the LIVE site.
#
#  RELATIONSHIP TO scripts/smoke-e2e.sh
#  smoke-e2e.sh proves the whole write path — it signs two accounts up, reads
#  their real OTP out of Mailpit, creates a business, publishes a product, runs
#  an RFQ through to an invoice. That is the right test for a dev box and the
#  wrong one for production, for two independent reasons:
#
#    1. Mailpit does not exist on the server. The OTP it reads to get past the
#       `verified.email` middleware is unobtainable, so every check downstream
#       of email verification would be untestable.
#    2. Even if it were obtainable, the test would leave two fake members, a
#       fake workshop, a fake product and a fake invoice in the live directory
#       of a marketplace whose entire proposition is that its listings are real.
#
#  So this script is READ-ONLY. It creates nothing, posts nothing, and sends no
#  email. It proves the site is up, correctly configured, not leaking, and
#  serving the data that was imported. What it cannot prove is stated out loud
#  as SKIP lines rather than quietly omitted — a check that silently disappears
#  is worse than one that fails.
#
#  USAGE
#      bash scripts/smoke-prod.sh                          # https://artisanhub237.com
#      bash scripts/smoke-prod.sh https://staging.example  # somewhere else
#
#      # optional: also sweep the signed-in surfaces, using an account YOU
#      # already created and verified by hand. No account is ever created here.
#      SMOKE_EMAIL=you@example.com SMOKE_PASSWORD='…' bash scripts/smoke-prod.sh
#
#  SMOKE_PASSWORD is read from the environment and never written to disk, never
#  echoed, and never passed on a command line.
#
#  Exit 0 = every hard check passed.
# =============================================================================
set -uo pipefail

BASE="${1:-${SMOKE_BASE:-https://artisanhub237.com}}"
BASE="${BASE%/}"
T="${TMPDIR:-/tmp}/smoke-prod-$$"
mkdir -p "$T"
trap 'rm -rf "$T"' EXIT

if [ -t 1 ]; then R=$'\033[31m'; G=$'\033[32m'; Y=$'\033[33m'; C=$'\033[36m'; B=$'\033[1m'; N=$'\033[0m'
else R=''; G=''; Y=''; C=''; B=''; N=''; fi

PASSED=0; FAILED=0; SKIPPED=0; WARNED=0
say()  { printf '\n%s== %s%s\n' "$B" "$1" "$N"; }
ok()   { PASSED=$((PASSED+1));  printf '  %sPASS%s %s\n' "$G" "$N" "$1"; }
bad()  { FAILED=$((FAILED+1));  printf '  %sFAIL%s %s\n' "$R" "$N" "$1"; [ $# -gt 1 ] && printf '       %s\n' "$2"; }
warn() { WARNED=$((WARNED+1));  printf '  %sWARN%s %s\n' "$Y" "$N" "$1"; [ $# -gt 1 ] && printf '       %s\n' "$2"; }
skip() { SKIPPED=$((SKIPPED+1)); printf '  %sSKIP%s %s\n' "$C" "$N" "$1"; [ $# -gt 1 ] && printf '       %s\n' "$2"; }

command -v curl >/dev/null 2>&1 || { echo "curl is required."; exit 2; }

CURL=(curl -sS --max-time 25 --compressed)
# code <cookiejar|-> <path> <outfile>  → prints the HTTP status
code() {
  local ck="$1" path="$2" out="$3"
  if [ "$ck" = "-" ]; then "${CURL[@]}" -o "$out" -w '%{http_code}' "$BASE$path" 2>/dev/null || echo 000
  else "${CURL[@]}" -c "$ck" -b "$ck" -o "$out" -w '%{http_code}' "$BASE$path" 2>/dev/null || echo 000; fi
}
hdrs() { "${CURL[@]}" -D - -o /dev/null "$BASE$1" 2>/dev/null; }

printf '%sProduction smoke — Artisan Hub 237%s\n' "$B" "$N"
printf 'target: %s\n' "$BASE"
printf 'mode:   read-only (no writes, no signups, no email)\n'
printf 'date:   %s\n' "$(date '+%Y-%m-%d %H:%M:%S %z')"

# ─────────────────────────────────────────────────────────────────────────────
say "1. Reachability and TLS"
# ─────────────────────────────────────────────────────────────────────────────
HOME_CODE="$(code - / "$T/home.html")"
case "$HOME_CODE" in
  200) ok "GET / -> 200" ;;
  000) bad "GET / did not respond" "DNS, TLS or the web server is down. Nothing below will be meaningful." ;;
  *)   bad "GET / -> $HOME_CODE" ;;
esac

case "$BASE" in
  https://*)
    if "${CURL[@]}" -o /dev/null "$BASE/" 2>/dev/null; then ok "TLS certificate validates"
    else bad "TLS certificate does NOT validate" "curl refused the chain. Issue/renew the certificate in cPanel → SSL/TLS Status."; fi

    # A site that answers on http:// as well as https:// leaks session cookies
    # on the first request from anyone who types the bare domain.
    HTTP_LOC="$("${CURL[@]}" -o /dev/null -D - "${BASE/https:/http:}/" 2>/dev/null | tr -d '\r' | sed -n 's/^[Ll]ocation: //p' | head -1)"
    case "$HTTP_LOC" in
      https://*) ok "http:// redirects to $HTTP_LOC" ;;
      "")        warn "http:// does not redirect to https" "Add the redirect in cPanel → Domains → Force HTTPS Redirect." ;;
      *)         warn "http:// redirects to $HTTP_LOC (not https)" ;;
    esac
    ;;
  *) skip "TLS checks" "target is not https" ;;
esac

# The SecurityHeaders middleware runs on every response, so its absence means
# the request never reached Laravel — a rewrite or document-root problem.
H="$(hdrs /)"
for pair in "X-Content-Type-Options:nosniff" "X-Frame-Options:" "Referrer-Policy:" "Content-Security-Policy:"; do
  name="${pair%%:*}"
  if printf '%s' "$H" | grep -qi "^$name:"; then ok "response header $name present"
  else warn "response header $name missing" "app/Http/Middleware/SecurityHeaders.php sets it — if it is absent, requests may not be reaching Laravel."; fi
done
printf '%s' "$H" | grep -qi '^Set-Cookie:.*[Hh]ttp[Oo]nly' && ok "session cookie is HttpOnly" || warn "no HttpOnly session cookie observed on /"

# ─────────────────────────────────────────────────────────────────────────────
say "2. Nothing is exposed that should not be"
# ─────────────────────────────────────────────────────────────────────────────
# The single most damaging shared-hosting mistake is pointing the domain at the
# project root rather than public/, which serves .env — database password, mail
# password and APP_KEY — as plain text to anyone who asks.
for path in /.env /.env.production /composer.json /composer.lock /artisan /storage/logs/laravel.log \
            /database/production/artisanhub237-production.sql /.git/config /docker-compose.yml /phpunit.xml; do
  c="$(code - "$path" "$T/exp.html")"
  case "$c" in
    200) bad "GET $path -> 200 — THIS FILE IS PUBLIC" \
             "The web root is wrong. Point it at public/. If it was .env, treat the DB password, APP_KEY and mail password as compromised and rotate all three." ;;
    000) warn "GET $path — no response" ;;
    *)   ok "GET $path -> $c (not served)" ;;
  esac
done

# Debug mode is the other way .env leaks: one triggered exception renders every
# environment value into the browser.
c="$(code - "/this-page-does-not-exist-$$" "$T/404.html")"
if grep -qiE "Whoops|Stack trace|APP_KEY|DB_PASSWORD|Illuminate\\\\Foundation" "$T/404.html" 2>/dev/null; then
  bad "the error page renders a stack trace" "APP_DEBUG is true. Set APP_DEBUG=false, then php artisan config:cache."
else
  ok "error page is not a debug trace (GET unknown URL -> $c)"
fi

# Horizon is gated by a viewHorizon Gate, but the gate is only as good as the
# deployment — check it in the open rather than assuming.
c="$(code - /horizon "$T/hz.html")"
case "$c" in
  200) bad "GET /horizon -> 200 for an anonymous visitor" "The queue dashboard is public. Check app/Providers/HorizonServiceProvider.php." ;;
  *)   ok "GET /horizon -> $c (not public)" ;;
esac

# ─────────────────────────────────────────────────────────────────────────────
say "3. Public pages"
# ─────────────────────────────────────────────────────────────────────────────
# A 200 is not enough: with APP_DEBUG=false a Blade error can still render a
# 200 containing "Undefined variable". Check the body too, exactly as the dev
# smoke test does.
sweep() {
  local ck="$1" label="$2"; shift 2
  local p f c e
  for p in "$@"; do
    f="$T/sw_$(printf '%s' "$label$p" | tr '/?=&.' '______').html"
    c="$(code "$ck" "$p" "$f")"
    e="$(grep -ciE "whoops|undefined variable|undefined array key|call to undefined|ErrorException|Too few arguments|SQLSTATE" "$f" 2>/dev/null || echo 0)"
    if [ "$c" = "200" ] && [ "$e" = "0" ]; then ok "[$label] $p"
    else bad "[$label] $p (http=$c, error-markers=$e)"; fi
  done
}

sweep - public / /galerie/entreprises /galerie/produits /galerie/secteurs /galerie/recherche \
  /evenements /partenaires /actualites /about /contact /faq \
  /collections-heritage /centres-artisanat /guide-artisan /carrieres /presse \
  /terms /privacy /mentions-legales /disclaimer \
  /login /creer-mon-compte /inscription /forgot-password \
  /verification-certificat /verifier /autorite-de-certification /certificats-revoques \
  /proteger-mon-travail /apercu-securite

# ─────────────────────────────────────────────────────────────────────────────
say "4. SEO and machine endpoints"
# ─────────────────────────────────────────────────────────────────────────────
c="$(code - /robots.txt "$T/robots.txt")"
if [ "$c" = "200" ]; then
  if grep -qi "sitemap" "$T/robots.txt"; then ok "/robots.txt served and points at the sitemap"
  else warn "/robots.txt served but names no sitemap"; fi
  # A staging robots.txt copied to production hides the whole site from Google.
  grep -qiE "^\s*Disallow:\s*/\s*$" "$T/robots.txt" && bad "/robots.txt contains 'Disallow: /' — the entire site is blocked from search engines"
else bad "/robots.txt -> $c"; fi

c="$(code - /sitemap.xml "$T/sitemap.xml")"
if [ "$c" = "200" ] && grep -q "<urlset" "$T/sitemap.xml"; then
  URLS="$(grep -c "<loc>" "$T/sitemap.xml" || echo 0)"
  ok "/sitemap.xml served ($URLS URLs)"
  # APP_URL wrong is the classic: the sitemap then advertises localhost.
  if grep -qE "<loc>https?://(localhost|127\.0\.0\.1|[^<]*\.(test|local))" "$T/sitemap.xml"; then
    bad "the sitemap contains development URLs" "APP_URL is wrong in .env. Fix it and run php artisan config:cache."
  else ok "sitemap URLs are all non-development"; fi
else bad "/sitemap.xml -> $c"; fi

c="$(code - /.well-known/jwks.json "$T/jwks.json")"
if [ "$c" = "200" ] && grep -q '"keys"' "$T/jwks.json"; then
  ok "/.well-known/jwks.json served — the certificate authority has a published key"
else
  bad "/.well-known/jwks.json -> $c" \
      "Certificate verification depends on this. If it 500s, ext-sodium is almost certainly missing — run scripts/preflight.sh on the server."
fi

# ─────────────────────────────────────────────────────────────────────────────
say "5. The imported data is actually there"
# ─────────────────────────────────────────────────────────────────────────────
# Signup is the only place the whole reference-data chain is visible from the
# outside: the trade picker is rendered from `industries`, and the plan pricing
# comes from `subscription_plans` via PlatformFees, which THROWS rather than
# inventing a price. An empty taxonomy or a missing plan turns this page into a
# 500 or a form nobody can submit.
c="$(code - /creer-mon-compte "$T/signup.html")"
if [ "$c" = "200" ]; then
  OPTS="$(grep -oE '<option[^>]*value="[0-9]+"' "$T/signup.html" | wc -l | tr -d ' ')"
  if [ "${OPTS:-0}" -gt 0 ]; then ok "signup form renders $OPTS taxonomy options"
  else warn "signup form renders no numeric <option> values" "The trade picker may load over AJAX; check /galerie/secteurs below before treating this as a fault."; fi
else bad "signup page -> $c"; fi

c="$(code - /galerie/secteurs "$T/sect.html")"
if [ "$c" = "200" ]; then
  TILES="$(grep -ociE "galerie/(entreprises|produits)\?[^\"']*(secteur|industry|metier)" "$T/sect.html" || echo 0)"
  [ "${TILES:-0}" -gt 0 ] \
    && ok "trade browse page is populated from the taxonomy" \
    || bad "trade browse page has no trade links" "The industries table is empty — import database/production/artisanhub237-production.sql."
fi

# The public directory is EMPTY BY DESIGN on launch day. All 510 imported SIARC
# artisans are status='draft' and unclaimed; only a claimed, published profile
# appears. Assert the page works, and report the count rather than demanding
# one, so this check does not turn into pressure to publish profiles nobody has
# consented to publishing.
c="$(code - /galerie/entreprises "$T/dir.html")"
if [ "$c" = "200" ]; then
  SHOPS="$(grep -oE 'galerie/entreprises/[a-z0-9-]+' "$T/dir.html" | sort -u | wc -l | tr -d ' ')"
  ok "public directory renders ($SHOPS published shop link(s))"
  [ "${SHOPS:-0}" = "0" ] && printf '       %sexpected on day one: the 510 SIARC profiles are drafts until each artisan claims and publishes.%s\n' "$C" "$N"
fi

# ─────────────────────────────────────────────────────────────────────────────
say "6. What this script deliberately does NOT test"
# ─────────────────────────────────────────────────────────────────────────────
skip "signup → email OTP → verified account" \
     "scripts/smoke-e2e.sh reads the OTP out of Mailpit on :8025. Mailpit does not exist in production and there is no other way to obtain a real code from outside. Verify mail with 'bash scripts/preflight.sh' on the server, which opens a real authenticated SMTP session."
skip "business creation, product publishing" \
     "Both write to the live directory. Do these once by hand from a real account and delete afterwards; do not automate writes against a public marketplace."
skip "RFQ → proposal → acceptance → purchase order → invoice" \
     "Same reason: every step of the quote flow is a live write. Covered by 'php artisan test' and by scripts/smoke-e2e.sh on staging."
skip "certificate issuance (Ed25519 signing)" \
     "Requires an authenticated seller and creates a signed artefact in the register. The /.well-known/jwks.json check above is the read-only proxy: if ext-sodium is missing it fails there first."
skip "outbound email delivery" \
     "Sending from a smoke test burns relay reputation and quota. preflight.sh authenticates against the relay without sending."
printf '       %sTo cover the skipped paths, run scripts/smoke-e2e.sh against a STAGING copy%s\n' "$C" "$N"
printf '       %sof this deployment (same code, same DB import, throwaway database).%s\n' "$C" "$N"

# ─────────────────────────────────────────────────────────────────────────────
say "7. Signed-in surfaces (optional)"
# ─────────────────────────────────────────────────────────────────────────────
if [ -z "${SMOKE_EMAIL:-}" ] || [ -z "${SMOKE_PASSWORD:-}" ]; then
  skip "authenticated page sweep" \
       "Set SMOKE_EMAIL and SMOKE_PASSWORD for an account you already created and verified by hand. This script never creates one."
else
  CK="$T/ck"
  code "$CK" /login "$T/login.html" >/dev/null
  TOKEN="$(grep -o 'name="_token" value="[^"]*"' "$T/login.html" | head -1 | sed 's/.*value="//;s/"//')"
  if [ -z "$TOKEN" ]; then
    bad "no CSRF token on /login" "The login form did not render. Session storage may be unwritable."
  else
    LOC="$("${CURL[@]}" -c "$CK" -b "$CK" -X POST "$BASE/login" \
            --data-urlencode "_token=$TOKEN" \
            --data-urlencode "email=$SMOKE_EMAIL" \
            --data-urlencode "password=$SMOKE_PASSWORD" \
            -o "$T/loginpost.html" -w '%{redirect_url}' 2>/dev/null)"
    case "$LOC" in
      *"/login/verification"*)
        skip "authenticated sweep — the account has two-factor enabled" \
             "The second factor is delivered out of band and cannot be read from here. Use an account without 2FA, or sweep by hand." ;;
      *"/login"*|"")
        bad "login was rejected for $SMOKE_EMAIL" \
            "Wrong credentials, an unverified email, or a suspended account. Nothing was created; no retry is made." ;;
      *)
        ok "logged in as $SMOKE_EMAIL (-> $LOC)"
        sweep "$CK" auth /tableau-de-bord/profil /tableau-de-bord/securite \
          /tableau-de-bord/notifications /tableau-de-bord/messages \
          /tableau-de-bord/demandes /tableau-de-bord/commandes \
          /tableau-de-bord/support /tableau-de-bord/revendiquer
        # Leave no session behind on the live site.
        code "$CK" /logout "$T/logout.html" >/dev/null 2>&1
        ;;
    esac
  fi
fi

# ─────────────────────────────────────────────────────────────────────────────
printf '\n%s──────────────────────────────────────────%s\n' "$B" "$N"
printf '%sRESULT%s  %s%d passed%s, %s%d failed%s, %s%d skipped%s, %s%d warnings%s\n' \
  "$B" "$N" "$G" "$PASSED" "$N" "$R" "$FAILED" "$N" "$C" "$SKIPPED" "$N" "$Y" "$WARNED" "$N"
if [ "$FAILED" -eq 0 ]; then
  printf 'No hard failure. Read the SKIP lines — they are the parts nobody has proven.\n'
  exit 0
fi
exit 1
