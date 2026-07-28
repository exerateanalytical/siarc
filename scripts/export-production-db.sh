#!/usr/bin/env bash
# =============================================================================
#  export-production-db.sh — build the one-shot production import file.
#
#  WHAT IT PRODUCES
#      database/production/artisanhub237-production.sql
#
#  A single, self-contained SQL file that turns an EMPTY MySQL 8 database into
#  a working production database for Artisan Hub 237. It contains:
#
#    * the complete schema for every table (so nothing has to be migrated), and
#    * data for the reference tables the platform cannot boot without, plus
#      the 510 SIARC 2026 artisans that form the founding directory.
#
#  WHY A DUMP AND NOT A SEEDER
#  The owner imports through phpMyAdmin on Namecheap shared hosting. A seeder
#  would need a working PHP CLI, a correct .env, and a migrate run that has
#  already succeeded — three things that are not yet true at the moment the
#  database has to be populated. Chicken and egg. A dump is one file, one
#  paste, no PHP, and it is byte-for-byte the schema this codebase was tested
#  against. It also lets the owner import BEFORE the app can boot, which is the
#  order shared hosting forces on you.
#  The `migrations` table is included with all rows, so after the import
#  `php artisan migrate:status` reports everything applied and `migrate --force`
#  is a no-op rather than a collision.
#
#  CREDENTIALS
#  This script contains NO credential of any kind. It reads connection details
#  from the environment, or from a local .env when one is present. Nothing it
#  writes into the output file is a secret either: the only password material
#  in the export is bcrypt of a random 40-character string per SIARC artisan,
#  which is deliberately unusable until the artisan claims the profile.
#
#  USAGE
#      # source database (defaults are the Laragon dev box)
#      SRC_DB=virtualdb SRC_USER=root SRC_PASS= bash scripts/export-production-db.sh
#
#      # export, then prove it by importing into a throwaway database
#      bash scripts/export-production-db.sh --verify
#
#  --verify creates a scratch database (default: artisanhub_export_check),
#  imports the file into it, asserts the counts, and drops it again. It never
#  writes to the source database.
# =============================================================================
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

OUT_DIR="$ROOT/database/production"
OUT="$OUT_DIR/artisanhub237-production.sql"
VERIFY=0
[ "${1:-}" = "--verify" ] && VERIFY=1

if [ -t 1 ]; then R=$'\033[31m'; G=$'\033[32m'; B=$'\033[1m'; N=$'\033[0m'
else R=''; G=''; B=''; N=''; fi

die()  { printf '%sERROR%s %s\n' "$R" "$N" "$1" >&2; exit 1; }
info() { printf '%s==>%s %s\n' "$B" "$N" "$1"; }

# ── Connection details: environment first, local .env second. Never literals. ──
env_get() {
  [ -f "$ROOT/.env" ] || return 1
  sed -n "s/^[[:space:]]*$1[[:space:]]*=//p" "$ROOT/.env" | head -1 \
    | sed -e 's/[[:space:]]\+#.*$//' -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' \
          -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/"
}
SRC_HOST="${SRC_HOST:-${DB_HOST:-$(env_get DB_HOST 2>/dev/null || echo 127.0.0.1)}}"
SRC_PORT="${SRC_PORT:-${DB_PORT:-$(env_get DB_PORT 2>/dev/null || echo 3306)}}"
SRC_DB="${SRC_DB:-${DB_DATABASE:-$(env_get DB_DATABASE 2>/dev/null || echo virtualdb)}}"
SRC_USER="${SRC_USER:-${DB_USERNAME:-$(env_get DB_USERNAME 2>/dev/null || echo root)}}"
SRC_PASS="${SRC_PASS-${DB_PASSWORD-$(env_get DB_PASSWORD 2>/dev/null || echo '')}}"
SCRATCH_DB="${SCRATCH_DB:-artisanhub_export_check}"

command -v mysqldump >/dev/null 2>&1 || die "mysqldump is not on PATH."
command -v mysql     >/dev/null 2>&1 || die "mysql is not on PATH."

# Passing a password on the command line leaks it to `ps`. Write a throwaway
# option file instead, 0600, removed on exit.
CNF="$(mktemp)"; chmod 600 "$CNF"
trap 'rm -f "$CNF"' EXIT
{
  printf '[client]\n'
  printf 'host=%s\n' "$SRC_HOST"
  printf 'port=%s\n' "$SRC_PORT"
  printf 'user=%s\n' "$SRC_USER"
  [ -n "$SRC_PASS" ] && printf 'password=%s\n' "$SRC_PASS"
} > "$CNF"

MYSQL()     { mysql --defaults-extra-file="$CNF" "$@"; }
MYSQLDUMP() { mysqldump --defaults-extra-file="$CNF" "$@"; }

MYSQL -N -e "SELECT 1" >/dev/null 2>&1 || die "cannot connect to MySQL at $SRC_HOST:$SRC_PORT as $SRC_USER."

# =============================================================================
#  WHAT SHIPS
# =============================================================================
# Reference data with no owner and no personal content. Every one of these is
# read at boot or on a hot path; an empty one is a broken feature, not a thin
# one. `migrations` ships so the app considers itself migrated.
FULL_TABLES=(
  migrations
  regions cities
  industries               # the official craft taxonomy — 413 rows, 4 levels
  product_categories attribute_templates
  certifications           # certificate / label types
  subscription_plans       # PlatformFees throws without an active plan
  roles permissions role_has_permissions
  system_settings platform_settings feature_flags
)

# The founding directory. `siarc_code IS NOT NULL` is the only marker that
# separates the imported SIARC 2026 artisans from anything created locally,
# which is why it — and not a date or an id range — is the filter.
SIARC_OWNERS="(SELECT user_id FROM businesses WHERE siarc_code IS NOT NULL AND user_id IS NOT NULL)"

# Everything else is deliberately absent. Named here so the omission is a
# decision on the record rather than an oversight:
#   businesses.is_demo = 1  the demo artisan, and with it 128 fabricated
#                           reviews and 8 fabricated awards. Publishing invented
#                           reviews on a live marketplace is a straightforward
#                           consumer-deception problem, so none of it ships.
#   *@e2e.test              accounts and businesses created by the smoke test.
#   products, orders, quotes, invoices, payments, conversations, messages,
#   provenance/workshop/export registers, otp_verifications, audit_logs,
#   business_views, search_queries, user_notifications — development scratch.
#   users                   except the 510 SIARC owners. The administrator is
#                           NOT in the export; it is created on the server from
#                           ADMIN_EMAIL / ADMIN_PASSWORD so no password hash of
#                           any real person is ever committed to this repo.

mkdir -p "$OUT_DIR"

info "Source: $SRC_USER@$SRC_HOST:$SRC_PORT/$SRC_DB"
info "Writing $OUT"

DUMP_COMMON=(
  --no-tablespaces          # PROCESS privilege is not granted on shared hosting
  --set-gtid-purged=OFF     # GTID statements are rejected by phpMyAdmin
  --skip-add-locks
  --skip-comments
  --single-transaction
  --default-character-set=utf8mb4
  --routines=FALSE --events=FALSE --triggers=FALSE
)

{
  cat <<'HEADER'
-- =============================================================================
--  Artisan Hub 237 — production database import
--
--  Import this ONCE, into an EMPTY database, before the application first runs.
--  phpMyAdmin: select the database, Import tab, choose this file, Go.
--
--  It creates every table and loads the reference data the platform cannot run
--  without, plus the 510 SIARC 2026 artisans as unpublished, unclaimed, draft
--  profiles with no email address.
--
--  It contains NO demo artisan, NO reviews, NO awards, NO test accounts and NO
--  administrator. Create the administrator on the server with:
--      php artisan db:seed --class="Database\Seeders\Siac\SiacAdminSeeder"
--  after setting ADMIN_EMAIL and ADMIN_PASSWORD in .env.
--
--  Generated by scripts/export-production-db.sh. Do not hand-edit.
-- =============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

HEADER

  # ── 1. Schema for EVERY table ───────────────────────────────────────────────
  # All of it, including the tables that ship empty: a missing table is a fatal
  # error the first time a member touches that feature, and the whole point of
  # this file is that the owner never has to run a migration.
  echo "-- ─────────────── schema (all tables) ───────────────"
  MYSQLDUMP "${DUMP_COMMON[@]}" --no-data "$SRC_DB" \
    || die "schema dump failed"
  echo

  # ── 2. Reference data ───────────────────────────────────────────────────────
  echo "-- ─────────────── reference data ───────────────"
  for t in "${FULL_TABLES[@]}"; do
    echo "-- $t"
    MYSQLDUMP "${DUMP_COMMON[@]}" --no-create-info --complete-insert "$SRC_DB" "$t" \
      || die "data dump failed for $t"
  done
  echo

  # ── 3. The 510 SIARC artisans and their owner accounts ──────────────────────
  echo "-- ─────────────── SIARC 2026 founding directory ───────────────"
  echo "-- users (SIARC profile owners only)"
  MYSQLDUMP "${DUMP_COMMON[@]}" --no-create-info --complete-insert \
    --where="id IN $SIARC_OWNERS" "$SRC_DB" users || die "users dump failed"

  echo "-- model_has_roles (SIARC profile owners only)"
  MYSQLDUMP "${DUMP_COMMON[@]}" --no-create-info --complete-insert \
    --where="model_id IN $SIARC_OWNERS" "$SRC_DB" model_has_roles || die "model_has_roles dump failed"

  echo "-- businesses (siarc_code IS NOT NULL)"
  MYSQLDUMP "${DUMP_COMMON[@]}" --no-create-info --complete-insert \
    --where="siarc_code IS NOT NULL" "$SRC_DB" businesses || die "businesses dump failed"
  echo

  # ── 4. Normalise the imported directory ─────────────────────────────────────
  # The dev database drifts: a profile gets published while someone is testing
  # the claim flow, a row picks up an address. Rather than trust that it did
  # not, the file asserts the invariant itself. These four statements are
  # idempotent and cost nothing if the data was already correct.
  cat <<'NORMALISE'
-- ─────────────── invariants for the founding directory ───────────────
-- Unpublished, uncontactable and unclaimed, whatever the source database said.
UPDATE `businesses` SET `status` = 'draft'   WHERE `siarc_code` IS NOT NULL AND `status` <> 'draft';
UPDATE `businesses` SET `email`  = NULL      WHERE `siarc_code` IS NOT NULL AND `email` IS NOT NULL;
UPDATE `businesses` SET `claimed_at` = NULL  WHERE `siarc_code` IS NOT NULL AND `claimed_at` IS NOT NULL;
UPDATE `users` u JOIN `businesses` b ON b.`user_id` = u.`id`
   SET u.`email` = NULL, u.`is_email_verified` = 0
 WHERE b.`siarc_code` IS NOT NULL AND (u.`email` IS NOT NULL OR u.`is_email_verified` <> 0);

-- The administrator account is not exported, so any row that pointed at the
-- developer's admin is now a foreign key aimed at nothing. FOREIGN_KEY_CHECKS
-- is off during the import so it loads, but the first UPDATE the app makes to
-- that row would fail against a constraint. Cut the reference instead.
UPDATE `system_settings` s LEFT JOIN `users` u ON u.`id` = s.`updated_by`
   SET s.`updated_by` = NULL
 WHERE s.`updated_by` IS NOT NULL AND u.`id` IS NULL;
UPDATE `businesses` b LEFT JOIN `users` u ON u.`id` = b.`id_verified_by`
   SET b.`id_verified_by` = NULL, b.`id_verified_at` = NULL
 WHERE b.`id_verified_by` IS NOT NULL AND u.`id` IS NULL;

-- Nothing below should ever match. They are here so a botched export fails
-- loudly at import time instead of quietly putting invented reviews on a live
-- marketplace.
DELETE FROM `business_reviews`;
DELETE FROM `business_awards`;
DELETE FROM `businesses` WHERE `is_demo` = 1;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
NORMALISE
} > "$OUT"

# CRLF would survive into phpMyAdmin fine, but it inflates the file and makes
# the diff unreadable on a repo that is otherwise LF.
if command -v sed >/dev/null 2>&1; then sed -i 's/\r$//' "$OUT"; fi

BYTES=$(wc -c < "$OUT" | tr -d ' ')
info "$(printf 'wrote %s (%s bytes, %.1f MiB)' "$OUT" "$BYTES" "$(echo "$BYTES" | awk '{print $1/1048576}')")"

if command -v gzip >/dev/null 2>&1; then
  gzip -9 -c "$OUT" > "$OUT.gz"
  GZBYTES=$(wc -c < "$OUT.gz" | tr -d ' ')
  info "$(printf 'wrote %s.gz (%s bytes, %.1f MiB) — phpMyAdmin imports .gz directly' "$OUT" "$GZBYTES" "$(echo "$GZBYTES" | awk '{print $1/1048576}')")"
fi

# =============================================================================
#  VERIFY — import into a scratch database and assert. Never touches the source.
# =============================================================================
[ "$VERIFY" -eq 1 ] || { info "Done. Re-run with --verify to prove the import."; exit 0; }

[ "$SCRATCH_DB" = "$SRC_DB" ] && die "SCRATCH_DB must not be the source database."

info "Verifying against scratch database '$SCRATCH_DB'"
MYSQL -e "DROP DATABASE IF EXISTS \`$SCRATCH_DB\`; CREATE DATABASE \`$SCRATCH_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
  || die "could not create the scratch database"

if ! MYSQL --default-character-set=utf8mb4 "$SCRATCH_DB" < "$OUT"; then
  die "the export did not import cleanly into $SCRATCH_DB"
fi

FAILS=0
assert() { # assert <label> <sql> <expected>
  local got; got="$(MYSQL -N "$SCRATCH_DB" -e "$2" 2>/dev/null | tr -d '\r')"
  if [ "$got" = "$3" ]; then printf '  %sPASS%s %-46s %s\n' "$G" "$N" "$1" "$got"
  else printf '  %sFAIL%s %-46s got %s, want %s\n' "$R" "$N" "$1" "${got:-<none>}" "$3"; FAILS=$((FAILS+1)); fi
}

printf '\n%sCounts%s\n' "$B" "$N"
assert "official craft taxonomy (industries)" "SELECT COUNT(*) FROM industries" 413
assert "taxonomy depth (distinct levels)"     "SELECT COUNT(DISTINCT level) FROM industries" 4
assert "regions"                              "SELECT COUNT(*) FROM regions" 10
assert "cities"                               "SELECT COUNT(*) FROM cities" 55
assert "subscription plans"                   "SELECT COUNT(*) FROM subscription_plans" 5
assert "active subscription plans (>0)"       "SELECT IF(COUNT(*)>0,'yes','no') FROM subscription_plans WHERE is_active=1" yes
assert "certification types"                  "SELECT COUNT(*) FROM certifications" 8
assert "product categories"                   "SELECT COUNT(*) FROM product_categories" 18
assert "roles"                                "SELECT COUNT(*) FROM roles" 8
assert "permissions"                          "SELECT COUNT(*) FROM permissions" 90
assert "role_has_permissions"                 "SELECT COUNT(*) FROM role_has_permissions" 230
assert "migrations recorded"                  "SELECT COUNT(*) FROM migrations" "$(MYSQL -N "$SRC_DB" -e 'SELECT COUNT(*) FROM migrations' | tr -d '\r')"

printf '\n%sSIARC founding directory%s\n' "$B" "$N"
assert "SIARC artisans"                       "SELECT COUNT(*) FROM businesses WHERE siarc_code IS NOT NULL" 510
assert "  ... all status='draft'"             "SELECT COUNT(*) FROM businesses WHERE siarc_code IS NOT NULL AND status='draft'" 510
assert "  ... all email IS NULL"              "SELECT COUNT(*) FROM businesses WHERE siarc_code IS NOT NULL AND email IS NULL" 510
assert "  ... all unclaimed"                  "SELECT COUNT(*) FROM businesses WHERE siarc_code IS NOT NULL AND claimed_at IS NULL" 510
assert "owner accounts"                       "SELECT COUNT(*) FROM users" 510
assert "  ... all email IS NULL"              "SELECT COUNT(*) FROM users WHERE email IS NULL" 510
assert "  ... all hold a role"                "SELECT COUNT(*) FROM model_has_roles" 510
assert "trade name resolves (FK intact)"      "SELECT COUNT(*) FROM businesses b JOIN industries i ON i.id=b.industry_id WHERE b.siarc_code IS NOT NULL" 510

printf '\n%sExclusions%s\n' "$B" "$N"
assert "demo businesses"                      "SELECT COUNT(*) FROM businesses WHERE is_demo=1" 0
assert "business reviews"                     "SELECT COUNT(*) FROM business_reviews" 0
assert "business awards"                      "SELECT COUNT(*) FROM business_awards" 0
assert "non-SIARC businesses"                 "SELECT COUNT(*) FROM businesses WHERE siarc_code IS NULL" 0
assert "e2e/test accounts"                    "SELECT COUNT(*) FROM users WHERE email LIKE '%e2e.test'" 0
assert "products"                             "SELECT COUNT(*) FROM products" 0
assert "payments"                             "SELECT COUNT(*) FROM payments" 0
assert "invoices"                             "SELECT COUNT(*) FROM invoices" 0
assert "purchase orders"                      "SELECT COUNT(*) FROM purchase_orders" 0
assert "conversations"                        "SELECT COUNT(*) FROM conversations" 0
assert "otp_verifications"                    "SELECT COUNT(*) FROM otp_verifications" 0
assert "audit_logs"                           "SELECT COUNT(*) FROM audit_logs" 0

printf '\n%sReferential integrity%s\n' "$B" "$N"
assert "dangling businesses.user_id"    "SELECT COUNT(*) FROM businesses b LEFT JOIN users u ON u.id=b.user_id WHERE b.user_id IS NOT NULL AND u.id IS NULL" 0
assert "dangling businesses.industry_id" "SELECT COUNT(*) FROM businesses b LEFT JOIN industries i ON i.id=b.industry_id WHERE b.industry_id IS NOT NULL AND i.id IS NULL" 0
assert "dangling businesses.city_id"    "SELECT COUNT(*) FROM businesses b LEFT JOIN cities c ON c.id=b.city_id WHERE b.city_id IS NOT NULL AND c.id IS NULL" 0
assert "dangling system_settings.updated_by" "SELECT COUNT(*) FROM system_settings s LEFT JOIN users u ON u.id=s.updated_by WHERE s.updated_by IS NOT NULL AND u.id IS NULL" 0
assert "dangling cities.region_id"      "SELECT COUNT(*) FROM cities c LEFT JOIN regions r ON r.id=c.region_id WHERE c.region_id IS NOT NULL AND r.id IS NULL" 0
assert "dangling industries.parent_id"  "SELECT COUNT(*) FROM industries a LEFT JOIN industries p ON p.id=a.parent_id WHERE a.parent_id IS NOT NULL AND p.id IS NULL" 0
assert "dangling model_has_roles.role_id" "SELECT COUNT(*) FROM model_has_roles m LEFT JOIN roles r ON r.id=m.role_id WHERE r.id IS NULL" 0

# Table parity: an import that silently dropped a CREATE TABLE would leave the
# app 500ing the first time someone opened that feature.
SRC_T="$(MYSQL -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SRC_DB' AND table_type='BASE TABLE'" | tr -d '\r')"
printf '\n%sSchema%s\n' "$B" "$N"
assert "tables created" "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SCRATCH_DB' AND table_type='BASE TABLE'" "$SRC_T"

MYSQL -e "DROP DATABASE \`$SCRATCH_DB\`;" >/dev/null 2>&1

printf '\n%s──────────────────────────────────────────%s\n' "$B" "$N"
if [ "$FAILS" -eq 0 ]; then
  printf '%sEXPORT VERIFIED%s  %s bytes\n' "$G" "$N" "$BYTES"
  exit 0
else
  printf '%sEXPORT FAILED VERIFICATION%s  %d assertion(s)\n' "$R" "$N" "$FAILS"
  exit 1
fi
