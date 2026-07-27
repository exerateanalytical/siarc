#!/bin/bash
# Proves the UI kit is actually in force: loads real pages and reports any form
# field still carrying its own bespoke styling instead of the shared classes.
#
# The kit lives in resources/views/pages/partials/ui-kit.blade.php and is the
# single definition of field height, border, radius, focus ring and font.
BASE="${BASE:-http://artisanatcameroun.test}"
T=/c/Users/PC/AppData/Local/Temp/uicheck
mkdir -p "$T"; rm -f "$T"/*
PASS=0; FAIL=0

ok()  { PASS=$((PASS+1)); printf '  \033[32mOK  \033[0m %s\n' "$1"; }
bad() { FAIL=$((FAIL+1)); printf '  \033[31mDRIFT\033[0m %s\n' "$1"; }

login() { # login <jar> <demo-key>
  local jar="$1" key="$2" tk
  rm -f "$jar"
  tk=$(curl -s -c "$jar" -b "$jar" "$BASE/login" -o "$T/l.html"
       grep -o 'name="_token" value="[^"]*"' "$T/l.html" | head -1 | sed 's/.*value="//;s/"//')
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/demo-login/$key" --data-urlencode "_token=$tk" -o /dev/null
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
login "$T/ck_v" vendor
for p in /tableau-de-bord/entrepreneur /tableau-de-bord/produits /tableau-de-bord/produits/nouveau \
         /tableau-de-bord/entreprise/modifier /tableau-de-bord/entreprise/verification \
         /tableau-de-bord/devis /tableau-de-bord/commandes /tableau-de-bord/messages \
         /tableau-de-bord/profil /tableau-de-bord/securite /tableau-de-bord/notifications \
         /tableau-de-bord/support; do
  check "$T/ck_v" seller "$p"
done

printf '\n\033[1mBuyer\033[0m\n'
login "$T/ck_b" buyer
for p in /tableau-de-bord/acheteur /tableau-de-bord/demandes /tableau-de-bord/commandes \
         /tableau-de-bord/messages /tableau-de-bord/sauvegardes /tableau-de-bord/profil; do
  check "$T/ck_b" buyer "$p"
done

printf '\n\033[1mAdmin\033[0m\n'
login "$T/ck_a" admin
for p in /tableau-de-bord/admin /tableau-de-bord/admin/utilisateurs /tableau-de-bord/admin/entreprises \
         /tableau-de-bord/admin/produits /tableau-de-bord/admin/parametres /tableau-de-bord/admin/rapports \
         /tableau-de-bord/admin/verifications /tableau-de-bord/admin/partenaires; do
  check "$T/ck_a" admin "$p"
done

printf '\n\033[1mRESULT: %d consistent, %d with drift\033[0m\n' "$PASS" "$FAIL"
[ "$FAIL" = "0" ]
