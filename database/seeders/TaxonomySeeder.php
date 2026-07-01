<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // Regions of Cameroon
        $regions = [
            ['name_fr' => 'Adamaoua', 'name_en' => 'Adamawa', 'code' => 'AD'],
            ['name_fr' => 'Centre', 'name_en' => 'Centre', 'code' => 'CE'],
            ['name_fr' => 'Est', 'name_en' => 'East', 'code' => 'ES'],
            ['name_fr' => 'Extrême-Nord', 'name_en' => 'Far North', 'code' => 'EN'],
            ['name_fr' => 'Littoral', 'name_en' => 'Littoral', 'code' => 'LT'],
            ['name_fr' => 'Nord', 'name_en' => 'North', 'code' => 'NO'],
            ['name_fr' => 'Nord-Ouest', 'name_en' => 'North West', 'code' => 'NW'],
            ['name_fr' => 'Ouest', 'name_en' => 'West', 'code' => 'OU'],
            ['name_fr' => 'Sud', 'name_en' => 'South', 'code' => 'SU'],
            ['name_fr' => 'Sud-Ouest', 'name_en' => 'South West', 'code' => 'SW'],
        ];

        foreach ($regions as $r) {
            DB::table('regions')->insertOrIgnore(array_merge($r, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Major cities
        $littoral = DB::table('regions')->where('code', 'LT')->value('id');
        $centre   = DB::table('regions')->where('code', 'CE')->value('id');
        $ouest    = DB::table('regions')->where('code', 'OU')->value('id');
        $sw       = DB::table('regions')->where('code', 'SW')->value('id');

        $cities = [
            ['region_id' => $littoral, 'name_fr' => 'Douala', 'name_en' => 'Douala', 'slug' => 'douala', 'latitude' => 4.0511, 'longitude' => 9.7679],
            ['region_id' => $centre,   'name_fr' => 'Yaoundé', 'name_en' => 'Yaoundé', 'slug' => 'yaounde', 'latitude' => 3.8480, 'longitude' => 11.5021],
            ['region_id' => $ouest,    'name_fr' => 'Bafoussam', 'name_en' => 'Bafoussam', 'slug' => 'bafoussam', 'latitude' => 5.4737, 'longitude' => 10.4162],
            ['region_id' => $sw,       'name_fr' => 'Limbé', 'name_en' => 'Limbe', 'slug' => 'limbe', 'latitude' => 4.0186, 'longitude' => 9.1993],
        ];

        foreach ($cities as $c) {
            DB::table('cities')->insertOrIgnore(array_merge($c, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Industries
        $industries = [
            ['name_fr' => 'Agriculture & Agroalimentaire', 'name_en' => 'Agriculture & Food', 'slug' => 'agriculture', 'icon' => 'ti-plant'],
            ['name_fr' => 'Banque & Finance', 'name_en' => 'Banking & Finance', 'slug' => 'finance', 'icon' => 'ti-building-bank'],
            ['name_fr' => 'Construction & Immobilier', 'name_en' => 'Construction & Real Estate', 'slug' => 'construction', 'icon' => 'ti-building'],
            ['name_fr' => 'Commerce & Distribution', 'name_en' => 'Trade & Distribution', 'slug' => 'commerce', 'icon' => 'ti-shopping-cart'],
            ['name_fr' => 'Énergie & Mines', 'name_en' => 'Energy & Mining', 'slug' => 'energie', 'icon' => 'ti-bolt'],
            ['name_fr' => 'Santé', 'name_en' => 'Healthcare', 'slug' => 'sante', 'icon' => 'ti-heart-rate-monitor'],
            ['name_fr' => 'Technologies & Numérique', 'name_en' => 'Tech & Digital', 'slug' => 'tech', 'icon' => 'ti-cpu'],
            ['name_fr' => 'Télécommunications', 'name_en' => 'Telecommunications', 'slug' => 'telecom', 'icon' => 'ti-wifi'],
            ['name_fr' => 'Transport & Logistique', 'name_en' => 'Transport & Logistics', 'slug' => 'transport', 'icon' => 'ti-truck'],
            ['name_fr' => 'Éducation & Formation', 'name_en' => 'Education & Training', 'slug' => 'education', 'icon' => 'ti-school'],
            ['name_fr' => 'Tourisme & Hôtellerie', 'name_en' => 'Tourism & Hospitality', 'slug' => 'tourisme', 'icon' => 'ti-beach'],
            ['name_fr' => 'Services aux Entreprises', 'name_en' => 'Business Services', 'slug' => 'services', 'icon' => 'ti-briefcase'],
        ];

        foreach ($industries as $i) {
            DB::table('industries')->insertOrIgnore(array_merge($i, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }

        // Verification tiers
        $tiers = [
            ['name' => 'Non Vérifié', 'slug' => 'unverified', 'description_fr' => 'Entreprise non encore vérifiée', 'description_en' => 'Company not yet verified', 'level' => 0],
            ['name' => 'Basique', 'slug' => 'basic', 'description_fr' => 'Vérification documentaire de base (RCCM)', 'description_en' => 'Basic document check (RCCM)', 'level' => 1],
            ['name' => 'Vérifié', 'slug' => 'verified', 'description_fr' => 'Vérification complète (RCCM + NIU + CNPS)', 'description_en' => 'Full check (RCCM + NIU + CNPS)', 'level' => 2],
            ['name' => 'Certifié', 'slug' => 'certified', 'description_fr' => 'Certification gouvernementale complète', 'description_en' => 'Full government certification', 'level' => 3],
        ];

        foreach ($tiers as $t) {
            DB::table('verification_tiers')->insertOrIgnore(array_merge($t, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
