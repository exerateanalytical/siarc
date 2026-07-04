<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The events listing showed type / city / region / price on the frontend only —
// promote them to real event attributes and backfill the seeded events.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_type', 30)->default('autres')->after('industry_id');
            $table->string('region_key', 30)->nullable()->after('event_type');
            $table->string('city_fr')->nullable()->after('region_key');
            $table->string('price_fr')->nullable()->after('city_fr');
            $table->string('price_en')->nullable()->after('price_fr');
        });

        $rows = [
            'journees-nationales-artisanat-camerounais-2025' => ['salons',      'centre',       'Yaoundé, Centre',        'Entrée libre',  'Free entry'],
            'festival-arts-traditions-bamoun'                => ['festivals',   'ouest',        'Foumban, Ouest',         '2 000 FCFA',    '2,000 FCFA'],
            'atelier-poterie-traditionnelle'                 => ['ateliers',    'extreme-nord', 'Maroua, Extrême-Nord',   '5 000 FCFA',    '5,000 FCFA'],
            'marche-createurs-eco-responsables'              => ['marches',     'littoral',     'Douala, Littoral',       'Entrée libre',  'Free entry'],
            'conference-artisanat-developpement-durable'     => ['conferences', 'centre',       'Yaoundé, Centre',        '3 000 FCFA',    '3,000 FCFA'],
            'prix-national-jeune-artisan-2025'               => ['concours',    'centre',       'Yaoundé, Centre',        'Entrée libre',  'Free entry'],
            'siarc-2026'                                     => ['salons',      'centre',       'Yaoundé, Centre',        'Entrée libre',  'Free entry'],
            'festival-national-du-textile'                   => ['festivals',   'ouest',        'Bafoussam, Ouest',       'Entrée libre',  'Free entry'],
            'expo-artisanat-jeunesse'                        => ['salons',      'littoral',     'Douala, Littoral',       'Entrée libre',  'Free entry'],
            'semaine-nationale-du-bois'                      => ['salons',      'sud',          'Ebolowa, Sud',           'Entrée libre',  'Free entry'],
        ];

        foreach ($rows as $slug => [$type, $regionKey, $city, $priceFr, $priceEn]) {
            DB::table('events')->where('slug', $slug)->update([
                'event_type' => $type,
                'region_key' => $regionKey,
                'city_fr'    => $city,
                'price_fr'   => $priceFr,
                'price_en'   => $priceEn,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'region_key', 'city_fr', 'price_fr', 'price_en']);
        });
    }
};
