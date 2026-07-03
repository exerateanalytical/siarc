<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the 6 events shown on the official "events page" design so every ticket's
 * "Voir détails" opens a real, admin-editable event. Dates are the design's 2025
 * dates, verbatim. Idempotent — safe to re-run.
 */
class DesignEventsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['journees-nationales-artisanat-camerounais-2025',
             'Journées Nationales de l\'Artisanat Camerounais 2025', 'National Days of Cameroonian Craftsmanship 2025',
             '2025-08-20 09:00', '2025-08-20 18:00', 'Palais des Congrès de Yaoundé, Yaoundé',
             'Un événement national pour promouvoir l\'excellence artisanale camerounaise et valoriser nos savoir-faire.',
             'A national event to promote Cameroonian artisanal excellence and showcase our know-how.'],
            ['festival-arts-traditions-bamoun',
             'Festival des Arts et Traditions Bamoun', 'Bamoun Arts and Traditions Festival',
             '2025-08-24 10:00', '2025-08-24 20:00', 'Palais Royal de Foumban, Foumban',
             'Célébration de la richesse culturelle Bamoun à travers l\'art, la musique, la danse et l\'artisanat traditionnel.',
             'A celebration of Bamoun cultural wealth through art, music, dance and traditional craftsmanship.'],
            ['atelier-poterie-traditionnelle',
             'Atelier de Poterie Traditionnelle', 'Traditional Pottery Workshop',
             '2025-09-05 09:00', '2025-09-05 16:00', 'Centre d\'Artisanat de Maroua, Maroua',
             'Apprenez les techniques ancestrales de la poterie avec des maîtres artisans.',
             'Learn ancestral pottery techniques with master artisans.'],
            ['marche-createurs-eco-responsables',
             'Marché des Créateurs Éco-responsables', 'Eco-responsible Creators Market',
             '2025-09-12 08:00', '2025-09-12 17:00', 'Place des Fêtes de Douala, Douala',
             'Rencontrez des créateurs engagés pour un artisanat durable et respectueux de l\'environnement.',
             'Meet creators committed to sustainable, environmentally friendly craftsmanship.'],
            ['conference-artisanat-developpement-durable',
             'Conférence : L\'artisanat au cœur du développement durable', 'Conference: Craftsmanship at the heart of sustainable development',
             '2025-09-28 14:00', '2025-09-28 19:00', 'Institut Français du Cameroun, Yaoundé',
             'Échanges et réflexions sur le rôle de l\'artisanat dans le développement économique et social du Cameroun.',
             'Discussions and reflections on the role of craftsmanship in Cameroon\'s economic and social development.'],
            ['prix-national-jeune-artisan-2025',
             'Prix National du Jeune Artisan 2025', 'National Young Artisan Award 2025',
             '2025-10-10 09:00', '2025-10-10 18:00', 'Palais des Congrès de Yaoundé, Yaoundé',
             'Valorisons la jeunesse et l\'innovation dans l\'artisanat. À vos créations !',
             'Let\'s celebrate youth and innovation in craftsmanship. Get creating!'],
        ];

        $industryId = DB::table('industries')->where('slug', 'artisanat')->value('id');

        foreach ($rows as [$slug, $nameFr, $nameEn, $starts, $ends, $location, $descFr, $descEn]) {
            DB::table('events')->updateOrInsert(
                ['slug' => $slug],
                [
                    'uuid'           => DB::table('events')->where('slug', $slug)->value('uuid') ?? Str::uuid()->toString(),
                    'name_fr'        => $nameFr,
                    'name_en'        => $nameEn,
                    'description_fr' => $descFr,
                    'description_en' => $descEn,
                    'location_fr'    => $location,
                    'location_en'    => $location,
                    'starts_at'      => $starts,
                    'ends_at'        => $ends,
                    'industry_id'    => $industryId,
                    'is_published'   => 1,
                    'updated_at'     => now(),
                    'created_at'     => DB::table('events')->where('slug', $slug)->value('created_at') ?? now(),
                ]
            );
        }

        $this->command?->info('Design events seeded: ' . count($rows));
    }
}
