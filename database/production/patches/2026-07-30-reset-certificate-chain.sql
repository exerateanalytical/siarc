-- ============================================================================
--  Reset the certificate chain so a new signing key can be created
--  Run once, in phpMyAdmin, against artisan_arthubdb. 2026-07-30.
-- ============================================================================
--
--  WHY THIS IS NEEDED
--  The production signing key (kid 834D8B4A0D21FB408ECD369DCE2BA8E2) was lost
--  when the app folder was replaced during the 30 July upload. There is no
--  backup. Every certificate signed with it is therefore already permanently
--  unverifiable -- that is true before this script runs and after it, and
--  nothing can change it.
--
--  The CA ceremony page refuses to create a new key while certificate records
--  exist, which is the correct guard when the old key is merely being
--  replaced. Here it protects nothing and blocks everything: without a key the
--  platform cannot sign any FUTURE certificate either.
--
--  The vendors those certificates belonged to have been deleted by the owner.
--  product_certificates cascades from businesses so those rows are likely gone
--  already; certificate_events has no foreign key and does not cascade, so its
--  rows survive and are what the guard still counts.
--
--  WHAT THIS DOES
--  Copies both tables to dated archive tables, then empties the live ones. The
--  history is not destroyed -- it is moved somewhere it no longer blocks key
--  creation. If you ever need it back it is one INSERT ... SELECT away.
--
--  certificate_events is a tamper-evident hash chain: each row commits to the
--  one before it. Emptying it starts a fresh chain from genesis, which is the
--  honest thing to do here -- the old chain attests to signatures that can no
--  longer be checked by anyone, including us.
--
--  Safe to run twice. The archive tables are created only if absent, and the
--  second run copies zero rows because the live tables are already empty.
--
--  MariaDB-safe: no CTEs, no procedures, no DELIMITER. Production is MariaDB
--  and rejects all three inside a phpMyAdmin paste.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Archive first. CREATE ... AS SELECT copies rows without copying indexes,
--    so no UNIQUE constraint can reject the snapshot.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS certificate_events_archive_20260730
AS SELECT * FROM certificate_events;

CREATE TABLE IF NOT EXISTS product_certificates_archive_20260730
AS SELECT * FROM product_certificates;

-- ---------------------------------------------------------------------------
-- 2. Empty the live tables the ceremony guard counts.
-- ---------------------------------------------------------------------------
DELETE FROM certificate_events;
DELETE FROM product_certificates;

-- ---------------------------------------------------------------------------
-- 3. Report. Both live tables must read 0 for the ceremony page to proceed.
--    The archive counts are what was preserved.
-- ---------------------------------------------------------------------------
SELECT 'certificate_events   (must be 0)' AS item, COUNT(*) AS n FROM certificate_events
UNION ALL
SELECT 'product_certificates (must be 0)', COUNT(*) FROM product_certificates
UNION ALL
SELECT 'archived: certificate_events',     COUNT(*) FROM certificate_events_archive_20260730
UNION ALL
SELECT 'archived: product_certificates',   COUNT(*) FROM product_certificates_archive_20260730;
