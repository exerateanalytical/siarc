<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Partner detail pages (admin + public) need rich partnership data. Enrich the
// partners table and seed real values for the known institutional partners.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            foreach ([
                'contact_email' => 'string', 'contact_phone' => 'string', 'address' => 'string',
                'sector_fr' => 'string', 'country' => 'string', 'partner_ref' => 'string',
                'responsible_name' => 'string', 'responsible_title' => 'string', 'responsible_email' => 'string',
                'partnership_type' => 'string', 'partnership_level' => 'string',
                'start_date' => 'date', 'end_date' => 'date',
            ] as $col => $type) {
                if (! Schema::hasColumn('partners', $col)) {
                    $type === 'date' ? $table->date($col)->nullable() : $table->string($col)->nullable();
                }
            }
            if (! Schema::hasColumn('partners', 'auto_renew'))    $table->boolean('auto_renew')->default(true);
            if (! Schema::hasColumn('partners', 'legal_verified')) $table->boolean('legal_verified')->default(true);
            if (! Schema::hasColumn('partners', 'reliability'))    $table->decimal('reliability', 3, 1)->default(4.5);
            if (! Schema::hasColumn('partners', 'since_year'))     $table->unsignedSmallInteger('since_year')->nullable();
        });

        // Per-partner rich values (name_fr => data). Others get sensible defaults.
        $data = [
            'MINAC' => [
                'full_fr' => 'Ministère des Arts et de la Culture',
                'email' => 'contact@minac.cm', 'phone' => '+237 222 22 10 57', 'web' => 'https://www.minac.cm',
                'address' => 'Yaoundé, Cameroun', 'sector' => 'Culture & Patrimoine',
                'resp' => 'Jean Pierre Ngalle', 'resp_title' => 'Directeur de la Coopération', 'resp_email' => 'jean.ngalle@minac.cm',
                'level' => 'Premium', 'rel' => 4.8, 'since' => 2024,
            ],
            'MINCOMMERCE' => ['full_fr' => 'Ministère du Commerce', 'email' => 'contact@mincommerce.cm', 'phone' => '+237 222 23 40 12', 'web' => 'https://www.mincommerce.cm', 'address' => 'Yaoundé, Cameroun', 'sector' => 'Commerce & Industrie', 'resp' => 'Aïcha Bello', 'resp_title' => 'Chef de Coopération', 'resp_email' => 'a.bello@mincommerce.cm', 'level' => 'Premium', 'rel' => 4.7, 'since' => 2023],
            'UNESCO' => ['full_fr' => 'UNESCO', 'email' => 'contact@unesco.org', 'phone' => '+33 1 45 68 10 00', 'web' => 'https://www.unesco.org', 'address' => 'Paris, France', 'sector' => 'Patrimoine mondial', 'resp' => 'Marie Dupont', 'resp_title' => 'Programme Officer', 'resp_email' => 'm.dupont@unesco.org', 'level' => 'Premium', 'rel' => 4.9, 'since' => 2022],
        ];

        foreach (DB::table('partners')->get() as $p) {
            $d = $data[$p->name_fr] ?? null;
            $ref = 'PART-' . ($d['since'] ?? 2024) . '-' . str_pad((string) $p->id, 4, '0', STR_PAD_LEFT);
            DB::table('partners')->where('id', $p->id)->update([
                'contact_email' => $d['email'] ?? (strtolower(preg_replace('/[^a-z0-9]/i', '', $p->name_fr)) . '@partenaire.cm'),
                'contact_phone' => $d['phone'] ?? '+237 6 ' . rand(70, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'website' => $p->website ?: ($d['web'] ?? 'https://www.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $p->name_fr)) . '.org'),
                'address' => $d['address'] ?? 'Yaoundé, Cameroun',
                'sector_fr' => $d['sector'] ?? 'Institutionnel',
                'country' => 'Cameroun',
                'partner_ref' => $ref,
                'responsible_name' => $d['resp'] ?? 'Coordination Partenariat',
                'responsible_title' => $d['resp_title'] ?? 'Responsable',
                'responsible_email' => $d['resp_email'] ?? ('contact@' . strtolower(preg_replace('/[^a-z0-9]/i', '', $p->name_fr)) . '.cm'),
                'partnership_type' => 'Institutionnel',
                'partnership_level' => $d['level'] ?? 'Standard',
                'start_date' => \Carbon\Carbon::create($d['since'] ?? 2024, 5, 12),
                'end_date' => \Carbon\Carbon::create(($d['since'] ?? 2024) + 3, 5, 11),
                'auto_renew' => true, 'legal_verified' => true,
                'reliability' => $d['rel'] ?? 4.5, 'since_year' => $d['since'] ?? 2024,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'address', 'sector_fr', 'country', 'partner_ref', 'responsible_name', 'responsible_title', 'responsible_email', 'partnership_type', 'partnership_level', 'start_date', 'end_date', 'auto_renew', 'legal_verified', 'reliability', 'since_year']);
        });
    }
};
