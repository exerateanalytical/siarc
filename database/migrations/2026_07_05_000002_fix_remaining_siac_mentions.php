<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The 2026_07_04_000003 SIARC rebrand migration missed two partner rows
// (MINEPIA, OIDAC) whose descriptions still say "SIAC" — the brand is
// SIARC (Salon International de l'Artisanat du Cameroun); "SIAC" must never
// appear in user-visible text.
return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            'MINEPIA' => [
                'description_fr' => "Ministère de l'Élevage, des Pêches et des Industries Animales — patron officiel du SIARC.",
                'description_en' => 'Ministry of Livestock, Fisheries and Animal Industries — official patron of SIARC.',
            ],
            'OIDAC' => [
                'description_fr' => "Organisation Interprofessionnelle pour le Développement de l'Aquaculture au Cameroun — organisateur du SIARC.",
                'description_en' => 'Interprofessional Organization for Aquaculture Development in Cameroon — SIARC organizer.',
            ],
        ];

        foreach ($replacements as $name => $vals) {
            DB::table('partners')->where('name_fr', $name)->update($vals);
        }
    }

    public function down(): void
    {
        // Not reversible to the incorrect "SIAC" text on purpose.
    }
};
