<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SampleOfferingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('share_offerings')->truncate();
        DB::table('company_documents')->truncate();
        DB::table('company_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $companies = DB::table('companies')->get()->keyBy('slug');

        $offerings = [
            [
                'company_slug'    => 'mtn-cameroun-sa',
                'title_en'        => 'MTN Cameroun Series B Bond Issuance',
                'title_fr'        => 'Emission d\'obligations MTN Cameroun Serie B',
                'summary_en'      => 'MTN Cameroun is raising 15 billion XAF through a bond issuance to finance 5G network rollout across 8 regions and expand mobile money infrastructure.',
                'summary_fr'      => 'MTN Cameroun leve 15 milliards FCFA via une emission obligataire pour financer le deploiement de la 5G dans 8 regions et etendre l\'infrastructure Mobile Money.',
                'instrument_type' => 'bonds',
                'status'          => 'open',
                'target_amount'   => 15000000000,
                'minimum_amount'  => 10000000000,
                'maximum_amount'  => 18000000000,
                'amount_raised'   => 9750000000,
                'share_price'     => 100000,
                'total_shares'    => 150000,
                'shares_sold'     => 97500,
                'equity_offered'  => null,
                'min_investment'  => 500000,
                'max_investment'  => 500000000,
                'open_date'       => '2026-05-01',
                'close_date'      => '2026-08-31',
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
            [
                'company_slug'    => 'afriland-first-bank',
                'title_en'        => 'Afriland First Bank Capital Increase',
                'title_fr'        => 'Augmentation de capital Afriland First Bank',
                'summary_en'      => 'Afriland First Bank opens its capital to strategic investors to strengthen its Tier 1 ratio and expand its SME lending portfolio across Central Africa.',
                'summary_fr'      => 'Afriland First Bank ouvre son capital a des investisseurs strategiques pour renforcer son ratio Tier 1 et elargir son portefeuille de prets PME en Afrique centrale.',
                'instrument_type' => 'ordinary_shares',
                'status'          => 'open',
                'target_amount'   => 5000000000,
                'minimum_amount'  => 3000000000,
                'maximum_amount'  => 6000000000,
                'amount_raised'   => 2100000000,
                'share_price'     => 50000,
                'total_shares'    => 100000,
                'shares_sold'     => 42000,
                'equity_offered'  => 15.5,
                'min_investment'  => 250000,
                'max_investment'  => 200000000,
                'open_date'       => '2026-04-15',
                'close_date'      => '2026-07-15',
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
            [
                'company_slug'    => 'eneo-cameroon-sa',
                'title_en'        => 'ENEO Cameroon Green Energy Infrastructure Bond',
                'title_fr'        => 'Obligation Infrastructure Energie Verte ENEO Cameroun',
                'summary_en'      => 'ENEO raises 25 billion XAF to finance the construction of 120 MW of solar capacity and upgrade transmission lines in the Northern Interconnected Grid.',
                'summary_fr'      => 'ENEO leve 25 milliards FCFA pour financer la construction de 120 MW de capacite solaire et moderniser les lignes de transport dans le reseau interconnecte Nord.',
                'instrument_type' => 'bonds',
                'status'          => 'cmf_approved',
                'target_amount'   => 25000000000,
                'minimum_amount'  => 20000000000,
                'maximum_amount'  => 30000000000,
                'amount_raised'   => 0,
                'share_price'     => 100000,
                'total_shares'    => 250000,
                'shares_sold'     => 0,
                'equity_offered'  => null,
                'min_investment'  => 1000000,
                'max_investment'  => 1000000000,
                'open_date'       => '2026-07-01',
                'close_date'      => '2026-12-31',
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
            [
                'company_slug'    => 'societe-camerounaise-de-palmeraies',
                'title_en'        => 'SOCAPALM Expansion Share Offering',
                'title_fr'        => 'Offre d\'actions SOCAPALM pour expansion',
                'summary_en'      => 'SOCAPALM invites investors to participate in a 4-billion XAF equity raise to develop 8,000 new hectares of certified sustainable palm plantations in the Littoral region.',
                'summary_fr'      => 'SOCAPALM invite les investisseurs a participer a une levee de fonds de 4 milliards FCFA pour developper 8 000 nouveaux hectares de plantations de palmiers certifiees durables dans la region du Littoral.',
                'instrument_type' => 'ordinary_shares',
                'status'          => 'closed',
                'target_amount'   => 4000000000,
                'minimum_amount'  => 3000000000,
                'maximum_amount'  => 5000000000,
                'amount_raised'   => 4250000000,
                'share_price'     => 25000,
                'total_shares'    => 160000,
                'shares_sold'     => 160000,
                'equity_offered'  => 12.0,
                'min_investment'  => 125000,
                'max_investment'  => 100000000,
                'open_date'       => '2025-10-01',
                'close_date'      => '2026-01-31',
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
            [
                'company_slug'    => 'cameroon-housing-bank',
                'title_en'        => 'CHB Mortgage Bond Series 2026',
                'title_fr'        => 'Obligation Hypothecaire CHB Serie 2026',
                'summary_en'      => 'Cameroon Housing Bank issues mortgage-backed bonds to fund affordable housing loan programs targeting middle-income Cameroonian households.',
                'summary_fr'      => 'La Banque de l\'Habitat du Cameroun emet des obligations hypothecaires pour financer des programmes de prets immobiliers destines aux menages camerounais a revenus moyens.',
                'instrument_type' => 'bonds',
                'status'          => 'open',
                'target_amount'   => 3000000000,
                'minimum_amount'  => 2000000000,
                'maximum_amount'  => 3500000000,
                'amount_raised'   => 875000000,
                'share_price'     => 50000,
                'total_shares'    => 60000,
                'shares_sold'     => 17500,
                'equity_offered'  => null,
                'min_investment'  => 250000,
                'max_investment'  => 50000000,
                'open_date'       => '2026-06-01',
                'close_date'      => '2026-09-30',
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
            [
                'company_slug'    => 'dibamba-power-development-company',
                'title_en'        => 'DPDC Capacity Expansion Private Placement',
                'title_fr'        => 'Placement prive DPDC pour extension de capacite',
                'summary_en'      => 'DPDC seeks qualified institutional investors for a private placement to fund a 44 MW expansion of the Dibamba thermal plant, doubling current generation capacity.',
                'summary_fr'      => 'DPDC recherche des investisseurs institutionnels qualifies pour un placement prive destinee a financer une extension de 44 MW de la centrale thermique de Dibamba.',
                'instrument_type' => 'ordinary_shares',
                'status'          => 'pending_cmf',
                'target_amount'   => 12000000000,
                'minimum_amount'  => 10000000000,
                'maximum_amount'  => 14000000000,
                'amount_raised'   => 0,
                'share_price'     => 200000,
                'total_shares'    => 60000,
                'shares_sold'     => 0,
                'equity_offered'  => 22.0,
                'min_investment'  => 5000000,
                'max_investment'  => 2000000000,
                'open_date'       => null,
                'close_date'      => null,
                'currency'        => 'XAF',
                'platform_fee_pct'=> 2.5,
            ],
        ];

        $members = [
            'mtn-cameroun-sa' => [
                ['first_name' => 'Karl', 'last_name' => 'Toriola', 'role' => 'admin', 'title' => 'Chief Executive Officer'],
                ['first_name' => 'Ama', 'last_name' => 'Ofori', 'role' => 'admin', 'title' => 'Chief Financial Officer'],
                ['first_name' => 'Jean-Paul', 'last_name' => 'Abessolo', 'role' => 'member', 'title' => 'Board Director'],
            ],
            'societe-generale-cameroun' => [
                ['first_name' => 'Philippe', 'last_name' => 'Duhamel', 'role' => 'owner', 'title' => 'Directeur General'],
                ['first_name' => 'Marie', 'last_name' => 'Ntonga', 'role' => 'admin', 'title' => 'Directrice Financiere'],
            ],
            'brasseries-du-cameroun' => [
                ['first_name' => 'Serge', 'last_name' => 'Mankou', 'role' => 'owner', 'title' => 'Directeur General'],
                ['first_name' => 'Helene', 'last_name' => 'Ateba', 'role' => 'member', 'title' => 'Directrice des Operations'],
                ['first_name' => 'Thomas', 'last_name' => 'Mbida', 'role' => 'member', 'title' => 'Directeur Commercial'],
            ],
            'afriland-first-bank' => [
                ['first_name' => 'Paul', 'last_name' => 'Fokam', 'role' => 'member', 'title' => 'Founder & Chairman'],
                ['first_name' => 'Lionel', 'last_name' => 'Zinsou', 'role' => 'member', 'title' => 'Board Director'],
                ['first_name' => 'Celestine', 'last_name' => 'Ketcha', 'role' => 'admin', 'title' => 'Chief Financial Officer'],
            ],
            'eneo-cameroon-sa' => [
                ['first_name' => 'JoÃ«l', 'last_name' => 'Nana Kontchou', 'role' => 'owner', 'title' => 'Managing Director'],
                ['first_name' => 'Rodrigue', 'last_name' => 'Wandji', 'role' => 'admin', 'title' => 'Chief Financial Officer'],
            ],
            'societe-camerounaise-de-palmeraies' => [
                ['first_name' => 'Pierre', 'last_name' => 'Moukoko', 'role' => 'owner', 'title' => 'Directeur General'],
                ['first_name' => 'Agnes', 'last_name' => 'Fouda', 'role' => 'member', 'title' => 'Directrice Qualite'],
            ],
        ];

        $documents = [
            'mtn-cameroun-sa' => [
                ['type' => 'annual_report', 'title' => 'Annual Report 2025', 'visibility' => 'public'],
                ['type' => 'rccm', 'title' => 'RCCM Certificate', 'visibility' => 'public'],
                ['type' => 'ifu', 'title' => 'Audited Financials 2025', 'visibility' => 'public'],
            ],
            'afriland-first-bank' => [
                ['type' => 'annual_report', 'title' => 'Annual Report 2025', 'visibility' => 'public'],
                ['type' => 'cmf_license', 'title' => 'Banking License', 'visibility' => 'public'],
            ],
            'eneo-cameroon-sa' => [
                ['type' => 'other', 'title' => 'Concession Agreement', 'visibility' => 'public'],
                ['type' => 'annual_report', 'title' => 'Annual Report 2025', 'visibility' => 'public'],
            ],
        ];

        // Insert offerings
        foreach ($offerings as $o) {
            $company = $companies[$o['company_slug']] ?? null;
            if (!$company) continue;

            DB::table('share_offerings')->insert([
                'id'               => Str::uuid()->toString(),
                'company_id'       => $company->id,
                'title_en'         => $o['title_en'],
                'title_fr'         => $o['title_fr'],
                'summary_en'       => $o['summary_en'],
                'summary_fr'       => $o['summary_fr'],
                'instrument_type'  => $o['instrument_type'],
                'status'           => $o['status'],
                'target_amount'    => $o['target_amount'],
                'minimum_amount'   => $o['minimum_amount'],
                'maximum_amount'   => $o['maximum_amount'],
                'amount_raised'    => $o['amount_raised'],
                'share_price'      => $o['share_price'],
                'total_shares'     => $o['total_shares'],
                'shares_sold'      => $o['shares_sold'],
                'equity_offered'   => $o['equity_offered'],
                'min_investment'   => $o['min_investment'],
                'max_investment'   => $o['max_investment'],
                'open_date'        => $o['open_date'],
                'close_date'       => $o['close_date'],
                'currency'         => $o['currency'],
                'platform_fee_pct' => $o['platform_fee_pct'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // Insert members — one per company using available users, skip duplicates
        $users = DB::table('users')->pluck('id')->toArray();
        foreach ($members as $slug => $roles) {
            $company = $companies[$slug] ?? null;
            if (!$company || empty($users)) continue;
            $userIndex = 0;
            foreach ($roles as $m) {
                DB::table('company_users')->insertOrIgnore([
                    'company_id' => $company->id,
                    'user_id'    => $users[$userIndex % count($users)],
                    'role'       => $m['role'],
                    'title'      => $m['title'],
                    'is_active'  => true,
                    'joined_at'  => now()->subYears(rand(1, 5)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $userIndex++;
            }
        }

        // Insert documents
        foreach ($documents as $slug => $docs) {
            $company = $companies[$slug] ?? null;
            if (!$company) continue;
            foreach ($docs as $d) {
                DB::table('company_documents')->insert([
                    'company_id'  => $company->id,
                    'type'        => $d['type'],
                    'title'       => $d['title'],
                    'file_path'   => 'documents/sample.pdf',
                    'file_hash'   => md5($d['title']),
                    'file_size'   => rand(100000, 5000000),
                    'mime_type'   => 'application/pdf',
                    'visibility'  => $d['visibility'],
                    'is_verified' => true,
                    'verified_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        $this->command->info('6 offerings, ' . DB::table('company_users')->count() . ' members, ' . DB::table('company_documents')->count() . ' documents seeded.');
    }
}

