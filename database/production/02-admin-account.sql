-- =============================================================================
--  02-admin-account.sql  --  import this SECOND, after the main database file.
--
--  WHY THIS FILE EXISTS
--  The main import deliberately contains no account that can log in: shipping a
--  developer's credentials to a live server is how platforms get taken over. But
--  it left nothing in their place, so importing it on its own gives you a site
--  you cannot get into. The project's own answer was to run an artisan seeder
--  over SSH, which is not the deal on shared hosting -- so this file does the
--  same job through phpMyAdmin.
--
--  It creates ONE super administrator. The password is in START-HERE.md.
--  Change it as soon as you are in.
-- =============================================================================

SET NAMES utf8mb4;

INSERT INTO `users`
  (`id`, `name`, `email`, `password`, `account_type`, `status`,
   `is_email_verified`, `language_preference`, `created_at`, `updated_at`)
VALUES
  ('467bb8e1-f7b9-4469-bc05-2308f0e879e0', 'Administrateur', 'nshomejude@gmail.com', '$2y$10$JtUaDtnEaiUQ1xHHZ1EXZO/ViNkckrViZoA5HkmrQXGHM658zya1.',
   'admin', 'active', 1, 'fr', NOW(), NOW());

-- Spatie stores a role against a guard. This platform uses the `sanctum` guard,
-- not the `web` default. A role written against the wrong guard is silently
-- ignored -- you would log in successfully and have no permissions at all.
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`)
SELECT r.`id`, 'App\\Modules\\Auth\\Models\\User', '467bb8e1-f7b9-4469-bc05-2308f0e879e0'
  FROM `roles` r
 WHERE r.`name` = 'super_admin' AND r.`guard_name` = 'sanctum'
 LIMIT 1;

-- Proof. Both numbers must come back 1.
SELECT
  (SELECT COUNT(*) FROM `users` WHERE `id` = '467bb8e1-f7b9-4469-bc05-2308f0e879e0')                  AS admin_user_created,
  (SELECT COUNT(*) FROM `model_has_roles` WHERE `model_id` = '467bb8e1-f7b9-4469-bc05-2308f0e879e0')  AS super_admin_role_granted;
