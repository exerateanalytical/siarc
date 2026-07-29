-- =============================================================================
--  2026-07-29-update-subscription-pricing.sql
--
--  Sets the four real subscription tiers, confirmed by the owner:
--    Basic (individual artisan)  15 000 FCFA / year
--    Standard                    25 000 FCFA / year
--    Cooperative                 50 000 FCFA / year
--    Centre (craft centre)      100 000 FCFA / year
--
--  Updates existing rows IN PLACE (same id, same slug where it already existed)
--  rather than deleting and re-inserting, because a payment or a subscription
--  already on file may reference a plan by id -- deleting the row would orphan
--  that reference. The 5th row ("Personnalisé"/custom-quote) is deactivated,
--  not deleted, for the same reason: nothing the owner described asked for a
--  quote tier, but a row referenced by history must not disappear.
--
--  Safe to run more than once -- every statement sets an exact value.
-- =============================================================================

UPDATE subscription_plans SET name_fr='Basic',       name_en='Basic',       price_yearly=15000,  is_active=1 WHERE slug='basic';
UPDATE subscription_plans SET name_fr='Standard',    name_en='Standard',    price_yearly=25000,  is_active=1 WHERE slug='standard';
UPDATE subscription_plans SET slug='cooperative', name_fr='Coopérative',   name_en='Cooperative', price_yearly=50000,  is_active=1 WHERE slug='premium';
UPDATE subscription_plans SET slug='centre',       name_fr='Centre artisanal', name_en='Craft Centre', price_yearly=100000, is_active=1 WHERE slug='entreprise';
UPDATE subscription_plans SET is_active=0 WHERE slug='personnalise';

SELECT id, slug, name_fr, price_yearly, is_active, sort_order FROM subscription_plans ORDER BY sort_order;
