<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Regions & Artisan Centres page: enrich the regions table with real Cameroon
// geographic facts and introduce a real artisan_centres table (shared by the
// admin list, admin detail and public centre pages), seeded verbatim.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            if (! Schema::hasColumn('regions', 'chef_lieu'))   $table->string('chef_lieu')->nullable();
            if (! Schema::hasColumn('regions', 'population'))   $table->unsignedBigInteger('population')->nullable();
            if (! Schema::hasColumn('regions', 'area_km2'))     $table->unsignedInteger('area_km2')->nullable();
            if (! Schema::hasColumn('regions', 'created_year')) $table->unsignedSmallInteger('created_year')->nullable();
            if (! Schema::hasColumn('regions', 'coordinator'))  $table->string('coordinator')->nullable();
            if (! Schema::hasColumn('regions', 'description_fr')) $table->text('description_fr')->nullable();
            if (! Schema::hasColumn('regions', 'description_en')) $table->text('description_en')->nullable();
            if (! Schema::hasColumn('regions', 'is_active'))    $table->boolean('is_active')->default(true);
            if (! Schema::hasColumn('regions', 'sort_order'))   $table->integer('sort_order')->default(0);
        });

        // Ensure the 10 regions exist (they are normally seeded, but guarantee
        // them at the migration level so every environment — incl. tests — has them).
        $regionNames = [
            'EN' => ['Extrême-Nord', 'Far North'], 'NO' => ['Nord', 'North'], 'AD' => ['Adamaoua', 'Adamawa'],
            'ES' => ['Est', 'East'], 'CE' => ['Centre', 'Centre'], 'LT' => ['Littoral', 'Littoral'],
            'OU' => ['Ouest', 'West'], 'NW' => ['Nord-Ouest', 'North-West'], 'SW' => ['Sud-Ouest', 'South-West'], 'SU' => ['Sud', 'South'],
        ];
        foreach ($regionNames as $code => [$nfr, $nen]) {
            DB::table('regions')->updateOrInsert(
                ['code' => $code],
                ['name_fr' => $nfr, 'name_en' => $nen, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // code => [chef-lieu, population, area km², created year, coordinator, desc_fr, sort]
        $facts = [
            'EN' => ['Maroua',      4200000, 34263, 1983, 'Oumarou Bakary',   'Région de l\'Extrême-Nord, savane et artisanat du cuir.', 10],
            'NO' => ['Garoua',      2440000, 66090, 1983, 'Abdoulaye Hamadou','Région du Nord, cuir, poterie et métallurgie.', 20],
            'AD' => ['Ngaoundéré',  1200000, 63701, 1983, 'Ibrahim Saidou',   'Région de l\'Adamaoua, plateau et élevage.', 30],
            'ES' => ['Bertoua',     840000,  109002,1983, 'Jean-Pierre Ela',  'Région de l\'Est, forêt, vannerie et poterie.', 40],
            'CE' => ['Yaoundé',     4712000, 68953, 1983, 'Jean Mbarga',      'Région du Centre, capitale Yaoundé. Cœur administratif et économique du Cameroun.', 50],
            'LT' => ['Douala',      3800000, 20248, 1983, 'Ekwalla Dipita',   'Région du Littoral, capitale économique Douala.', 60],
            'OU' => ['Bafoussam',   1950000, 13892, 1983, 'Célestin Kamga',   'Région de l\'Ouest, tissage, poterie et sculpture.', 70],
            'NW' => ['Bamenda',     1970000, 17300, 1983, 'Peter Ngwa',       'Région du Nord-Ouest, artisanat des Grassfields.', 80],
            'SW' => ['Buéa',        1540000, 24571, 1983, 'Emmanuel Eyong',   'Région du Sud-Ouest, mont Cameroun et artisanat côtier.', 90],
            'SU' => ['Ebolowa',     750000,  47191, 1983, 'Marc Ondoua',      'Région du Sud, forêt équatoriale et sculpture sur bois.', 100],
        ];
        foreach ($facts as $code => [$chef, $pop, $area, $year, $coord, $desc, $sort]) {
            DB::table('regions')->where('code', $code)->update([
                'chef_lieu' => $chef, 'population' => $pop, 'area_km2' => $area,
                'created_year' => $year, 'coordinator' => $coord, 'description_fr' => $desc,
                'is_active' => true, 'sort_order' => $sort,
            ]);
        }

        Schema::create('artisan_centres', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('type', 20)->default('principal'); // principal | secondaire
            $table->unsignedInteger('artisans_count')->default(0);
            $table->string('specialties_fr')->nullable();
            $table->string('specialties_en')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $rid = fn ($code) => DB::table('regions')->where('code', $code)->value('id');
        // [name_fr, region_code, type, artisans, specialties, city, sort]
        $centres = [
            ['Centre Artisanal de Yaoundé',       'CE', 'principal',  420, 'Sculpture, Vannerie, Bijouterie', 'Yaoundé',   10],
            ['Village Artisanal de Bafoussam',    'OU', 'principal',  380, 'Tissage, Poterie, Bois',          'Bafoussam', 20],
            ['Centre de l\'Artisanat de Maroua',  'EN', 'secondaire', 210, 'Cuir, Tissage, Métal',            'Maroua',    30],
            ['Maison de l\'Artisan de Douala',    'LT', 'principal',  510, 'Sculpture, Peinture, Couture',    'Douala',    40],
            ['Centre Artisanal de Bertoua',       'ES', 'secondaire', 190, 'Vannerie, Poterie, Bois',         'Bertoua',   50],
            ['Village des Métiers de Ngaoundéré', 'AD', 'principal',  240, 'Cuir, Métallurgie, Élevage',      'Ngaoundéré',60],
            ['Centre Artisanal de Bamenda',       'NW', 'principal',  330, 'Tissage, Sculpture, Perles',      'Bamenda',   70],
            ['Maison de l\'Artisan de Buéa',      'SW', 'secondaire', 175, 'Vannerie, Poterie, Bois',         'Buéa',      80],
            ['Centre Artisanal d\'Ebolowa',       'SU', 'secondaire', 160, 'Sculpture sur bois, Vannerie',    'Ebolowa',   90],
            ['Village Artisanal de Garoua',       'NO', 'principal',  290, 'Cuir, Poterie, Calebasses',       'Garoua',    100],
            ['Centre des Métiers de Foumban',     'OU', 'principal',  460, 'Bronze, Perles, Sculpture',       'Foumban',   110],
            ['Atelier Régional de Kribi',         'SU', 'secondaire', 130, 'Coquillages, Vannerie',           'Kribi',     120],
        ];
        $now = now();
        foreach ($centres as $i => [$name, $code, $type, $artisans, $spec, $city, $sort]) {
            DB::table('artisan_centres')->insert([
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'name_fr' => $name, 'name_en' => $name,
                'region_id' => $rid($code), 'type' => $type,
                'artisans_count' => $artisans, 'specialties_fr' => $spec, 'specialties_en' => $spec,
                'city' => $city,
                'description_fr' => 'Centre d\'artisanat regroupant des artisans de la région, dédié à la promotion et à la transmission des savoir-faire locaux.',
                'description_en' => 'A craft centre bringing together regional artisans, dedicated to promoting and passing on local know-how.',
                'contact_phone' => '+237 6 ' . rand(70, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'status' => 'active', 'sort_order' => $sort,
                'created_at' => $now->copy()->subDays(rand(30, 400)), 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_centres');
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['chef_lieu', 'population', 'area_km2', 'created_year', 'coordinator', 'description_fr', 'description_en', 'is_active', 'sort_order']);
        });
    }
};
