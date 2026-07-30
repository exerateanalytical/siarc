<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Introduce a country dimension so artisans outside Cameroon can register.
 *
 * The platform was built single-country: `regions` held Cameroon's ten regions
 * and nothing recorded which country a region belonged to, because there was
 * only ever one. Adding Côte d'Ivoire and Algeria therefore needs a real
 * country table rather than more rows in `regions` — otherwise an Ivorian
 * artisan would appear to be in a Cameroonian region.
 *
 * `businesses.country_id` is denormalised on purpose. The country of a business
 * is derivable through region_id, but region_id is nullable (221 of the imported
 * SIARC artisans have no commune match, and a business can be created before its
 * region is chosen), and the public directory filter needs one indexed column to
 * group by. Backfilled from the region here and kept in step by the application.
 *
 * Currency lives on the country because it is a property of the country, not of
 * the platform: Cameroon uses XAF, Côte d'Ivoire XOF, Algeria DZD. Products
 * already carry their own `price_currency`, so nothing about existing prices
 * changes — this just gives the signup flow a correct default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            // ISO 3166-1: alpha-2 is what forms and URLs use, alpha-3 is what
            // shipping and customs paperwork asks for.
            $table->char('code', 2)->unique();
            $table->char('code3', 3)->unique();
            $table->string('name_fr');
            $table->string('name_en');
            // Stored without the leading '+' so it can be concatenated or
            // compared without stripping punctuation first.
            $table->string('dial_code', 6);
            $table->char('currency_code', 3);
            $table->string('currency_symbol', 12);
            $table->string('flag_emoji', 16)->nullable();
            // The default language a new signup from this country is offered.
            // Algeria is Arabic-speaking but the platform is fr/en only, so
            // French is the honest default there rather than a language we
            // cannot yet render.
            $table->char('default_lang', 2)->default('fr');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seeded here rather than in a seeder: production is updated by running
        // migrations, and a country table with no countries would break every
        // signup form the moment this deploys.
        $now = now();
        DB::table('countries')->insert([
            [
                'code' => 'CM', 'code3' => 'CMR',
                'name_fr' => 'Cameroun', 'name_en' => 'Cameroon',
                'dial_code' => '237', 'currency_code' => 'XAF', 'currency_symbol' => 'FCFA',
                'flag_emoji' => '🇨🇲', 'default_lang' => 'fr',
                'is_active' => true, 'sort_order' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'code' => 'CI', 'code3' => 'CIV',
                'name_fr' => "Côte d'Ivoire", 'name_en' => 'Ivory Coast',
                'dial_code' => '225', 'currency_code' => 'XOF', 'currency_symbol' => 'FCFA',
                'flag_emoji' => '🇨🇮', 'default_lang' => 'fr',
                'is_active' => true, 'sort_order' => 2,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'code' => 'DZ', 'code3' => 'DZA',
                'name_fr' => 'Algérie', 'name_en' => 'Algeria',
                'dial_code' => '213', 'currency_code' => 'DZD', 'currency_symbol' => 'DA',
                'flag_emoji' => '🇩🇿', 'default_lang' => 'fr',
                'is_active' => true, 'sort_order' => 3,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        $cameroonId = (int) DB::table('countries')->where('code', 'CM')->value('id');

        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('id')
                ->constrained('countries')->nullOnDelete();
        });

        // Every region that existed before this migration is Cameroonian.
        DB::table('regions')->update(['country_id' => $cameroonId]);

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('region_id')
                ->constrained('countries')->nullOnDelete();
            $table->index('country_id');
        });

        // Backfill through the region where there is one. A business with no
        // region keeps a null country rather than being assumed Cameroonian —
        // guessing would put a real artisan in the wrong country's directory.
        //
        // Written as a correlated subquery, not `UPDATE ... JOIN`: the JOIN form
        // is MySQL/MariaDB-only and the test suite runs on SQLite, where it is a
        // syntax error. This form is valid on all three.
        DB::statement(
            'UPDATE businesses
             SET country_id = (SELECT country_id FROM regions WHERE regions.id = businesses.region_id)
             WHERE region_id IS NOT NULL'
        );

        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('id')
                ->constrained('countries')->nullOnDelete();
        });
        DB::table('cities')->update(['country_id' => $cameroonId]);
    }

    public function down(): void
    {
        foreach (['cities', 'businesses', 'regions'] as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->dropForeign([$t . '_country_id_foreign']);
                $table->dropColumn('country_id');
            });
        }

        Schema::dropIfExists('countries');
    }
};
