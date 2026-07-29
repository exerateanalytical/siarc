-- =============================================================================
--  2026-07-29-fix-platform-name.sql
--
--  system_settings still carried the platform's pre-rename working name from
--  the very first day of the project (2026-07-01): "Galerie Virtuelle
--  Nationale de l'Artisanat" / "National Virtual Gallery of Crafts". No
--  current view renders it (confirmed: the only consumers of
--  system_settings are the admin settings screen and SystemSettings::get()),
--  but it is visible to any admin who opens Paramètres, and the platform's
--  own name must read the same everywhere, on principle -- so it is corrected
--  here rather than left as a landmine for whatever next reads it.
--
--  Safe to run more than once.
-- =============================================================================

UPDATE system_settings SET value = 'Artisan Hub 237' WHERE `key` = 'platform_name_fr';
UPDATE system_settings SET value = 'Artisan Hub 237' WHERE `key` = 'platform_name_en';

SELECT `key`, value FROM system_settings WHERE `key` LIKE 'platform_name%';
