<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiacRegionsSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['code' => 'CE', 'name_fr' => 'Centre',        'name_en' => 'Centre',          'cities' => ['Yaoundé', 'Mbalmayo', 'Obala', 'Bafia', 'Nanga-Eboko']],
            ['code' => 'LT', 'name_fr' => 'Littoral',      'name_en' => 'Littoral',        'cities' => ['Douala', 'Edéa', 'Nkongsamba', 'Loum', 'Mbanga']],
            ['code' => 'OU', 'name_fr' => 'Ouest',         'name_en' => 'West',            'cities' => ['Bafoussam', 'Dschang', 'Foumban', 'Mbouda', 'Bangangté']],
            ['code' => 'NW', 'name_fr' => 'Nord-Ouest',    'name_en' => 'North West',      'cities' => ['Bamenda', 'Kumbo', 'Wum', 'Ndop', 'Fundong']],
            ['code' => 'SW', 'name_fr' => 'Sud-Ouest',     'name_en' => 'South West',      'cities' => ['Buea', 'Limbé', 'Kumba', 'Mamfé', 'Mundemba']],
            ['code' => 'NO', 'name_fr' => 'Nord',          'name_en' => 'North',           'cities' => ['Garoua', 'Guider', 'Pitoa', 'Rey Bouba', 'Tcholliré']],
            ['code' => 'AD', 'name_fr' => 'Adamaoua',      'name_en' => 'Adamawa',         'cities' => ['Ngaoundéré', 'Meiganga', 'Tibati', 'Banyo', 'Tignère']],
            ['code' => 'ES', 'name_fr' => 'Est',           'name_en' => 'East',            'cities' => ['Bertoua', 'Batouri', 'Abong-Mbang', 'Belabo', 'Yokadouma']],
            ['code' => 'SU', 'name_fr' => 'Sud',           'name_en' => 'South',           'cities' => ['Ebolowa', 'Kribi', 'Sangmélima', 'Ambam', 'Mvangué']],
            ['code' => 'EN', 'name_fr' => 'Extrême-Nord',  'name_en' => 'Far North',       'cities' => ['Maroua', 'Mora', 'Kousséri', 'Yagoua', 'Kaélé']],
        ];

        // Idempotent: earlier migrations already seed the region/city reference
        // data, so a plain insert here made `migrate && db:seed` fail on a fresh
        // install with a duplicate-key error. Re-running the seeder must be safe.
        $cityCount = 0;

        foreach ($regions as $r) {
            $regionId = DB::table('regions')->where('code', $r['code'])->value('id');

            if (! $regionId) {
                $regionId = DB::table('regions')->insertGetId([
                    'code'       => $r['code'],
                    'name_fr'    => $r['name_fr'],
                    'name_en'    => $r['name_en'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($r['cities'] as $city) {
                $exists = DB::table('cities')
                    ->where('region_id', $regionId)
                    ->where('name_fr', $city)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('cities')->insert([
                    'region_id'  => $regionId,
                    'name_fr'    => $city,
                    'name_en'    => $city,
                    'slug'       => \Illuminate\Support\Str::slug($city),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cityCount++;
            }
        }

        $this->command->info('  Regions ensured; ' . $cityCount . ' new cities seeded.');
    }
}
