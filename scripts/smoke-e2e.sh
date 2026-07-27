#!/bin/bash
# End-to-end proof against the running site, with real sessions and real writes.
# signup -> verify email (real OTP from the mailbox) -> business -> product ->
# RFQ -> proposal -> accept -> purchase order -> status -> invoice -> messaging.
BASE="http://artisanatcameroun.test"
MAILPIT="http://127.0.0.1:8025"
T="/c/Users/PC/AppData/Local/Temp/e2e"
mkdir -p "$T"; rm -f "$T"/*
STAMP=$(date +%s)
SELLER="seller${STAMP}@e2e.test"
BUYER="buyer${STAMP}@e2e.test"
PASS='E2e@Passw0rd!'
PASSED=0; FAILED=0
curl -s -o /dev/null "$BASE/" # warm the app

say()  { printf '\n\033[1m== %s\033[0m\n' "$1"; }
ok()   { PASSED=$((PASSED+1)); printf '  \033[32mPASS\033[0m %s\n' "$1"; }
bad()  { FAILED=$((FAILED+1)); printf '  \033[31mFAIL\033[0m %s\n' "$1"; }
check(){ if [ "$1" = "$2" ]; then ok "$3 ($1)"; else bad "$3 (got $1, want $2)"; fi; }

tok()  { curl -s -c "$1" -b "$1" "$2" -o "$T/tk.html"
         grep -o 'name="_token" value="[^"]*"' "$T/tk.html" | head -1 | sed 's/.*value="//;s/"//'; }
code() { curl -s -b "$1" "$2" -o "$3" -w "%{http_code}"; }

# posted <redirect_url> <must-not-contain> <label> [body-file]
# A Laravel validation failure redirects BACK to the form, so "302" alone
# proves nothing — check we actually moved on.
posted() {
  case "$1" in
    *"$2"*) bad "$3 — rejected, bounced back to the form"
            [ -n "$4" ] && grep -oE '<li[^>]*>[^<]{4,140}</li>' "$4" | head -3 ;;
    "")     bad "$3 — no redirect at all" ;;
    *)      ok "$3" ;;
  esac
}

# Verify a new account's email the way a real member does: read the OTP that
# actually landed in the mailbox and post it back. Exercises the real gate
# rather than reaching into the database.
verify_email() {
  local ck="$1" addr="$2" tk id body otp
  tk=$(tok "$ck" "$BASE/verification-email")
  curl -s -c "$ck" -b "$ck" -X POST "$BASE/verification-email/envoyer" \
       --data-urlencode "_token=$tk" -o /dev/null
  local tries=0
  while [ $tries -lt 8 ]; do
    id=$(curl -s --get "$MAILPIT/api/v1/search" --data-urlencode "query=to:$addr" --data-urlencode "limit=1" \
         | grep -o '"ID":"[^"]*"' | head -1 | sed 's/.*:"//;s/"//')
    [ -n "$id" ] && break
    sleep 1; tries=$((tries+1))
  done
  if [ -z "$id" ]; then bad "OTP email never arrived for $addr"; return 1; fi
  # Read the rendered text part, not the JSON envelope — the envelope's
  # MessageID contains digit runs that look like a code but are not.
  body=$(curl -s "$MAILPIT/view/$id.txt")
  otp=$(printf '%s' "$body" | grep -oE '[0-9]{6}' | head -1)
  if [ -z "$otp" ]; then bad "no 6-digit code in the OTP email"; return 1; fi
  tk=$(tok "$ck" "$BASE/verification-email")
  local st
  st=$(curl -s -c "$ck" -b "$ck" -X POST "$BASE/verification-email/confirmer" \
       --data-urlencode "_token=$tk" --data-urlencode "code=$otp" -o /dev/null -w "%{redirect_url}")
  posted "$st" "verification-email" "email verified via real OTP ($addr)"
}

# ── 1. Two real accounts through the live signup ────────────────────────────
say "1. Signup + email verification"
for pair in "seller:$SELLER" "buyer:$BUYER"; do
  role="${pair%%:*}"; mail="${pair##*:}"
  # users.phone is UNIQUE — give each account of each run its own number.
  [ "$role" = "seller" ] && PFX=61 || PFX=62
  C="$T/ck_$role"
  TK=$(tok "$C" "$BASE/creer-mon-compte")
  st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/creer-mon-compte" \
    --data-urlencode "_token=$TK" --data-urlencode "first_name=E2E" \
    --data-urlencode "last_name=${role}" --data-urlencode "email=$mail" \
    --data-urlencode "phone=+237${PFX}${STAMP: -7}" --data-urlencode "password=$PASS" \
    --data-urlencode "password_confirmation=$PASS" \
    --data-urlencode "account_type=artisan" --data-urlencode "lang=fr" \
    -o "$T/su_$role.html" -w "%{redirect_url}")
  case "$st" in
    *submitted=1*) ok "$role account created" ;;
    *) bad "$role signup rejected (redirected to $st)" ;;
  esac
  verify_email "$C" "$mail"
done
C="$T/ck_seller"; CB="$T/ck_buyer"

# ── 2. Business creation ────────────────────────────────────────────────────
say "2. Business creation"
c=$(code "$C" "$BASE/tableau-de-bord/entreprise/creer" "$T/bizform.html")
check "$c" "200" "business form loads"
IND=$(grep -oE '<option value="[0-9]+"' "$T/bizform.html" | head -1 | grep -oE '[0-9]+')
[ -n "$IND" ] && ok "industry dropdown populated (id=$IND)" || bad "industry dropdown EMPTY - business creation impossible"
TK=$(tok "$C" "$BASE/tableau-de-bord/entreprise/creer")
st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/entreprise/creer" \
  --data-urlencode "_token=$TK" --data-urlencode "industry_id=$IND" \
  --data-urlencode "name_fr=Atelier E2E $STAMP" \
  --data-urlencode "description_fr=Atelier de test automatise pour la verification de bout en bout." \
  --data-urlencode "email=$SELLER" --data-urlencode "phone=+2377${STAMP: -7}" \
  -o "$T/biz.html" -w "%{redirect_url}")
posted "$st" "entreprise/creer" "business created" "$T/biz.html"

# ── 3. Product upload ───────────────────────────────────────────────────────
say "3. Product upload"
c=$(code "$C" "$BASE/tableau-de-bord/produits/nouveau" "$T/pnew.html")
check "$c" "200" "product form loads"
CAT=$(grep -oE '<option value="[0-9]+"' "$T/pnew.html" | head -1 | grep -oE '[0-9]+')
[ -n "$CAT" ] && ok "category dropdown populated (id=$CAT)" || bad "category dropdown EMPTY - product creation impossible"
TK=$(tok "$C" "$BASE/tableau-de-bord/produits/nouveau")
st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/produits/nouveau" \
  -F "_token=$TK" -F "category_id=$CAT" -F "name_fr=Produit E2E $STAMP" \
  -F "description_fr=Description du produit de test." -F "price_amount=50000" \
  -F "price_type=negotiable" -F "quantity_available=10" \
  -o "$T/pstore.html" -w "%{redirect_url}")
posted "$st" "produits/nouveau" "product created" "$T/pstore.html"

c=$(code "$C" "$BASE/tableau-de-bord/produits" "$T/plist.html")
check "$c" "200" "product list loads"
grep -q "Produit E2E $STAMP" "$T/plist.html" && ok "new product appears in the list" || bad "new product MISSING from list"
SLUG=$(grep -o 'produits/[a-z0-9-]*/modifier' "$T/plist.html" | head -1 | sed 's|produits/||;s|/modifier||')
[ -n "$SLUG" ] && ok "product edit link present ($SLUG)" || bad "no product edit link"

say "4. Product status toggle"
if [ -n "$SLUG" ]; then
  TK=$(tok "$C" "$BASE/tableau-de-bord/produits")
  st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/produits/$SLUG/statut" \
    --data-urlencode "_token=$TK" -o /dev/null -w "%{http_code}")
  check "$st" "302" "publish/unpublish toggled"
  c=$(code "$C" "$BASE/tableau-de-bord/produits?status=draft" "$T/pdraft.html")
  grep -q "Produit E2E $STAMP" "$T/pdraft.html" && ok "product moved to Drafts" || bad "status did not persist"
  TK=$(tok "$C" "$BASE/tableau-de-bord/produits")
  curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/produits/$SLUG/statut" --data-urlencode "_token=$TK" -o /dev/null
fi

# ── 5. Buyer sends an RFQ ───────────────────────────────────────────────────
say "5. Quote request"
c=$(code "$CB" "$BASE/galerie/entreprises" "$T/dir.html")
check "$c" "200" "business directory loads"
BSLUG="atelier-e2e-$STAMP"
c=$(code "$CB" "$BASE/galerie/entreprises?q=Atelier+E2E+$STAMP" "$T/dirq.html")
grep -q "$BSLUG" "$T/dirq.html" && ok "new shop is publicly findable ($BSLUG)" || bad "new shop NOT findable in the public directory"
c=$(code "$T/none" "$BASE/galerie/entreprises/$BSLUG" "$T/shop.html")
check "$c" "200" "public shop page renders for a guest"
TK=$(tok "$CB" "$BASE/tableau-de-bord/demandes/creer?business=$BSLUG")
st=$(curl -s -c "$CB" -b "$CB" -X POST "$BASE/tableau-de-bord/demandes" \
  --data-urlencode "_token=$TK" --data-urlencode "business_slug=$BSLUG" \
  --data-urlencode "title=Demande E2E $STAMP" \
  --data-urlencode "description=Nous souhaitons un devis pour dix pieces." \
  -o "$T/rfq.html" -w "%{redirect_url}")
posted "$st" "demandes/creer" "RFQ submitted" "$T/rfq.html"
c=$(code "$CB" "$BASE/tableau-de-bord/demandes" "$T/rfqlist.html")
check "$c" "200" "buyer quote list loads"
grep -q "Demande E2E $STAMP" "$T/rfqlist.html" && ok "RFQ appears for the buyer" || bad "RFQ MISSING for the buyer"
RD=$(grep -o 'demandes/detail?[^"]*rfq=[0-9]*' "$T/rfqlist.html" | head -1 | grep -o '[0-9]*$')
if [ -n "$RD" ]; then
  c=$(code "$CB" "$BASE/tableau-de-bord/demandes/detail?rfq=$RD" "$T/rdet.html")
  check "$c" "200" "unquoted RFQ has its own detail page"
fi

# ── 6. Seller quotes it ─────────────────────────────────────────────────────
say "6. Seller proposal"
c=$(code "$C" "$BASE/tableau-de-bord/devis" "$T/sdevis.html")
check "$c" "200" "seller quote dashboard loads"
grep -q "Demande E2E $STAMP" "$T/sdevis.html" && ok "RFQ reached the seller" || bad "RFQ did NOT reach the seller"
RFQID=$(grep -o 'articles?[^"]*rfq=[0-9]*' "$T/sdevis.html" | head -1 | grep -o '[0-9]*$')
[ -n "$RFQID" ] && ok "builder link carries the RFQ id ($RFQID)" || bad "no builder link on the seller dashboard"
if [ -n "$RFQID" ]; then
  c=$(code "$C" "$BASE/tableau-de-bord/propositions/articles?rfq=$RFQID" "$T/bld.html")
  check "$c" "200" "proposal builder loads the real RFQ"
  grep -q "Demande E2E $STAMP" "$T/bld.html" && ok "builder prefills the real request" || bad "builder shows the WRONG item"
  TK=$(tok "$C" "$BASE/tableau-de-bord/propositions/articles?rfq=$RFQID")
  st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/demandes/$RFQID/proposition" \
    --data-urlencode "_token=$TK" \
    --data-urlencode "items[0][name]=Produit E2E" --data-urlencode "items[0][quantity]=10" \
    --data-urlencode "items[0][unit_price]=50000" --data-urlencode "items[0][unit]=Pieces" \
    --data-urlencode "items[0][discount_pct]=5" --data-urlencode "delivery_fee=25000" \
    -o "$T/prop.html" -w "%{redirect_url}")
  posted "$st" "propositions/articles" "proposal sent" "$T/prop.html"
fi

# ── 7. Buyer accepts ────────────────────────────────────────────────────────
say "7. Acceptance -> purchase order"
c=$(code "$CB" "$BASE/tableau-de-bord/demandes" "$T/rfq2.html")
PID=$(grep -o 'detail?[^"]*proposal=[0-9]*' "$T/rfq2.html" | head -1 | grep -o '[0-9]*$')
[ -n "$PID" ] && ok "buyer sees the proposal ($PID)" || bad "buyer cannot reach the proposal"
if [ -n "$PID" ]; then
  c=$(code "$CB" "$BASE/tableau-de-bord/propositions/detail?proposal=$PID" "$T/pdet.html")
  check "$c" "200" "proposal detail loads"
  grep -q "475,000\|475 000" "$T/pdet.html" && ok "detail shows the computed subtotal" || echo "    (subtotal string not matched - not fatal)"
  TK=$(tok "$CB" "$BASE/tableau-de-bord/propositions/detail?proposal=$PID")
  st=$(curl -s -c "$CB" -b "$CB" -X POST "$BASE/tableau-de-bord/propositions/$PID/accepter" \
    --data-urlencode "_token=$TK" -o "$T/acc.html" -w "%{http_code}")
  check "$st" "302" "proposal accepted"
fi

# ── 8. Order book + status transitions ──────────────────────────────────────
say "8. Orders"
c=$(code "$CB" "$BASE/tableau-de-bord/commandes" "$T/border.html")
check "$c" "200" "buyer order book loads"
grep -q "commandes/bon" "$T/border.html" && ok "buyer sees the new order" || bad "buyer order book is EMPTY"
c=$(code "$C" "$BASE/tableau-de-bord/commandes" "$T/sorder.html")
check "$c" "200" "seller order book loads"
OID=$(grep -o 'commandes/[0-9]*/statut' "$T/sorder.html" | head -1 | grep -o '[0-9]*')
[ -n "$OID" ] && ok "seller has a status control ($OID)" || bad "seller cannot advance the order"
if [ -n "$OID" ]; then
  TK=$(tok "$C" "$BASE/tableau-de-bord/commandes")
  st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/commandes/$OID/statut" \
    --data-urlencode "_token=$TK" --data-urlencode "status=in_production" -o /dev/null -w "%{http_code}")
  check "$st" "302" "order advanced to in_production"
  c=$(code "$C" "$BASE/tableau-de-bord/commandes?status=in_production" "$T/sorder2.html")
  grep -qi "production" "$T/sorder2.html" && ok "new status persisted and is filterable" || bad "status did not persist"
  PO=$(grep -o 'commandes/bon?[^"]*po=[0-9]*' "$T/sorder.html" | head -1 | grep -o '[0-9]*$')
  if [ -n "$PO" ]; then
    c=$(code "$C" "$BASE/tableau-de-bord/commandes/bon?po=$PO" "$T/po.html")
    check "$c" "200" "purchase order document renders"
  fi
  INV=$(grep -o 'factures/detail?[^"]*invoice=[0-9]*' "$T/border.html" | head -1 | grep -o '[0-9]*$')
  if [ -n "$INV" ]; then
    c=$(code "$CB" "$BASE/tableau-de-bord/factures/detail?invoice=$INV" "$T/inv.html")
    check "$c" "200" "invoice renders for the buyer"
  fi
fi

# ── 9. Messaging ────────────────────────────────────────────────────────────
say "9. Messaging"
c=$(code "$CB" "$BASE/tableau-de-bord/messages" "$T/binbox.html")
check "$c" "200" "buyer inbox loads"
CONV=$(grep -o 'messages/[0-9]*' "$T/binbox.html" | head -1 | grep -o '[0-9]*')
[ -n "$CONV" ] && ok "the RFQ opened a conversation ($CONV)" || bad "no conversation created by the RFQ"
c=$(code "$C" "$BASE/tableau-de-bord/messages" "$T/sinbox.html")
check "$c" "200" "seller inbox loads"
grep -q "Demande E2E $STAMP" "$T/sinbox.html" && ok "seller sees the buyer thread" || bad "seller inbox does NOT show the thread"
if [ -n "$CONV" ]; then
  c=$(code "$C" "$BASE/tableau-de-bord/messages/$CONV" "$T/thread.html")
  check "$c" "200" "seller opens the thread"
  TK=$(tok "$C" "$BASE/tableau-de-bord/messages/$CONV")
  st=$(curl -s -c "$C" -b "$C" -X POST "$BASE/tableau-de-bord/messages/$CONV/repondre" \
    --data-urlencode "_token=$TK" --data-urlencode "body=Bonjour, merci pour votre demande." \
    -o /dev/null -w "%{http_code}")
  check "$st" "302" "seller reply accepted"
  c=$(code "$CB" "$BASE/tableau-de-bord/messages/$CONV" "$T/bthread.html")
  grep -q "merci pour votre demande" "$T/bthread.html" && ok "buyer receives the reply" || bad "reply did NOT reach the buyer"
fi

# ── 10. Every surface, both roles + public ──────────────────────────────────
say "10. Full page sweep"
sweep() {
  local ck="$1" label="$2"; shift 2
  for p in "$@"; do
    f="$T/sw_$(echo "$label$p" | tr '/?=&' '____').html"
    c=$(code "$ck" "$BASE$p" "$f")
    e=$(grep -ic "whoops\|Undefined variable\|Undefined array key\|Call to undefined\|ErrorException\|Too few arguments" "$f")
    if [ "$c" = "200" ] && [ "$e" = "0" ]; then ok "[$label] $p"
    else bad "[$label] $p (http=$c errors=$e)"; fi
  done
}
sweep "$C" seller /tableau-de-bord/entrepreneur /tableau-de-bord/produits /tableau-de-bord/produits/nouveau \
  /tableau-de-bord/devis /tableau-de-bord/commandes /tableau-de-bord/messages \
  /tableau-de-bord/entreprise/modifier /tableau-de-bord/entreprise/verification \
  /tableau-de-bord/profil /tableau-de-bord/securite /tableau-de-bord/notifications \
  /tableau-de-bord/support /tableau-de-bord/sauvegardes /certificat-adhesion
sweep "$CB" buyer /tableau-de-bord/acheteur /tableau-de-bord/demandes /tableau-de-bord/commandes \
  /tableau-de-bord/messages /tableau-de-bord/sauvegardes /tableau-de-bord/profil \
  /tableau-de-bord/securite /tableau-de-bord/notifications /tableau-de-bord/support
sweep "$T/none" public / /galerie/entreprises /galerie/produits /galerie/secteurs /evenements \
  /partenaires /actualites /about /contact /faq /collections-heritage /centres-artisanat \
  /legal/conditions /legal/confidentialite /legal/avertissement /legal/mentions-legales \
  /login /creer-mon-compte /verification-certificat /guide-artisan /carrieres /presse

printf '\n\033[1mRESULT: %d passed, %d failed\033[0m\n' "$PASSED" "$FAILED"
echo "SELLER=$SELLER BUYER=$BUYER STAMP=$STAMP"
[ "$FAILED" = "0" ]
