#!/bin/bash
# Proves the UI kit is actually in force: loads real pages and reports any form
# field still carrying its own bespoke styling instead of the shared classes.
#
# The kit lives in resources/views/pages/partials/ui-kit.blade.php and is the
# single definition of field height, border, radius, focus ring and font.
BASE="${BASE:-http://artisanatcameroun.test}"
MAILPIT="${MAILPIT:-http://127.0.0.1:8025}"
T=/c/Users/PC/AppData/Local/Temp/uicheck
mkdir -p "$T"; rm -f "$T"/*
STAMP=$(date +%s)
PW='Ui@Check1234'
PASSED=0; FAIL=0

ok()  { PASSED=$((PASSED+1)); printf '  \033[32mOK  \033[0m %s\n' "$1"; }
bad() { FAIL=$((FAIL+1)); printf '  \033[31mDRIFT\033[0m %s\n' "$1"; }

tok() { curl -s -c "$1" -b "$1" "$2" -o "$T/tk.html"
        grep -o 'name="_token" value="[^"]*"' "$T/tk.html" | head -1 | sed 's/.*value="//;s/"//'; }

# php is not on PATH in a default Laragon shell. Resolve it once, here, and stop
# if it cannot be found: this script's only shell-out to php is the role grant,
# and when that silently no-ops every admin page redirects to login and is
# reported as interface drift. A check that cannot run has to fail loudly rather
# than manufacture a finding.
PHP="${PHP:-$(command -v php || true)}"
if [ -z "$PHP" ]; then
  for c in /c/laragon/bin/php/php-*/php.exe /c/laragon/bin/php/php-*/php; do
    [ -x "$c" ] && PHP="$c" && break
  done
fi
if [ -z "$PHP" ] || ! "$PHP" -v >/dev/null 2>&1; then
  printf '[31mCannot find a working php binary.[0m Set PHP=/path/to/php and re-run.
' >&2
  exit 2
fi

# Take the throwaway accounts, and the shop one of them opens, back out of the
# database when the run ends — however it ends. Without this every run left a
# live "UI Check Atelier <stamp>" business behind, and 26 of them had piled up
# in the admin directory by the time anyone looked: real-looking rows an admin
# could open, linking to a public profile that 404s because the shop is a draft.
# A check that inspects the product has no business enlarging it.
#
# Two sweeps, both confined to this script's own `uicheck-` accounts. The first
# is this run, matched on $STAMP. The second clears uicheck- strays left by an
# earlier run that crashed before its trap could fire. Neither touches the
# smoke test's seller/buyer accounts, which share the @e2e.test domain but own
# a real quote-to-invoice chain that the admin console reads.
# `\$` (not `\\\$`): inside a double-quoted bash string that collapses to a
# literal `$` for PHP, which is what grant_role below already relies on.
cleanup() {
  "$PHP" artisan tinker --execute="
    \$ids = \DB::table('users')
        ->where('email','like','uicheck-%@e2e.test')
        ->where(function (\$q) {
            \$q->where('email','like','%-${STAMP}@e2e.test')
               ->orWhere('created_at','<', now()->subHours(6));
        })->pluck('id');
    \DB::table('businesses')->whereIn('user_id', \$ids)->delete();
    \DB::table('users')->whereIn('id', \$ids)->delete();
    echo 'CLEANED ' . \$ids->count();
  " 2>/dev/null | grep -q CLEANED || printf '\033[33mCleanup of this run'"'"'s throwaway accounts failed — check for uicheck-*@e2e.test rows.\033[0m\n' >&2
}
trap cleanup EXIT

# Sign up a throwaway account rather than relying on demo logins. The platform
# ships with no demo accounts at all now, so a script that needed them would
# only ever work on a developer's machine.
signup() { # signup <jar> <slot> <phone-prefix>
  local jar="$1" slot="$2" pfx="$3" tk mail
  rm -f "$jar"
  mail="uicheck-${slot}-${STAMP}@e2e.test"
  tk=$(tok "$jar" "$BASE/creer-mon-compte")
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/creer-mon-compte" \
    --data-urlencode "_token=$tk" --data-urlencode "first_name=UI" \
    --data-urlencode "last_name=$slot" --data-urlencode "email=$mail" \
    --data-urlencode "phone=+237${pfx}${STAMP: -7}" \
    --data-urlencode "password=$PW" --data-urlencode "password_confirmation=$PW" \
    --data-urlencode "account_type=artisan" --data-urlencode "lang=fr" -o /dev/null
  printf '%s' "$mail"
}

# Verify the address using the code that really lands in the mailbox, so the
# account can pass the verified.email gate on business/product pages.
verify_email() { # verify_email <jar> <address>
  local jar="$1" addr="$2" tk id otp tries=0
  tk=$(tok "$jar" "$BASE/verification-email")
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/verification-email/envoyer" --data-urlencode "_token=$tk" -o /dev/null
  while [ $tries -lt 8 ]; do
    id=$(curl -s --get "$MAILPIT/api/v1/search" --data-urlencode "query=to:$addr" --data-urlencode "limit=1" \
         | grep -o '"ID":"[^"]*"' | head -1 | sed 's/.*:"//;s/"//')
    [ -n "$id" ] && break
    sleep 1; tries=$((tries+1))
  done
  [ -z "$id" ] && return 1
  otp=$(curl -s "$MAILPIT/view/$id.txt" | grep -oE '[0-9]{6}' | head -1)
  [ -z "$otp" ] && return 1
  tk=$(tok "$jar" "$BASE/verification-email")
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/verification-email/confirmer" \
    --data-urlencode "_token=$tk" --data-urlencode "code=$otp" -o /dev/null
}

# Grant a role by writing the pivot directly. Spatie's assignRole() resolves
# against the model's default guard, but this platform registers its roles under
# `sanctum`, so the helper silently no-ops here.
grant_role() { # grant_role <email> <role>
  "$PHP" artisan tinker --execute="
    \$u = \DB::table('users')->where('email','$1')->value('id');
    \$r = \DB::table('roles')->where('name','$2')->where('guard_name','sanctum')->value('id');
    if (\$u && \$r) {
      \DB::table('model_has_roles')->updateOrInsert(
        ['role_id'=>\$r,'model_type'=>'App\\\\Modules\\\\Auth\\\\Models\\\\User','model_id'=>\$u], []
      );
    }
    echo (\$u && \$r) ? 'GRANTED' : 'MISSING';" 2>/dev/null | grep -q GRANTED || {
    printf '\033[31mCould not grant %s to %s.\033[0m Every admin page below would report false drift.\n' "$2" "$1" >&2
    exit 3
  }
}

# A seller with no shop is correctly redirected away from the product and
# business pages, so give this one a shop before sweeping them.
create_shop() { # create_shop <jar> <email>
  local jar="$1" mail="$2" tk ind
  tk=$(tok "$jar" "$BASE/tableau-de-bord/entreprise/creer")
  ind=$(grep -oE '<option value="[0-9]+"' "$T/tk.html" | head -1 | grep -oE '[0-9]+')
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/tableau-de-bord/entreprise/creer" \
    --data-urlencode "_token=$tk" --data-urlencode "industry_id=$ind" \
    --data-urlencode "business_name=UI Check Atelier $STAMP" \
    --data-urlencode "business_description=Boutique temporaire creee par la verification d'interface." \
    --data-urlencode "email=$mail" -o /dev/null
}

# Sign in fresh so a role granted after signup is picked up — the session
# snapshots the role at login. Starts from an empty jar on purpose: signup
# already left us authenticated, and GET /login then redirects to the dashboard,
# yielding no CSRF token and a silent 419.
relogin() { # relogin <jar> <email>
  local jar="$1" tk
  rm -f "$jar"
  tk=$(tok "$jar" "$BASE/login")
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/login" --data-urlencode "_token=$tk" \
    --data-urlencode "email=$2" --data-urlencode "password=$PW" -o /dev/null
}

# Bespoke field styling that the kit replaces. Any of these still sitting on an
# <input>/<select>/<textarea> means that field escaped the conversion.
DRIFT='border-gray-[0-9]|border-\[#E9E4D8\]|border-\[#E7E7E5\]|border-\[#E5E7E5\]|border-\[#E2DDD0\]|border-\[#E0DCD5\]|border-\[#E4E2DD\]|border-\[#EFEBE2\]|border-\[#EAE5D8\]|border-\[#CFC9BF\]|border-\[#C9CFC9\]|border-\[#EDEEED\]'

check() { # check <jar|-> <label> <path>
  local jar="$1" label="$2" path="$3" f
  f="$T/$(echo "$label$path" | tr '/?=&' '____').html"
  local code
  if [ "$jar" = "-" ]; then code=$(curl -s "$BASE$path" -o "$f" -w "%{http_code}")
  else code=$(curl -s -b "$jar" "$BASE$path" -o "$f" -w "%{http_code}"); fi

  if [ "$code" != "200" ]; then bad "[$label] $path — http $code"; return; fi
  if grep -qiE "whoops|Undefined variable|Call to undefined" "$f"; then bad "[$label] $path — page error"; return; fi

  # Count fields that still carry bespoke styling.
  local drifted
  drifted=$(grep -oE '<(input|select|textarea)[^>]*>' "$f" \
            | grep -vE 'type="hidden"' \
            | grep -cE "$DRIFT")
  if [ "$drifted" -gt 0 ]; then bad "[$label] $path — $drifted field(s) still bespoke"
  else ok "[$label] $path"; fi
}

printf '\n\033[1mPublic\033[0m\n'
for p in / /contact /login /creer-mon-compte /verification-certificat \
         /galerie/entreprises /galerie/produits /galerie/secteurs /faq /about; do
  check - public "$p"
done

printf '\n\033[1mSeller\033[0m\n'
SELLER=$(signup "$T/ck_v" seller 71); verify_email "$T/ck_v" "$SELLER"; create_shop "$T/ck_v" "$SELLER"
for p in /tableau-de-bord/entrepreneur /tableau-de-bord/produits /tableau-de-bord/produits/nouveau \
         /tableau-de-bord/entreprise/modifier /tableau-de-bord/entreprise/verification \
         /tableau-de-bord/devis /tableau-de-bord/commandes /tableau-de-bord/messages \
         /tableau-de-bord/profil /tableau-de-bord/securite /tableau-de-bord/notifications \
         /tableau-de-bord/support; do
  check "$T/ck_v" seller "$p"
done

printf '\n\033[1mBuyer\033[0m\n'
BUYER=$(signup "$T/ck_b" buyer 72); verify_email "$T/ck_b" "$BUYER"
for p in /tableau-de-bord/acheteur /tableau-de-bord/demandes /tableau-de-bord/commandes \
         /tableau-de-bord/messages /tableau-de-bord/sauvegardes /tableau-de-bord/profil; do
  check "$T/ck_b" buyer "$p"
done

printf '\n\033[1mAdmin\033[0m\n'
ADMIN=$(signup "$T/ck_a" admin 73)
grant_role "$ADMIN" super_admin
relogin "$T/ck_a" "$ADMIN"
for p in /tableau-de-bord/admin /tableau-de-bord/admin/utilisateurs /tableau-de-bord/admin/entreprises \
         /tableau-de-bord/admin/produits /tableau-de-bord/admin/parametres /tableau-de-bord/admin/rapports \
         /tableau-de-bord/admin/kyc /tableau-de-bord/admin/partenaires; do
  check "$T/ck_a" admin "$p"
done

printf '\n\033[1mRESULT: %d consistent, %d with drift\033[0m\n' "$PASSED" "$FAIL"
[ "$FAIL" = "0" ]
