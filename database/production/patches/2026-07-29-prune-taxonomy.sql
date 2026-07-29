-- ============================================================================
-- Prune unused taxonomy categories from `industries`.
--
-- Owner's instruction (2026-07-29): "I see other categories concerning fish
-- etc in the platform that are not the categories that were sent from the
-- file containing the 511 artisans. delete those categories and keep only
-- the categories from the 511 artisans and the categories with category
-- Icon."
--
-- Keep-set rule (a row survives if ANY of these hold):
--   (a) at least one row in businesses, attribute_templates, certifications,
--       events, popular_searches_cache, or sectors references it via
--       industry_id (every table with an industry_id FK, not just
--       `businesses` — confirmed against information_schema);
--   (b) it has a non-empty `image_icon` (the 10 curated public browse tiles);
--   (c) it is an ancestor, however many levels up, of a row kept under (a) or
--       (b) — otherwise a used leaf would dangle from a deleted parent and
--       break breadcrumb/parent lookups. A "fish" example: Poissonnier (id
--       948) is used by real businesses and is kept, along with its parent
--       "Abattage, conservation de viande et poisson..." (id 947) — even
--       though most of that branch's siblings are unused and ARE deleted.
--
-- Verified on a local restore of this exact production export on 2026-07-29:
--   413 total -> 116 kept -> 297 deleted.
--   Kept by level: 1x3 (all three filières survive), 2x10 (exactly the
--   image_icon tiles), 3x28, 4x75.
--
-- Mirrors database/migrations/2026_07_30_100000_prune_unused_industries.php
-- exactly (same keep-set query) so a run through phpMyAdmin (no SSH on the
-- live host) produces the identical result the migration produces locally.
--
-- SAFE TO RE-RUN: the keep-set is recomputed from live data every time, so a
-- second run against an already-pruned database is a no-op (0 rows deleted).
--
-- HOW TO RUN: paste this whole file into phpMyAdmin's SQL tab against the
-- production database and execute it once. Read the four SELECT result sets
-- it prints (before-count, computed keep/delete counts, after-count) — if
-- "computed delete count" does not equal "expected delete count", STOP and do
-- not proceed; the taxonomy has drifted since this patch was written and the
-- numbers must be re-derived before deleting anything.
-- ============================================================================

DROP PROCEDURE IF EXISTS prune_unused_industries_20260729;

DELIMITER $$

CREATE PROCEDURE prune_unused_industries_20260729()
proc_body: BEGIN
    DECLARE expected_total   INT DEFAULT 413;
    DECLARE expected_keep    INT DEFAULT 116;
    DECLARE expected_delete  INT DEFAULT 297;

    DECLARE total_before INT;
    DECLARE keep_count   INT;
    DECLARE delete_count INT;
    DECLARE total_after  INT;

    SET total_before = (SELECT COUNT(*) FROM industries);

    SET keep_count = (
        SELECT COUNT(*) FROM (
            WITH RECURSIVE keep AS (
                SELECT id FROM industries WHERE image_icon IS NOT NULL AND image_icon <> ''
                UNION SELECT industry_id FROM businesses WHERE industry_id IS NOT NULL
                UNION SELECT industry_id FROM attribute_templates WHERE industry_id IS NOT NULL
                UNION SELECT industry_id FROM certifications WHERE industry_id IS NOT NULL
                UNION SELECT industry_id FROM events WHERE industry_id IS NOT NULL
                UNION SELECT industry_id FROM popular_searches_cache WHERE industry_id IS NOT NULL
                UNION SELECT industry_id FROM sectors WHERE industry_id IS NOT NULL
                UNION
                SELECT i.parent_id FROM industries i JOIN keep k ON i.id = k.id WHERE i.parent_id IS NOT NULL
            )
            SELECT id FROM keep
        ) AS computed_keep_set
    );

    SET delete_count = total_before - keep_count;

    -- Report the numbers before doing anything irreversible.
    SELECT
        total_before               AS `industries_before`,
        keep_count                 AS `computed_keep_count`,
        delete_count               AS `computed_delete_count`,
        expected_total             AS `expected_total_before`,
        expected_keep              AS `expected_keep_count`,
        expected_delete            AS `expected_delete_count`,
        (total_before = expected_total
            AND keep_count = expected_keep
            AND delete_count = expected_delete) AS `matches_verified_baseline`;

    IF total_before <> expected_total OR keep_count <> expected_keep OR delete_count <> expected_delete THEN
        SELECT 'ABORTED: computed counts do not match the verified baseline. No rows were deleted. Re-derive the keep-set before re-running.' AS `result`;
        LEAVE proc_body;
    END IF;

    START TRANSACTION;

    WITH RECURSIVE keep AS (
        SELECT id FROM industries WHERE image_icon IS NOT NULL AND image_icon <> ''
        UNION SELECT industry_id FROM businesses WHERE industry_id IS NOT NULL
        UNION SELECT industry_id FROM attribute_templates WHERE industry_id IS NOT NULL
        UNION SELECT industry_id FROM certifications WHERE industry_id IS NOT NULL
        UNION SELECT industry_id FROM events WHERE industry_id IS NOT NULL
        UNION SELECT industry_id FROM popular_searches_cache WHERE industry_id IS NOT NULL
        UNION SELECT industry_id FROM sectors WHERE industry_id IS NOT NULL
        UNION
        SELECT i.parent_id FROM industries i JOIN keep k ON i.id = k.id WHERE i.parent_id IS NOT NULL
    )
    DELETE FROM industries WHERE id NOT IN (SELECT id FROM keep);

    SET total_after = (SELECT COUNT(*) FROM industries);

    IF total_after <> expected_keep THEN
        ROLLBACK;
        SELECT CONCAT('ABORTED: after DELETE, industries has ', total_after,
                       ' rows, expected ', expected_keep, '. Rolled back, nothing was changed.') AS `result`;
        LEAVE proc_body;
    END IF;

    COMMIT;

    SELECT
        total_before   AS `industries_before`,
        total_after    AS `industries_after`,
        delete_count   AS `rows_deleted`,
        'COMMITTED' AS `result`;
END$$

DELIMITER ;

CALL prune_unused_industries_20260729();

DROP PROCEDURE IF EXISTS prune_unused_industries_20260729;
