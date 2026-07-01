<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase2SeederAll extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Pick real companies from DB ────────────────────────────────────────
        $companies = DB::table('companies')->whereNull('deleted_at')->limit(5)->get();
        if ($companies->isEmpty()) { $this->command->warn('No companies found — seed companies first.'); return; }
        $co = $companies->values();

        // ── Phase 2: Tenders ──────────────────────────────────────────────────
        if (DB::table('tenders')->count() === 0) {
            $tenders = [
                [Str::uuid(), $co[0]->id, 'Supply of Pesticides and Agrochemicals', 'goods', 'open', 500000000, '2026-08-31', 'Douala, Littoral'],
                [Str::uuid(), $co[1]->id, 'Coffee Processing Equipment Maintenance', 'works', 'open', 120000000, '2026-09-15', 'Yaoundé, Centre'],
                [Str::uuid(), $co[2]->id, 'ICT Infrastructure Upgrade — Nationwide', 'ict', 'open', 2000000000, '2026-10-01', 'National'],
                [Str::uuid(), $co[3]->id, 'Transport & Logistics Services for 2027', 'services', 'open', 800000000, '2026-11-30', 'Cameroon-wide'],
                [Str::uuid(), $co[4]->id, 'Construction of Cold Storage Facility', 'construction', 'open', 350000000, '2026-09-30', 'Kribi, South'],
            ];
            foreach ($tenders as $i => [$id, $co_id, $title, $cat, $status, $budget, $deadline, $loc]) {
                DB::table('tenders')->insert([
                    'id' => $id,
                    'company_id' => $co_id,
                    'posted_by' => DB::table('users')->value('id') ?? Str::uuid(),
                    'title' => $title,
                    'description' => 'This tender invites qualified suppliers and service providers to submit bids. Interested bidders must meet prequalification criteria and submit all required documentation by the deadline. The procuring entity reserves the right to accept or reject any bid.',
                    'category' => $cat,
                    'type' => $i % 2 === 0 ? 'open' : 'rfp',
                    'status' => $status,
                    'budget_estimate' => $budget,
                    'currency' => 'XAF',
                    'deadline' => $deadline,
                    'location' => $loc,
                    'eligibility' => 'Must be a registered company with at least 3 years experience in the relevant sector. Must provide financial statements for last 2 years.',
                    'contact_email' => 'procurement@example.cm',
                    'view_count' => rand(20, 200),
                    'bid_count' => rand(1, 8),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $this->command->info('5 sample tenders seeded.');
        }

        // ── Phase 3: Investment Seeks ──────────────────────────────────────────
        if (DB::table('invest_seeks')->count() === 0) {
            $seeks = [
                [$co[0]->id, 'Seeking Equity Partner for Cocoa Processing Expansion', 'equity', 'agriculture', 500000000, 25.0],
                [$co[1]->id, 'Series A — Digital Agri-Finance Platform', 'series_a', 'ict', 1200000000, 20.0],
                [$co[2]->id, 'Development Finance for Energy Infrastructure', 'development_finance', 'energy', 5000000000, null],
                [$co[3]->id, 'Angel Round — HealthTech Startup', 'angel', 'health', 150000000, 30.0],
                [$co[4]->id, 'Government Grant — Agri Mechanisation', 'government_fund', 'agriculture', 200000000, null],
            ];
            foreach ($seeks as [$co_id, $title, $type, $sector, $amount, $equity]) {
                DB::table('invest_seeks')->insert([
                    'id' => Str::uuid(),
                    'company_id' => $co_id,
                    'posted_by' => DB::table('users')->value('id') ?? Str::uuid(),
                    'title' => $title,
                    'description' => 'We are seeking investment to accelerate our growth strategy. Our company has demonstrated strong fundamentals with proven market traction and a clear path to profitability. This investment will fund expansion into new markets, technology upgrades, and team growth.',
                    'type' => $type,
                    'sector' => $sector,
                    'amount_sought' => $amount,
                    'currency' => 'XAF',
                    'equity_offered' => $equity,
                    'use_of_funds' => 'Equipment 40%, Working capital 30%, Market expansion 20%, Operations 10%',
                    'traction' => 'Revenue grew 45% last year. 200+ active clients. Profitable for 2 consecutive years.',
                    'status' => 'open',
                    'view_count' => rand(15, 150),
                    'interest_count' => rand(0, 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('5 investment seeks seeded.');
        }

        // ── Phase 5: Federations ──────────────────────────────────────────────
        if (DB::table('federations')->count() === 0) {
            $feds = [
                ['cocoa-value-chain-cameroon', 'Cameroon Cocoa Value Chain Federation', 'CCVCF', 'cocoa', 'The federation uniting all actors in the Cameroon cocoa value chain — from farmers to exporters — for joint procurement, shared logistics, quality standards, and international market access.', true],
                ['timber-forest-ecosystem', 'Timber & Forest Products Ecosystem', 'TFPE', 'timber', 'A governed business network for Cameroon\'s timber and forestry sector, covering logging companies, sawmills, furniture manufacturers, exporters, certification bodies, and conservation partners.', true],
                ['digital-cameroon-ict-hub', 'Digital Cameroon ICT Federation', 'DCIF', 'ict', 'The ICT sector federation connecting tech startups, telecom operators, software companies, hardware distributors, and digital service providers across Cameroon.', true],
                ['palm-oil-network', 'Palm Oil Industry Network', 'POIN', 'palm_oil', 'Connecting palm oil producers, refiners, packagers, distributors, and biodiesel manufacturers into a single supply-chain ecosystem.', false],
                ['cameroon-agri-food-federation', 'Cameroon Agri-Food Value Federation', 'CAFED', 'agri_food', 'Multi-sector federation spanning all food value chains in Cameroon from primary agriculture to consumer food products, retail, and export.', false],
            ];
            foreach ($feds as [$slug, $name, $acronym, $sector, $desc, $featured]) {
                DB::table('federations')->insert([
                    'slug' => $slug,
                    'name' => $name,
                    'acronym' => $acronym,
                    'description' => $desc,
                    'sector' => $sector,
                    'status' => 'active',
                    'is_featured' => $featured,
                    'is_public' => true,
                    'member_count' => rand(8, 45),
                    'view_count' => rand(50, 400),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // Seed some federation members
            $fedIds = DB::table('federations')->pluck('id');
            foreach ($fedIds as $fedId) {
                foreach ($co->take(3) as $c) {
                    DB::table('federation_members')->insertOrIgnore([
                        'federation_id' => $fedId,
                        'company_id' => $c->id,
                        'role' => 'member',
                        'status' => 'active',
                        'joined_at' => now()->subDays(rand(10, 120)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            // Seed some federation posts
            $types = ['announcement','discussion','document'];
            $titles = ['Q3 Sector Update','Export Opportunity Alert','New Quality Standards Released','Joint Procurement Initiative','Upcoming Trade Fair 2026'];
            foreach ($fedIds->take(3) as $fedId) {
                foreach (range(1,3) as $i) {
                    DB::table('federation_posts')->insert([
                        'federation_id' => $fedId,
                        'user_id' => DB::table('users')->value('id') ?? Str::uuid(),
                        'company_id' => $co[0]->id,
                        'title' => $titles[array_rand($titles)],
                        'body' => 'This post contains important information for all federation members. Please review and respond accordingly. Joint action will benefit the entire ecosystem.',
                        'type' => $types[array_rand($types)],
                        'is_pinned' => $i === 1,
                        'view_count' => rand(10, 80),
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now(),
                    ]);
                }
            }
            $this->command->info('5 federations + members + posts seeded.');
        }

        // ── Phase 6: ESG Reports ──────────────────────────────────────────────
        if (DB::table('esg_reports')->count() === 0) {
            foreach ($co->take(3) as $c) {
                $env = rand(45, 85);
                $soc = rand(50, 90);
                $gov = rand(55, 95);
                DB::table('esg_reports')->insert([
                    'id' => Str::uuid(),
                    'company_id' => $c->id,
                    'submitted_by' => DB::table('users')->value('id') ?? Str::uuid(),
                    'year' => 2025,
                    'status' => 'published',
                    'co2_tonnes' => rand(50, 5000) / 10,
                    'energy_kwh' => rand(100000, 5000000),
                    'renewable_energy_pct' => rand(5, 60),
                    'water_m3' => rand(1000, 50000),
                    'waste_tonnes' => rand(10, 500),
                    'recycled_pct' => rand(10, 70),
                    'environmental_initiatives' => 'Solar panel installation on rooftop. Tree planting campaign — 500 trees planted in 2025. Switched to biodegradable packaging.',
                    'total_employees' => rand(20, 500),
                    'female_employees' => rand(5, 200),
                    'local_employees_pct' => rand(60, 95),
                    'training_hours_per_employee' => rand(8, 40),
                    'safety_incidents' => rand(0, 5),
                    'has_health_insurance' => true,
                    'community_initiatives' => 'Scholarship program for 15 students. Community health drive. Local supplier preference policy.',
                    'has_ethics_policy' => true,
                    'has_whistleblower_policy' => (bool) rand(0,1),
                    'has_board_diversity' => (bool) rand(0,1),
                    'anti_corruption_training' => true,
                    'env_score' => $env,
                    'social_score' => $soc,
                    'governance_score' => $gov,
                    'overall_esg_score' => (int)(($env + $soc + $gov) / 3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('3 ESG reports seeded.');
        }

        // ── Phase 7: Export Resources ─────────────────────────────────────────
        if (DB::table('export_resources')->count() === 0) {
            $resources = [
                ['Cameroon Export Procedures Guide', 'cameroon-export-procedures', 'customs', 'Complete step-by-step guide to exporting from Cameroon covering CNCC registration, customs procedures, BIVAC pre-shipment inspection, and port documentation.'],
                ['Certificate of Origin — How to Obtain', 'certificate-of-origin-cameroon', 'certification', 'The certificate of origin is required for most exports. Learn how to obtain it from the Chamber of Commerce (CCIMA), costs, processing times, and what documents you need.'],
                ['CEMAC Trade Agreements & Tariff Preferences', 'cemac-trade-agreements', 'trade_agreements', 'Overview of trade agreements available to Cameroonian exporters: CEMAC free trade area, EU-Cameroon Economic Partnership Agreement (EPA), African Continental Free Trade Area (AfCFTA), and preferential tariffs.'],
                ['HS Code Finder for Cameroon Exports', 'hs-code-finder-cameroon', 'hs_codes', 'Harmonised System (HS) codes determine import duties in destination countries. This guide covers how to find the correct HS code for your product, common codes for Cameroon exports (cocoa, coffee, timber, rubber), and how to use the World Customs Organization database.'],
                ['Cocoa Export Requirements', 'cocoa-export-requirements', 'certification', 'Everything you need to know about exporting Cocoa from Cameroon: ONCC certification, grading standards, phytosanitary certificates, quality inspection procedures, and compliance with European Food Safety Regulations.'],
                ['EU Market Entry for Cameroonian Products', 'eu-market-entry-cameroon', 'markets', 'Comprehensive guide to exporting to the European Union under the EPA agreement. Covers tariff preferences, product standards, labelling requirements, and key contacts at the Cameroon Embassy in Brussels.'],
                ['Export Financing Options in Cameroon', 'export-financing-cameroon', 'financing', 'Available financing instruments for Cameroonian exporters: BGFI Export Credit, SCB pre-export financing, Afriland First Bank trade finance, BEAC refinancing windows, and development finance institutions (IFC, BDEAC, AfDB).'],
                ['Shipping & Freight Guide — Port of Douala', 'shipping-port-of-douala', 'shipping', 'Complete guide to using the Port of Douala: container booking, shipping lines serving Cameroon, transit times to major markets, freight rate benchmarks, and port agent contacts.'],
            ];
            foreach ($resources as [$title, $slug, $cat, $body]) {
                DB::table('export_resources')->insert([
                    'title' => $title,
                    'slug' => $slug,
                    'body' => $body,
                    'category' => $cat,
                    'is_published' => true,
                    'is_featured' => in_array($slug, ['cameroon-export-procedures','cocoa-export-requirements','eu-market-entry-cameroon']),
                    'view_count' => rand(30, 300),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('8 export resources seeded.');
        }
    }
}
