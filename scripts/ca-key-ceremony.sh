#!/usr/bin/env bash
# =============================================================================
#  ca-key-ceremony.sh — install the Certification Authority signing key
#                       ON THE PRODUCTION SERVER.
#
#  RUN THIS ON THE SERVER, from the application root (the directory holding
#  `artisan`), once, after the first deploy and before anyone is invited to
#  register a product.
#
#      bash scripts/ca-key-ceremony.sh
#
# -----------------------------------------------------------------------------
#  WHAT THE KEY IS
#
#  app/Support/CertificationAuthority.php holds an Ed25519 private key and makes
#  a detached signature over the certified facts of every certificate the
#  platform issues. The matching public key is published at
#  /.well-known/jwks.json in RFC 8037 JWK form, which is the whole point: a
#  museum, an insurer or a customs officer can verify a certificate offline,
#  years later, against a key they pinned themselves, without asking us and
#  without believing our answer.
#
#  That property rests entirely on one file:
#
#      storage/app/ca/ah237-ca.key      (base64 Ed25519 secret key)
#      storage/app/ca/ah237-ca.key.pub  (base64 Ed25519 public key)
#
#  It is gitignored (`/storage/app/ca` in .gitignore), it is excluded from the
#  release bundle, and it must never be committed, emailed, pasted into a chat
#  window, or placed anywhere under the web root. In the shared-hosting layout
#  the application root sits ABOVE public_html, so storage/app/ca is already
#  unreachable over HTTP — but only as long as nobody moves the app into
#  public_html "to make the paths simpler".
#
# -----------------------------------------------------------------------------
#  GENERATE ON THE SERVER, OR TRANSFER OUT-OF-BAND — PICK ONE
#
#  GENERATE ON THE SERVER (default, and what this script does)
#      The private key never exists anywhere else. Nothing to intercept. The
#      cost is that certificates signed by any earlier key stop verifying —
#      see the next section, which is the part people get wrong.
#
#  TRANSFER AN EXISTING KEY
#      Only if certificates already in the hands of third parties were signed
#      with it and must keep verifying. Move it as a file over scp/sftp — never
#      through email, a ticket, a chat message or a git branch — then:
#          mkdir -p storage/app/ca && chmod 700 storage/app/ca
#          # copy ah237-ca.key into place
#          chmod 600 storage/app/ca/ah237-ca.key
#          php artisan tinker --execute="echo App\Support\CertificationAuthority::kid();"
#      and check the kid printed matches the one the certificates carry. The
#      .pub file is regenerated from the secret key automatically, so it does
#      not need transferring.
#
# -----------------------------------------------------------------------------
#  WHAT HAPPENS TO CERTIFICATES ALREADY ISSUED ON THE DEV MACHINE
#
#  They do not verify. Not "might not" — cannot. The signature is checked
#  against the key the server holds, and a signature made by a different key
#  fails by construction.
#
#  Worse, they fail LOUDLY. Each register reports three signature states:
#
#      unsigned   ca_signature is NULL — "hash-verified, not signed". Benign.
#      valid      verifies against the published key.
#      invalid    a signature is present and does NOT verify.
#
#  A certificate row carried over from development lands in `invalid`, and
#  `invalid` is the single most alarming thing a provenance document can say:
#  it reads as "this record has been tampered with". Nothing has been tampered
#  with; it was simply signed by a key this server does not have.
#
#  So the exported database must not carry foreign signatures into production.
#  Two acceptable endings, in order of preference:
#
#    1. Do not export the rows. The certificates on the development machine are
#       demo-seed artefacts (SeedDemoArtisan). Production should start with an
#       empty certificate register and issue real ones under the real key.
#
#    2. Retire the signatures. If the rows must come across, NULL their
#       ca_signature and ca_kid so they report `unsigned` rather than `invalid`.
#       That is honest: the facts are still hash-verified, and the document no
#       longer claims a signature it cannot back up. This script will do it for
#       you with --retire-foreign-signatures.
#
#  What is NOT acceptable is re-signing old rows silently to make the red go
#  away. If a certificate is worth signing under the production authority, it
#  is worth re-issuing so the certificate_events chain records that it was.
#
#  The hash chain in `certificate_events` is unaffected either way: it is keyed
#  on SHA-256, not on the signing key, and survives the ceremony intact.
# =============================================================================
set -euo pipefail

RETIRE=0
FORCE=0
for arg in "$@"; do
  case "$arg" in
    --retire-foreign-signatures) RETIRE=1 ;;
    --force) FORCE=1 ;;
    -h|--help) sed -n '2,90p' "$0"; exit 0 ;;
    *) echo "Unknown option: $arg" >&2; exit 2 ;;
  esac
done

if [ -t 1 ]; then
  R=$'\033[31m'; G=$'\033[32m'; Y=$'\033[33m'; B=$'\033[1m'; N=$'\033[0m'
else
  R=''; G=''; Y=''; B=''; N=''
fi
step() { printf '\n%s==> %s%s\n' "$B" "$*" "$N"; }
ok()   { printf '  %sok%s   %s\n' "$G" "$N" "$*"; }
warn() { printf '  %swarn%s %s\n' "$Y" "$N" "$*"; }
die()  { printf '\n%sFATAL%s %s\n' "$R" "$N" "$*" >&2; exit 1; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

PHP_BIN="${PHP_BIN:-php}"
command -v "$PHP_BIN" >/dev/null 2>&1 || die "php not found. Set PHP_BIN=/path/to/php."

[ -f "$ROOT/artisan" ] || die "Run this from the application root (no ./artisan here)."
[ -f "$ROOT/.env" ]    || die "No .env. Copy .env.production to .env and fill it first."

grep -q '^APP_ENV=production' "$ROOT/.env" || warn "APP_ENV is not 'production' — is this really the live server?"

# -----------------------------------------------------------------------------
step "[1/6] Locate the key path"
# -----------------------------------------------------------------------------
KEY_PATH="$("$PHP_BIN" artisan tinker --execute="echo config('certificates.ca.key_path');" 2>/dev/null | tr -d '\r')"
[ -n "$KEY_PATH" ] || die "Could not read config('certificates.ca.key_path')."
ok "$KEY_PATH"

# The one check that matters more than any other: the key must not be inside a
# directory the web server will hand out.
case "$KEY_PATH" in
  */public_html/*|*/public/*)
    die "The key path is inside the web root. Anyone could download the private key. Move the application above public_html before continuing." ;;
esac
ok "path is outside the web root"

if [ -f "$KEY_PATH" ] && [ "$FORCE" -eq 0 ]; then
  warn "A signing key is already installed."
  warn "Replacing it invalidates every certificate signed with it."
  warn "If that is genuinely what you want: php artisan certificates:init-authority --force"
  EXISTING=1
else
  EXISTING=0
fi

# -----------------------------------------------------------------------------
step "[2/6] Generate the key pair"
# -----------------------------------------------------------------------------
if [ "$EXISTING" -eq 1 ]; then
  ok "skipped — using the key already present"
else
  "$PHP_BIN" artisan certificates:init-authority || die "Key generation failed."
fi

# -----------------------------------------------------------------------------
step "[3/6] Lock the permissions down"
# -----------------------------------------------------------------------------
# 0700 on the directory and 0600 on the key. On shared hosting the account is
# the only user that matters, but 'the only user that matters' is not the same
# as 'the only user', and cPanel accounts have historically been readable by
# the web server user across accounts on misconfigured boxes.
KEY_DIR="$(dirname "$KEY_PATH")"
chmod 700 "$KEY_DIR"      || warn "could not chmod 700 $KEY_DIR"
chmod 600 "$KEY_PATH"     || warn "could not chmod 600 $KEY_PATH"
[ -f "$KEY_PATH.pub" ] && chmod 644 "$KEY_PATH.pub"
ok "$(ls -l "$KEY_PATH" | awk '{print $1, $NF}')"

# A stray .htaccess costs nothing and closes the case where someone later moves
# the app under public_html and forgets this file exists.
if [ ! -f "$KEY_DIR/.htaccess" ]; then
  printf 'Require all denied\nDeny from all\n' > "$KEY_DIR/.htaccess" 2>/dev/null \
    && ok "wrote $KEY_DIR/.htaccess (belt and braces)" \
    || warn "could not write $KEY_DIR/.htaccess"
fi

# -----------------------------------------------------------------------------
step "[4/6] Read back the key identity"
# -----------------------------------------------------------------------------
KID="$("$PHP_BIN" artisan tinker --execute="echo App\Support\CertificationAuthority::kid();" 2>/dev/null | tr -d '\r')"
PUB="$("$PHP_BIN" artisan tinker --execute="echo App\Support\CertificationAuthority::publicKey();" 2>/dev/null | tr -d '\r')"
[ -n "$KID" ] || die "The authority reports no key. Generation did not take effect."
ok "kid        $KID"
ok "public key $PUB"
printf '\n  %sRecord these two values somewhere outside this server.%s\n' "$B" "$N"
printf '  They are what a third party pins in order to verify a certificate\n'
printf '  without trusting the site. Publishing them is fine and expected —\n'
printf '  it is the .key file, and only that file, which is secret.\n'

# -----------------------------------------------------------------------------
step "[5/6] Confirm /.well-known/jwks.json publishes the same key"
# -----------------------------------------------------------------------------
APP_URL="$(grep -E '^APP_URL=' "$ROOT/.env" | head -1 | cut -d= -f2- | tr -d '"'"'"' \r')"
APP_URL="${APP_URL%/}"
if command -v curl >/dev/null 2>&1 && [ -n "$APP_URL" ]; then
  JWKS="$(curl -fsSL --max-time 20 "$APP_URL/.well-known/jwks.json" 2>/dev/null || true)"
  if [ -z "$JWKS" ]; then
    warn "could not fetch $APP_URL/.well-known/jwks.json — check it by hand."
  elif printf '%s' "$JWKS" | grep -q "$KID"; then
    ok "published kid matches the installed key"
  else
    printf '  %sFAIL%s /.well-known/jwks.json does not carry kid %s.\n' "$R" "$N" "$KID"
    printf '        Almost always a stale config cache. Run:\n'
    printf '            php artisan config:clear && php artisan config:cache\n'
    printf '        Response was: %s\n' "$JWKS"
  fi
else
  warn "curl unavailable or APP_URL unset — open $APP_URL/.well-known/jwks.json yourself."
  printf '        It must contain: "kid":"%s"\n' "$KID"
fi

# -----------------------------------------------------------------------------
step "[6/6] Certificates signed by some other key"
# -----------------------------------------------------------------------------
# Anything whose ca_kid is not the installed kid was signed elsewhere — almost
# always carried in on a database export from a development machine. Left alone
# it renders as 'invalid', which reads to a visitor as "tampered with".
SCAN_PHP='
$kid = App\Support\CertificationAuthority::kid();
$tables = ["product_certificates","artisan_verifications","ownership_transfers","export_consignments","workshop_registrations"];
$total = 0;
foreach ($tables as $t) {
    try {
        if (! Schema::hasTable($t) || ! Schema::hasColumn($t, "ca_kid")) { continue; }
        $n = DB::table($t)->whereNotNull("ca_signature")->where(function ($q) use ($kid) {
            $q->whereNull("ca_kid")->orWhere("ca_kid", "!=", $kid);
        })->count();
        if ($n > 0) { echo $t . "=" . $n . PHP_EOL; $total += $n; }
    } catch (\Throwable $e) { /* table shape differs; skip */ }
}
echo "TOTAL=" . $total . PHP_EOL;
'
SCAN="$("$PHP_BIN" artisan tinker --execute="$SCAN_PHP" 2>/dev/null | tr -d '\r')"
FOREIGN="$(printf '%s' "$SCAN" | grep '^TOTAL=' | cut -d= -f2)"
FOREIGN="${FOREIGN:-0}"

if [ "$FOREIGN" -eq 0 ]; then
  ok "none — every stored signature was made by this key"
else
  printf '  %sfound %s certificate(s) signed by a different key:%s\n' "$Y" "$FOREIGN" "$N"
  printf '%s\n' "$SCAN" | grep -v '^TOTAL=' | sed 's/^/         /'
  printf '\n  Each of these will display as SIGNATURE INVALID on its verification\n'
  printf '  page. Nothing is wrong with the data — the signature was simply made\n'
  printf '  by a key this server does not hold.\n\n'
  if [ "$RETIRE" -eq 1 ]; then
    RETIRE_PHP='
    $kid = App\Support\CertificationAuthority::kid();
    $tables = ["product_certificates","artisan_verifications","ownership_transfers","export_consignments","workshop_registrations"];
    $n = 0;
    foreach ($tables as $t) {
        try {
            if (! Schema::hasTable($t) || ! Schema::hasColumn($t, "ca_kid")) { continue; }
            $n += DB::table($t)->whereNotNull("ca_signature")->where(function ($q) use ($kid) {
                $q->whereNull("ca_kid")->orWhere("ca_kid", "!=", $kid);
            })->update(["ca_signature" => null, "ca_kid" => null]);
        } catch (\Throwable $e) {}
    }
    echo "RETIRED=" . $n . PHP_EOL;
    '
    "$PHP_BIN" artisan tinker --execute="$RETIRE_PHP" | tr -d '\r' | sed 's/^/  /'
    ok "those rows now report 'unsigned' (hash-verified, not signed) instead of 'invalid'"
    printf '  Re-issue them under the production authority when you want a real\n'
    printf '  signature — do not hand-write one.\n'
  else
    printf '  Re-run with --retire-foreign-signatures to NULL ca_signature/ca_kid on\n'
    printf '  those rows, so they report "unsigned" instead of "invalid". Better\n'
    printf '  still: drop the demo certificate rows from the database export and\n'
    printf '  let production issue its own.\n'
  fi
fi

cat <<EOF

${B}Ceremony complete.${N}

  key         $KEY_PATH        (0600, above the web root, never in git)
  kid         $KID
  public      $PUB
  published   $APP_URL/.well-known/jwks.json

${B}Back the private key up now${N}, encrypted, somewhere that is not this server.
Losing it does not invalidate certificates already issued — the public key still
verifies them — but it does mean no certificate can ever be issued under this
authority again, and every new one would carry a different kid.
EOF
