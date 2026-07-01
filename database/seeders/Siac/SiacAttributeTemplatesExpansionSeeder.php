<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiacAttributeTemplatesExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $industryIds = DB::table('industries')->pluck('id', 'slug');

        $templates = [
            // Aquaculture — production, feeding, water conditions, health
            ['industry' => 'aquaculture', 'field_key' => 'type_alimentation', 'name_fr' => 'Type d\'alimentation', 'name_en' => 'Feed type', 'field_type' => 'select', 'sort_order' => 4,
                'options_fr' => ['Aliment naturel', 'Aliment commercial', 'Mixte'], 'options_en' => ['Natural feed', 'Commercial feed', 'Mixed']],
            ['industry' => 'aquaculture', 'field_key' => 'marque_aliment', 'name_fr' => 'Marque d\'aliment', 'name_en' => 'Feed brand', 'field_type' => 'text', 'sort_order' => 5],
            ['industry' => 'aquaculture', 'field_key' => 'taux_conversion', 'name_fr' => 'Taux de conversion alimentaire (FCR)', 'name_en' => 'Feed conversion ratio (FCR)', 'field_type' => 'number', 'unit' => 'ratio', 'sort_order' => 6],
            ['industry' => 'aquaculture', 'field_key' => 'temperature_eau', 'name_fr' => 'Température de l\'eau', 'name_en' => 'Water temperature', 'field_type' => 'number', 'unit' => '°C', 'sort_order' => 7],
            ['industry' => 'aquaculture', 'field_key' => 'ph_eau', 'name_fr' => 'pH de l\'eau', 'name_en' => 'Water pH', 'field_type' => 'number', 'unit' => 'pH', 'sort_order' => 8],
            ['industry' => 'aquaculture', 'field_key' => 'oxygene_dissous', 'name_fr' => 'Oxygène dissous', 'name_en' => 'Dissolved oxygen', 'field_type' => 'number', 'unit' => 'mg/L', 'sort_order' => 9],
            ['industry' => 'aquaculture', 'field_key' => 'sans_maladie', 'name_fr' => 'Sans maladie', 'name_en' => 'Disease free', 'field_type' => 'boolean', 'sort_order' => 10],
            ['industry' => 'aquaculture', 'field_key' => 'vaccination', 'name_fr' => 'Vacciné', 'name_en' => 'Vaccinated', 'field_type' => 'boolean', 'sort_order' => 11],
            ['industry' => 'aquaculture', 'field_key' => 'taux_survie', 'name_fr' => 'Taux de survie', 'name_en' => 'Survival rate', 'field_type' => 'number', 'unit' => '%', 'sort_order' => 12],

            // Artisanat — technique, finish, production time
            ['industry' => 'artisanat', 'field_key' => 'technique_fabrication', 'name_fr' => 'Technique de fabrication', 'name_en' => 'Crafting technique', 'field_type' => 'text', 'sort_order' => 4],
            ['industry' => 'artisanat', 'field_key' => 'couleur', 'name_fr' => 'Couleur', 'name_en' => 'Color', 'field_type' => 'text', 'sort_order' => 5],
            ['industry' => 'artisanat', 'field_key' => 'finition', 'name_fr' => 'Finition', 'name_en' => 'Finish', 'field_type' => 'select', 'sort_order' => 6,
                'options_fr' => ['Brut', 'Verni', 'Peint', 'Teint'], 'options_en' => ['Raw', 'Varnished', 'Painted', 'Dyed']],
            ['industry' => 'artisanat', 'field_key' => 'duree_fabrication', 'name_fr' => 'Durée de fabrication', 'name_en' => 'Production time', 'field_type' => 'text', 'sort_order' => 7],

            // Agriculture — variety, moisture, crop year, processing
            ['industry' => 'agriculture', 'field_key' => 'variete', 'name_fr' => 'Variété', 'name_en' => 'Variety', 'field_type' => 'text', 'sort_order' => 1],
            ['industry' => 'agriculture', 'field_key' => 'calibre', 'name_fr' => 'Calibre / Taille', 'name_en' => 'Size / Caliber', 'field_type' => 'text', 'sort_order' => 2],
            ['industry' => 'agriculture', 'field_key' => 'taux_humidite', 'name_fr' => 'Taux d\'humidité', 'name_en' => 'Moisture content', 'field_type' => 'number', 'unit' => '%', 'sort_order' => 3],
            ['industry' => 'agriculture', 'field_key' => 'annee_recolte', 'name_fr' => 'Année de récolte', 'name_en' => 'Crop year', 'field_type' => 'number', 'sort_order' => 4],
            ['industry' => 'agriculture', 'field_key' => 'type_transformation', 'name_fr' => 'Type de transformation', 'name_en' => 'Processing type', 'field_type' => 'select', 'sort_order' => 5,
                'options_fr' => ['Brut', 'Séché', 'Transformé'], 'options_en' => ['Raw', 'Dried', 'Processed']],

            // Textile & Mode — fabric, size, pattern
            ['industry' => 'textile-mode', 'field_key' => 'matiere', 'name_fr' => 'Matière', 'name_en' => 'Fabric material', 'field_type' => 'text', 'sort_order' => 1],
            ['industry' => 'textile-mode', 'field_key' => 'taille_vetement', 'name_fr' => 'Taille', 'name_en' => 'Clothing size', 'field_type' => 'text', 'sort_order' => 2],
            ['industry' => 'textile-mode', 'field_key' => 'motif', 'name_fr' => 'Motif', 'name_en' => 'Pattern', 'field_type' => 'text', 'sort_order' => 3],
            ['industry' => 'textile-mode', 'field_key' => 'technique_confection', 'name_fr' => 'Technique de confection', 'name_en' => 'Sewing technique', 'field_type' => 'text', 'sort_order' => 4],

            // Agroalimentaire — taste, aroma, ingredients, storage, spice level
            ['industry' => 'agroalimentaire', 'field_key' => 'saveur', 'name_fr' => 'Profil de saveur', 'name_en' => 'Taste profile', 'field_type' => 'text', 'sort_order' => 1],
            ['industry' => 'agroalimentaire', 'field_key' => 'arome', 'name_fr' => 'Arôme', 'name_en' => 'Aroma', 'field_type' => 'text', 'sort_order' => 2],
            ['industry' => 'agroalimentaire', 'field_key' => 'ingredients', 'name_fr' => 'Ingrédients', 'name_en' => 'Ingredients', 'field_type' => 'text', 'sort_order' => 3],
            ['industry' => 'agroalimentaire', 'field_key' => 'mode_conservation', 'name_fr' => 'Mode de conservation', 'name_en' => 'Storage method', 'field_type' => 'text', 'sort_order' => 4],
            ['industry' => 'agroalimentaire', 'field_key' => 'niveau_piquant', 'name_fr' => 'Niveau de piquant', 'name_en' => 'Spice level', 'field_type' => 'select', 'sort_order' => 5,
                'options_fr' => ['Doux', 'Moyen', 'Fort', 'Très fort'], 'options_en' => ['Mild', 'Medium', 'Hot', 'Very hot']],
        ];

        $inserted = 0;
        foreach ($templates as $t) {
            $industryId = $industryIds[$t['industry']] ?? null;
            if (! $industryId) {
                continue;
            }

            $exists = DB::table('attribute_templates')
                ->where('industry_id', $industryId)
                ->where('field_key', $t['field_key'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('attribute_templates')->insert([
                'industry_id'   => $industryId,
                'field_key'     => $t['field_key'],
                'name_fr'       => $t['name_fr'],
                'name_en'       => $t['name_en'],
                'field_type'    => $t['field_type'],
                'unit'          => $t['unit'] ?? null,
                'options_fr'    => isset($t['options_fr']) ? json_encode($t['options_fr']) : null,
                'options_en'    => isset($t['options_en']) ? json_encode($t['options_en']) : null,
                'is_required'   => false,
                'is_filterable' => false,
                'sort_order'    => $t['sort_order'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $inserted++;
        }

        $this->command->info("  {$inserted} additional attribute templates seeded.");
    }
}
