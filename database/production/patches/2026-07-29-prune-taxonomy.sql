-- =============================================================================
--  2026-07-29-prune-taxonomy.sql   (MariaDB-safe rewrite, 29 July 2026)
--
--  Owner's instruction (2026-07-29): "I see other categories concerning fish
--  etc in the platform that are not the categories that were sent from the
--  file containing the 511 artisans. delete those categories and keep only the
--  categories from the 511 artisans and the categories with category Icon."
--
--  Prunes `industries` from 413 rows to 116, keeping exactly:
--    (a) every category a real record points at -- businesses,
--        attribute_templates, certifications, events, popular_searches_cache,
--        sectors (every table with an industry_id FK, confirmed against
--        information_schema, not just `businesses`)
--    (b) every category carrying a real illustrated icon (image_icon) -- the
--        curated public browse tiles
--    (c) every ancestor of anything kept by (a) or (b), so the tree stays
--        whole. Worked example: Poissonnier (id 948) is used by real
--        businesses so it survives, and so does its parent (id 947), while
--        unused sibling fish/food leaves are removed.
--
--  WHY THIS FILE WAS REWRITTEN
--  The first version wrapped the logic in a stored procedure using
--  `WITH RECURSIVE ... DELETE`. MySQL 8 accepts that; the production server
--  runs MariaDB, which does not support a CTE inside a DELETE, so the
--  CREATE PROCEDURE failed to parse and nothing ran at all -- the taxonomy was
--  left untouched. This version uses no procedure, no CTE and no DELIMITER,
--  only plain SQL both engines have supported for years. It is the same class
--  of bug as the GROUP BY failure found on this platform earlier today: a
--  local MySQL more permissive than the server it deploys to.
--
--  THE GUARD IS REAL, NOT ADVISORY
--  The DELETE carries `AND @keep_n = 116`. If the computed keep-set is not
--  exactly 116 rows, that condition is false for every row and the DELETE
--  removes nothing -- a wrong keep-set cannot damage the taxonomy, it simply
--  does nothing, and the verification query at the end will still read 413.
--
--  Safe to run more than once: on a second run the keep-set is already the
--  whole table, so the DELETE matches no rows.
-- =============================================================================

-- ── 1. Build the keep-set in a temporary table ──────────────────────────────
DROP TEMPORARY TABLE IF EXISTS keep_ids;
CREATE TEMPORARY TABLE keep_ids (id BIGINT UNSIGNED NOT NULL PRIMARY KEY);

-- (b) categories with a real illustrated icon
INSERT IGNORE INTO keep_ids (id)
  SELECT id FROM industries WHERE image_icon IS NOT NULL AND image_icon <> '';

-- (a) every category a real record actually references
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM businesses             WHERE industry_id IS NOT NULL;
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM attribute_templates    WHERE industry_id IS NOT NULL;
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM certifications         WHERE industry_id IS NOT NULL;
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM events                 WHERE industry_id IS NOT NULL;
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM popular_searches_cache WHERE industry_id IS NOT NULL;
INSERT IGNORE INTO keep_ids (id) SELECT DISTINCT industry_id FROM sectors                WHERE industry_id IS NOT NULL;

-- (c) climb to every ancestor. The taxonomy is 4 levels deep, so four passes
--     reach the root from any leaf; a fifth is harmless insurance.
--
--     Each pass stages the parents in a SECOND temporary table first. Both
--     MySQL and MariaDB refuse to open the same temporary table twice in one
--     statement (error 1137), so `INSERT INTO keep_ids ... JOIN keep_ids` is
--     illegal -- which is exactly what the first attempt at this rewrite hit.
DROP TEMPORARY TABLE IF EXISTS parent_ids;
CREATE TEMPORARY TABLE parent_ids (id BIGINT UNSIGNED NOT NULL PRIMARY KEY);

INSERT IGNORE INTO parent_ids (id) SELECT DISTINCT i.parent_id FROM industries i JOIN keep_ids k ON i.id = k.id WHERE i.parent_id IS NOT NULL;
INSERT IGNORE INTO keep_ids   (id) SELECT id FROM parent_ids;

DELETE FROM parent_ids;
INSERT IGNORE INTO parent_ids (id) SELECT DISTINCT i.parent_id FROM industries i JOIN keep_ids k ON i.id = k.id WHERE i.parent_id IS NOT NULL;
INSERT IGNORE INTO keep_ids   (id) SELECT id FROM parent_ids;

DELETE FROM parent_ids;
INSERT IGNORE INTO parent_ids (id) SELECT DISTINCT i.parent_id FROM industries i JOIN keep_ids k ON i.id = k.id WHERE i.parent_id IS NOT NULL;
INSERT IGNORE INTO keep_ids   (id) SELECT id FROM parent_ids;

DELETE FROM parent_ids;
INSERT IGNORE INTO parent_ids (id) SELECT DISTINCT i.parent_id FROM industries i JOIN keep_ids k ON i.id = k.id WHERE i.parent_id IS NOT NULL;
INSERT IGNORE INTO keep_ids   (id) SELECT id FROM parent_ids;

DELETE FROM parent_ids;
INSERT IGNORE INTO parent_ids (id) SELECT DISTINCT i.parent_id FROM industries i JOIN keep_ids k ON i.id = k.id WHERE i.parent_id IS NOT NULL;
INSERT IGNORE INTO keep_ids   (id) SELECT id FROM parent_ids;

DROP TEMPORARY TABLE IF EXISTS parent_ids;

-- A stale reference in another table must not resurrect a category row that
-- does not actually exist.
DELETE k FROM keep_ids k LEFT JOIN industries i ON i.id = k.id WHERE i.id IS NULL;

-- ── 2. Report, before anything irreversible ─────────────────────────────────
SET @total_before = (SELECT COUNT(*) FROM industries);
SET @keep_n       = (SELECT COUNT(*) FROM keep_ids);

SELECT @total_before             AS `industries_before`,
       @keep_n                   AS `computed_keep_count`,
       @total_before - @keep_n   AS `will_delete`,
       IF(@keep_n = 116, 'YES - the DELETE below will run',
                         'NO  - guard blocks the DELETE, nothing changes') AS `matches_expected_116`;

-- ── 3. Delete, guarded ──────────────────────────────────────────────────────
--  keep_ids is referenced exactly once here: MySQL and MariaDB both refuse to
--  open the same temporary table twice in one statement, which is why the count
--  check is held in @keep_n instead of being queried inline.
DELETE FROM industries
 WHERE id NOT IN (SELECT id FROM keep_ids)
   AND @keep_n = 116;

-- ── 4. Prove the outcome ────────────────────────────────────────────────────
SELECT @total_before                                       AS `industries_before`,
       (SELECT COUNT(*) FROM industries)                   AS `industries_after`,
       @total_before - (SELECT COUNT(*) FROM industries)    AS `rows_deleted`,
       CASE
         WHEN (SELECT COUNT(*) FROM industries) = 116            THEN 'DONE - 116 categories remain'
         WHEN (SELECT COUNT(*) FROM industries) = @total_before   THEN 'NOTHING CHANGED - guard blocked it, report this'
         ELSE 'UNEXPECTED - report this number'
       END                                                 AS `result`;

-- Nothing may reference a category that no longer exists.
SELECT COUNT(*) AS `orphaned_businesses_must_be_zero`
  FROM businesses b
 WHERE b.industry_id IS NOT NULL
   AND NOT EXISTS (SELECT 1 FROM industries i WHERE i.id = b.industry_id);

DROP TEMPORARY TABLE IF EXISTS keep_ids;
