-- ============================================================================
--  Give users a country of their own
--  Run once, in phpMyAdmin, against artisan_arthubdb.
--
--  Run this BEFORE 2026-07-31-open-signup-to-every-country.sql.
-- ============================================================================
--
--  Additive and nullable, so the currently-running code ignores it entirely —
--  safe to run before the new code is uploaded, which is the required order.
--
--  Every account that exists today is a SIARC import from a Cameroonian
--  dataset, so Cameroon is a statement of fact for them, not a guess.
--
--  MariaDB-safe: no CTEs, no procedures, no UPDATE ... JOIN.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `users` ADD COLUMN `country_id` bigint unsigned NULL AFTER `phone`;

ALTER TABLE `users`
  ADD CONSTRAINT `users_country_id_foreign`
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

UPDATE `users`
SET `country_id` = (SELECT `id` FROM `countries` WHERE `code` = 'CM')
WHERE `country_id` IS NULL;

INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_07_31_090000_add_country_to_users',
        (SELECT * FROM (SELECT COALESCE(MAX(`batch`),0)+1 FROM `migrations`) AS b));

-- Expected: 0 and 0.
SELECT
  (SELECT COUNT(*) FROM `users` WHERE `country_id` IS NULL) AS `users_without_a_country_MUST_BE_0`,
  (SELECT COUNT(*) FROM `information_schema`.`columns`
    WHERE `table_schema` = DATABASE() AND `table_name` = 'users'
      AND `column_name` = 'country_id') - 1 AS `column_missing_MUST_BE_0`;
