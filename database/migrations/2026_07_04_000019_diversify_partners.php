<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The admin Partners page design (Partners.png) shows a rich, diverse partner
// directory (institutional / international / finance / private, several
// countries, active vs. pending). The seeded data was 11 Cameroon-only
// institutional partners — not enough breadth to drive that page for real.
// Add a `partner_type` + `status` classification, correct a few countries
// that were defaulted to Cameroun by mistake, and seed additional real-world
// partner categories so the KPI cards / breakdowns are computed from actual
// rows, never hardcoded.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (! Schema::hasColumn('partners', 'partner_type')) {
                $table->string('partner_type')->default('Institutionnel')->after('tier');
            }
            if (! Schema::hasColumn('partners', 'status')) {
                $table->string('status')->default('active')->after('partner_type');
            }
        });

        // Correct country/type for the existing international bodies that
        // were defaulted to Cameroun by the earlier enrichment migration.
        $corrections = [
            'UNESCO' => ['country' => 'France', 'partner_type' => 'International'],
            'ITC' => ['country' => 'Suisse', 'partner_type' => 'International'],
            'OAPI' => ['country' => 'Cameroun', 'partner_type' => 'International'],
            'Banque Africaine de Développement' => ['country' => 'Côte d\'Ivoire', 'partner_type' => 'Finance'],
            'AFD' => ['country' => 'France', 'partner_type' => 'Finance'],
            'Union Européenne' => ['country' => 'Belgique', 'partner_type' => 'International'],
        ];
        foreach ($corrections as $name => $vals) {
            DB::table('partners')->where('name_fr', $name)->update($vals + ['status' => 'active']);
        }
        DB::table('partners')->whereNotIn('name_fr', array_keys($corrections))
            ->update(['partner_type' => 'Institutionnel', 'status' => 'active']);

        $maxSort = (int) DB::table('partners')->max('sort_order');
        $now = now();

        // [name_fr, type, sector_fr, country, status, level, since]
        $new = [
            ['Union Africaine', 'International', 'Coopération Régionale', 'Éthiopie', 'active', 'Premium', 2023],
            ['Organisation Internationale de la Francophonie', 'International', 'Coopération Culturelle', 'France', 'pending', 'Standard', 2025],
            ['CEEAC', 'International', 'Intégration Régionale', 'Congo', 'active', 'Standard', 2024],
            ['GIZ Cameroun', 'International', 'Développement', 'Allemagne', 'pending', 'Standard', 2025],
            ['USAID Cameroun', 'International', 'Développement', 'États-Unis', 'pending', 'Standard', 2025],
            ['BEAC', 'Finance', 'Bancaire', 'CEMAC', 'active', 'Premium', 2023],
            ['Afriland First Bank', 'Finance', 'Bancaire', 'Cameroun', 'active', 'Standard', 2024],
            ['Ecobank Cameroun', 'Finance', 'Bancaire', 'Cameroun', 'pending', 'Standard', 2025],
            ['Société Générale Cameroun', 'Finance', 'Bancaire', 'Cameroun', 'active', 'Standard', 2024],
            ['Orange Cameroun', 'Privé', 'Télécommunications', 'Cameroun', 'active', 'Premium', 2023],
            ['MTN Cameroun', 'Privé', 'Télécommunications', 'Cameroun', 'active', 'Standard', 2024],
            ['Cameroon Airlines Corporation', 'Privé', 'Transport', 'Cameroun', 'pending', 'Standard', 2025],
            ['Bolloré Transport & Logistics', 'Privé', 'Logistique', 'Cameroun', 'pending', 'Standard', 2025],
            ['Nestlé Cameroun', 'Privé', 'Agroalimentaire', 'Cameroun', 'active', 'Standard', 2024],
            ['Guinness Cameroun', 'Privé', 'Agroalimentaire', 'Cameroun', 'pending', 'Standard', 2025],
            ['TotalEnergies Cameroun', 'Privé', 'Énergie', 'Cameroun', 'active', 'Standard', 2024],
            ['Fondation Paul Biya', 'Institutionnel', 'Social & Humanitaire', 'Cameroun', 'active', 'Standard', 2023],
            ['MINPMEESA', 'Institutionnel', 'Développement', 'Cameroun', 'active', 'Standard', 2024],
            ['ANOR', 'Institutionnel', 'Normalisation & Qualité', 'Cameroun', 'active', 'Standard', 2024],
            ['MINTOUL', 'Institutionnel', 'Tourisme & Loisirs', 'Cameroun', 'active', 'Standard', 2024],
            ['Fondation Sawa pour l\'Éducation', 'Privé', 'Éducation', 'Cameroun', 'pending', 'Standard', 2025],
        ];

        foreach ($new as $i => [$name, $type, $sector, $country, $status, $level, $since]) {
            $ref = 'PART-' . $since . '-' . str_pad((string) ($maxSort + $i + 100), 4, '0', STR_PAD_LEFT);
            $slugKey = strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
            DB::table('partners')->insert([
                'name_fr' => $name, 'name_en' => $name,
                'logo' => null, 'website' => 'https://www.' . $slugKey . '.example',
                'tier' => 'partner', 'partner_type' => $type, 'status' => $status,
                'description_fr' => $name . ' — partenaire ' . strtolower($type) . ' de la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.',
                'description_en' => $name . ' — ' . strtolower($type) . ' partner of the National Virtual Gallery of Cameroonian Crafts.',
                'sort_order' => $maxSort + $i + 10, 'is_active' => $status === 'active',
                'contact_email' => $slugKey . '@partenaire.cm',
                'contact_phone' => '+237 6' . str_pad((string) (70 + $i % 20), 2, '0', STR_PAD_LEFT) . ' ' . str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT) . ' ' . str_pad((string) (20 + $i), 2, '0', STR_PAD_LEFT) . ' ' . str_pad((string) (30 + $i), 2, '0', STR_PAD_LEFT),
                'address' => $country === 'Cameroun' ? 'Douala, Cameroun' : $country,
                'sector_fr' => $sector, 'country' => $country, 'partner_ref' => $ref,
                'responsible_name' => 'Coordination Partenariat', 'responsible_title' => 'Responsable des Partenariats',
                'responsible_email' => 'contact@' . $slugKey . '.example',
                'partnership_type' => $type, 'partnership_level' => $level,
                'start_date' => \Carbon\Carbon::create($since, 5, 12), 'end_date' => \Carbon\Carbon::create($since + 3, 5, 11),
                'auto_renew' => true, 'legal_verified' => $status === 'active',
                'reliability' => $status === 'active' ? 4.5 : 3.8, 'since_year' => $since,
                'created_at' => $now->copy()->subMonths(21 - $i), 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['partner_type', 'status']);
        });
    }
};
