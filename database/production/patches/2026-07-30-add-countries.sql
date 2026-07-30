-- ============================================================================
--  Open the platform to Côte d'Ivoire and Algeria
--  Run once, in phpMyAdmin, against artisan_arthubdb. 2026-07-30.
-- ============================================================================
--
--  WHAT THIS IS
--  The platform was built single-country: `regions` held Cameroon's ten regions
--  and nothing recorded which country a region belonged to, because there had
--  only ever been one. This adds that missing dimension so artisans in Côte
--  d'Ivoire and Algeria can register, be listed, and be filtered for.
--
--  It is the SQL equivalent of two Laravel migrations. The last statement
--  records them in the `migrations` table so Laravel knows they are done and
--  never tries to apply them a second time. Do not skip it.
--
--  ORDER MATTERS. Run the whole file in one go, top to bottom. phpMyAdmin's
--  SQL tab handles this fine — paste it all and press Go.
--
--  SAFETY
--  Nothing here deletes or rewrites an existing row. It creates one table, adds
--  one nullable column to three tables, and inserts new reference rows. Your
--  510 existing businesses are pointed at Cameroon via the region they already
--  have; any business with no region is left null rather than guessed at.
--
--  MariaDB-safe: no CTEs, no procedures, no DELIMITER, no UPDATE ... JOIN.
--  Production is MariaDB and rejects all four in a phpMyAdmin paste.
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- 1. The countries table.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code3` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_fr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dial_code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_symbol` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag_emoji` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_lang` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`),
  UNIQUE KEY `countries_code3_unique` (`code3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The three launch countries. INSERT IGNORE so a re-run cannot duplicate them.
INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('CM', 'CMR', 'Cameroun', 'Cameroon', '237', 'XAF', 'FCFA', '🇨🇲', 'fr', 1, 1, NOW(), NOW()),
  ('CI', 'CIV', 'Côte d''Ivoire', 'Ivory Coast', '225', 'XOF', 'FCFA', '🇨🇮', 'fr', 1, 2, NOW(), NOW()),
  ('DZ', 'DZA', 'Algérie', 'Algeria', '213', 'DZD', 'DA', '🇩🇿', 'fr', 1, 3, NOW(), NOW());

-- ---------------------------------------------------------------------------
-- 2. The country column on the three tables that need it.
--
--    If a column already exists, MySQL errors with "Duplicate column name".
--    That means this file was already run — stop and skip to the verification
--    query at the bottom rather than forcing anything.
-- ---------------------------------------------------------------------------
ALTER TABLE `regions`    ADD COLUMN `country_id` bigint unsigned NULL AFTER `id`;
ALTER TABLE `cities`     ADD COLUMN `country_id` bigint unsigned NULL AFTER `id`;
ALTER TABLE `businesses` ADD COLUMN `country_id` bigint unsigned NULL AFTER `region_id`;

ALTER TABLE `regions`
  ADD CONSTRAINT `regions_country_id_foreign`
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

ALTER TABLE `cities`
  ADD CONSTRAINT `cities_country_id_foreign`
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

ALTER TABLE `businesses`
  ADD KEY `businesses_country_id_index` (`country_id`),
  ADD CONSTRAINT `businesses_country_id_foreign`
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- 3. Everything that existed before this file is Cameroonian.
-- ---------------------------------------------------------------------------
UPDATE `regions` SET `country_id` = (SELECT `id` FROM `countries` WHERE `code` = 'CM')
  WHERE `country_id` IS NULL;
UPDATE `cities`  SET `country_id` = (SELECT `id` FROM `countries` WHERE `code` = 'CM')
  WHERE `country_id` IS NULL;

-- Côte d'Ivoire — 14 districts
INSERT INTO `regions` (`country_id`,`code`,`name_fr`,`name_en`,`is_active`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'AB', 'Abidjan', 'Abidjan', 1, 1, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'BS', 'Bas-Sassandra', 'Bas-Sassandra', 1, 2, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'CO', 'Comoé', 'Comoé', 1, 3, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'DE', 'Denguélé', 'Denguélé', 1, 4, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'GD', 'Gôh-Djiboua', 'Gôh-Djiboua', 1, 5, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'LC', 'Lacs', 'Lakes', 1, 6, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'LG', 'Lagunes', 'Lagunes', 1, 7, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'MO', 'Montagnes', 'Mountains', 1, 8, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'SB', 'Sassandra-Marahoué', 'Sassandra-Marahoué', 1, 9, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'SM', 'Savanes', 'Savanes', 1, 10, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'VB', 'Vallée du Bandama', 'Bandama Valley', 1, 11, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'WO', 'Woroba', 'Woroba', 1, 12, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'YA', 'Yamoussoukro', 'Yamoussoukro', 1, 13, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'CI'), 'ZA', 'Zanzan', 'Zanzan', 1, 14, NOW(), NOW());

-- Algeria — 58 wilayas
INSERT INTO `regions` (`country_id`,`code`,`name_fr`,`name_en`,`is_active`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '01', 'Adrar', 'Adrar', 1, 1, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '02', 'Chlef', 'Chlef', 1, 2, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '03', 'Laghouat', 'Laghouat', 1, 3, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '04', 'Oum El Bouaghi', 'Oum El Bouaghi', 1, 4, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '05', 'Batna', 'Batna', 1, 5, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '06', 'Béjaïa', 'Béjaïa', 1, 6, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '07', 'Biskra', 'Biskra', 1, 7, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '08', 'Béchar', 'Béchar', 1, 8, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '09', 'Blida', 'Blida', 1, 9, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '10', 'Bouira', 'Bouira', 1, 10, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '11', 'Tamanrasset', 'Tamanrasset', 1, 11, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '12', 'Tébessa', 'Tébessa', 1, 12, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '13', 'Tlemcen', 'Tlemcen', 1, 13, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '14', 'Tiaret', 'Tiaret', 1, 14, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '15', 'Tizi Ouzou', 'Tizi Ouzou', 1, 15, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '16', 'Alger', 'Alger', 1, 16, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '17', 'Djelfa', 'Djelfa', 1, 17, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '18', 'Jijel', 'Jijel', 1, 18, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '19', 'Sétif', 'Sétif', 1, 19, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '20', 'Saïda', 'Saïda', 1, 20, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '21', 'Skikda', 'Skikda', 1, 21, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '22', 'Sidi Bel Abbès', 'Sidi Bel Abbès', 1, 22, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '23', 'Annaba', 'Annaba', 1, 23, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '24', 'Guelma', 'Guelma', 1, 24, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '25', 'Constantine', 'Constantine', 1, 25, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '26', 'Médéa', 'Médéa', 1, 26, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '27', 'Mostaganem', 'Mostaganem', 1, 27, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '28', 'M''Sila', 'M''Sila', 1, 28, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '29', 'Mascara', 'Mascara', 1, 29, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '30', 'Ouargla', 'Ouargla', 1, 30, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '31', 'Oran', 'Oran', 1, 31, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '32', 'El Bayadh', 'El Bayadh', 1, 32, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '33', 'Illizi', 'Illizi', 1, 33, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '34', 'Bordj Bou Arréridj', 'Bordj Bou Arréridj', 1, 34, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '35', 'Boumerdès', 'Boumerdès', 1, 35, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '36', 'El Tarf', 'El Tarf', 1, 36, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '37', 'Tindouf', 'Tindouf', 1, 37, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '38', 'Tissemsilt', 'Tissemsilt', 1, 38, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '39', 'El Oued', 'El Oued', 1, 39, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '40', 'Khenchela', 'Khenchela', 1, 40, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '41', 'Souk Ahras', 'Souk Ahras', 1, 41, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '42', 'Tipaza', 'Tipaza', 1, 42, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '43', 'Mila', 'Mila', 1, 43, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '44', 'Aïn Defla', 'Aïn Defla', 1, 44, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '45', 'Naâma', 'Naâma', 1, 45, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '46', 'Aïn Témouchent', 'Aïn Témouchent', 1, 46, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '47', 'Ghardaïa', 'Ghardaïa', 1, 47, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '48', 'Relizane', 'Relizane', 1, 48, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '49', 'Timimoun', 'Timimoun', 1, 49, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '50', 'Bordj Badji Mokhtar', 'Bordj Badji Mokhtar', 1, 50, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '51', 'Ouled Djellal', 'Ouled Djellal', 1, 51, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '52', 'Béni Abbès', 'Béni Abbès', 1, 52, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '53', 'In Salah', 'In Salah', 1, 53, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '54', 'In Guezzam', 'In Guezzam', 1, 54, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '55', 'Touggourt', 'Touggourt', 1, 55, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '56', 'Djanet', 'Djanet', 1, 56, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '57', 'El M''Ghair', 'El M''Ghair', 1, 57, NOW(), NOW()),
  ((SELECT `id` FROM `countries` WHERE `code` = 'DZ'), '58', 'El Meniaa', 'El Meniaa', 1, 58, NOW(), NOW());

-- ---------------------------------------------------------------------------
-- 4. Point existing businesses at the country of the region they already have.
--
--    Written as a correlated subquery, not UPDATE ... JOIN: the JOIN form is
--    rejected by some MariaDB configurations in a phpMyAdmin paste.
-- ---------------------------------------------------------------------------
UPDATE `businesses`
SET `country_id` = (SELECT `country_id` FROM `regions` WHERE `regions`.`id` = `businesses`.`region_id`)
WHERE `region_id` IS NOT NULL;

-- ---------------------------------------------------------------------------
-- 5. Tell Laravel these two migrations are done, so it never re-runs them.
--    Skipping this leaves the app trying to create a table that exists.
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES
  ('2026_07_30_140000_add_countries_for_multi_country_signup', (SELECT * FROM (SELECT COALESCE(MAX(`batch`),0)+1 FROM `migrations`) AS b)),
  ('2026_07_30_141000_seed_ivory_coast_and_algeria_regions',   (SELECT * FROM (SELECT COALESCE(MAX(`batch`),0)   FROM `migrations`) AS b2));

-- ---------------------------------------------------------------------------
-- 6. Verification. Expected: CM 10 regions, CI 14, DZ 58, and every one of your
--    businesses that has a region also has a country.
-- ---------------------------------------------------------------------------
SELECT c.`code` AS country, c.`currency_code` AS currency,
       (SELECT COUNT(*) FROM `regions` r WHERE r.`country_id` = c.`id`) AS regions
FROM `countries` c ORDER BY c.`sort_order`;

SELECT
  (SELECT COUNT(*) FROM `businesses` WHERE `region_id` IS NOT NULL AND `country_id` IS NULL)
    AS `businesses_with_a_region_but_no_country_MUST_BE_0`,
  (SELECT COUNT(*) FROM `regions` WHERE `country_id` IS NULL)
    AS `regions_with_no_country_MUST_BE_0`;
