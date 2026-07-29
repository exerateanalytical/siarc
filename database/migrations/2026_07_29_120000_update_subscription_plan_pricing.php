<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Brings the migration-seeded `subscription_plans` in line with the four real
 * tiers the owner confirmed (2026-07-29): Basic 15 000, Standard 25 000,
 * Coopérative/Cooperative 50 000, Centre artisanal/Craft Centre 100 000 XAF a
 * year — the same values already applied by hand to the live database via
 * database/production/patches/2026-07-29-update-subscription-pricing.sql.
 *
 * Without this, a fresh test database built from migrations still carries the
 * placeholder demo prices (30 000 / 60 000 / 120 000 / 300 000) and the old
 * slugs `premium` / `entreprise`, which would silently diverge from what the
 * live site actually charges. Rows are updated in place — never deleted — so
 * any `business_subscriptions` row already pointing at a plan id keeps
 * resolving to the same plan under its new name and price.
 *
 * The 5th row, `personnalise` (a custom/quote tier), is deactivated rather
 * than removed: nothing asked for a quote tier, but a row history may
 * reference must not disappear.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscription_plans')->where('slug', 'basic')->update([
            'name_fr' => 'Basic', 'name_en' => 'Basic', 'price_yearly' => 15000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'standard')->update([
            'name_fr' => 'Standard', 'name_en' => 'Standard', 'price_yearly' => 25000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'premium')->update([
            'slug' => 'cooperative', 'name_fr' => 'Coopérative', 'name_en' => 'Cooperative',
            'price_yearly' => 50000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'entreprise')->update([
            'slug' => 'centre', 'name_fr' => 'Centre artisanal', 'name_en' => 'Craft Centre',
            'price_yearly' => 100000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'personnalise')->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('slug', 'basic')->update([
            'name_fr' => 'Basic', 'name_en' => 'Basic', 'price_yearly' => 30000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'standard')->update([
            'name_fr' => 'Standard', 'name_en' => 'Standard', 'price_yearly' => 60000, 'is_active' => true,
        ]);
        DB::table('subscription_plans')->where('slug', 'cooperative')->update([
            'slug' => 'premium', 'name_fr' => 'Premium', 'name_en' => 'Premium', 'price_yearly' => 120000,
        ]);
        DB::table('subscription_plans')->where('slug', 'centre')->update([
            'slug' => 'entreprise', 'name_fr' => 'Entreprise', 'name_en' => 'Enterprise', 'price_yearly' => 300000,
        ]);
        DB::table('subscription_plans')->where('slug', 'personnalise')->update(['is_active' => true]);
    }
};
