<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase9SeederAll extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')->whereNull('deleted_at')->limit(10)->get();
        if ($companies->isEmpty()) { $this->command->warn('No companies found.'); return; }
        $c1 = $companies[0]; $c2 = $companies[1] ?? $c1; $c3 = $companies[2] ?? $c1;
        $c4 = $companies[3] ?? $c1; $c5 = $companies[4] ?? $c1;
        $userId = DB::table('users')->value('id') ?? 'system';

        // ── INNOVATION PROJECTS ───────────────────────────────────────────────
        $projects = [
            ['cocoa-traceability-blockchain','Cocoa Traceability Blockchain Platform',
             'Joint R&D project to build a blockchain-based traceability system for Cameroonian cocoa, enabling farm-to-export tracking for EUDR compliance. Seeking tech partners and cocoa cooperatives.',
             'research','cocoa','prototype','seeking_partners',50000000,null,'2026-12-15',
             'ICT partners with blockchain experience, cocoa cooperatives willing to pilot, and an agronomy advisor.','blockchain,cocoa,traceability,EUDR',$c1->id],
            ['agritech-iot-soil-sensors','AgriTech IoT Soil Sensor Network',
             'Developing low-cost IoT soil moisture and nutrient sensors for smallholder farmers. Prototype validated; scaling to 500 farms. Looking for hardware manufacturing and distribution partners.',
             'prototype','agriculture','pilot','in_progress',30000000,null,'2026-10-30',
             'Hardware manufacturers, agri-input distributors, and impact investors.','iot,agritech,sensors,hardware',$c2->id],
            ['national-fintech-hackathon','National FinTech Hackathon 2026',
             'A 72-hour hackathon challenging developers to build financial inclusion solutions for the unbanked. XAF 5,000,000 prize pool. Open to all Cameroonian developers and startups.',
             'hackathon','finance','idea','open',null,5000000,'2026-09-20',
             'Developers, designers, and fintech startups. Sponsors welcome.','fintech,hackathon,inclusion',$c3->id],
            ['solar-cold-chain-challenge','Solar Cold-Chain Innovation Challenge',
             'Innovation challenge seeking solar-powered cold storage solutions for rural food preservation. Reduce post-harvest losses. Winning solution receives funding and a pilot deployment.',
             'challenge','energy','prototype','open',null,8000000,'2026-11-10',
             'Clean-energy engineers, refrigeration specialists, and agri-food businesses.','solar,coldchain,postharvest',$c4->id],
            ['telemedicine-rural-platform','Rural Telemedicine Platform',
             'Open innovation project to develop a telemedicine platform connecting rural patients to urban specialists via low-bandwidth video and SMS. Seeking health and ICT collaborators.',
             'open_innovation','health','pilot','seeking_partners',40000000,null,'2026-12-01',
             'Healthcare providers, ICT developers, and mobile network partners.','telemedicine,health,rural',$c5->id],
            ['timber-waste-biocomposite','Timber Waste to Biocomposite Patent',
             'Patent-pending process converting timber processing waste into construction biocomposite panels. Seeking manufacturing partners and licensees to commercialise.',
             'patent','timber','market_ready','seeking_partners',60000000,null,'2027-01-31',
             'Construction material manufacturers and licensing partners.','patent,biocomposite,timber,circular',$c1->id],
        ];
        foreach ($projects as $p) {
            if (!DB::table('innovation_projects')->where('slug',$p[0])->exists()) {
                DB::table('innovation_projects')->insert([
                    'id'=>(string)Str::uuid(),'slug'=>$p[0],'title'=>$p[1],'description'=>$p[2],
                    'type'=>$p[3],'sector'=>$p[4],'stage'=>$p[5],'status'=>$p[6],
                    'budget'=>$p[7],'prize_amount'=>$p[8],'deadline'=>$p[9],'looking_for'=>$p[10],'tags'=>$p[11],
                    'company_id'=>$p[12],'user_id'=>$userId,
                    'participant_count'=>rand(2,15),'view_count'=>rand(40,400),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── LOGISTICS LISTINGS ────────────────────────────────────────────────
        $listings = [
            ['load','Cocoa Beans — Bafoussam to Douala Port','perishable','truck_medium','Bafoussam','Douala',12000,40,450000,'2026-07-10',
             '12 tonnes of dried cocoa beans needing transport to Douala Port for export. Covered truck required.',$c1->id],
            ['load','Construction Materials — Douala to Yaounde','general','truck_large','Douala','Yaoundé',24000,60,650000,'2026-07-05',
             'Cement, steel rebar, and fittings for a Yaounde construction site. Flatbed or large covered truck.',$c2->id],
            ['capacity','Available: Refrigerated Truck Douala-North Route','refrigerated','refrigerated_truck','Douala','Garoua',null,null,800000,'2026-07-08',
             'Refrigerated 15-tonne truck running Douala to Garoua weekly. Space available for perishables. Competitive rates.',$c3->id],
            ['load','Palm Oil Drums — Limbe to Douala','liquid','tanker','Limbe','Douala',8000,10,280000,'2026-07-12',
             'Bulk palm oil in sealed drums, Limbe to Douala. Tanker or container truck preferred.',$c4->id],
            ['capacity','Available: Container Truck Douala Port Runs','containers','container_truck','Douala','Yaoundé',null,null,500000,'2026-07-06',
             'Daily container haulage between Douala Port and Yaounde. Return-trip discounts available.',$c5->id],
            ['load','Fresh Produce — Foumbot to Yaounde Market','refrigerated','refrigerated_truck','Foumbot','Yaoundé',6000,25,320000,'2026-07-09',
             'Fresh tomatoes, peppers, and vegetables from Foumbot farms to Yaounde markets. Reefer required, urgent.',$c1->id],
            ['capacity','Available: Flatbed Trailer West Region','oversized','flatbed','Bafoussam','Douala',null,null,700000,'2026-07-15',
             'Heavy-duty flatbed trailer available for oversized cargo, machinery, and equipment. West to Littoral.',$c2->id],
        ];
        foreach ($listings as $l) {
            DB::table('logistics_listings')->insert([
                'id'=>(string)Str::uuid(),'type'=>$l[0],'title'=>$l[1],'cargo_type'=>$l[2],'vehicle_type'=>$l[3],
                'origin_city'=>$l[4],'destination_city'=>$l[5],'weight_kg'=>$l[6],'volume_m3'=>$l[7],
                'price'=>$l[8],'available_date'=>$l[9],'description'=>$l[10],'company_id'=>$l[11],'user_id'=>$userId,
                'contact_phone'=>'+237 6'.rand(70000000,99999999),'status'=>'open',
                'bid_count'=>rand(0,6),'view_count'=>rand(20,250),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }

        // ── DIGITAL BUSINESS CARDS ────────────────────────────────────────────
        $cards = [
            ['jean-paul-mbarga','Jean-Paul Mbarga','Chief Executive Officer','Procurement & Trade Director',
             'Connecting Cameroonian suppliers to global markets','jp.mbarga@example.cm','+237 677 100 200','+237 677 100 200',
             'https://example.cm','Bonanjo, Douala','Douala','#007a33',$c1->id],
            ['amina-fombang','Amina Fombang','Founder & Managing Director','AgriTech Innovator',
             'Building the future of smart farming in Cameroon','amina@example.cm','+237 699 300 400','+237 699 300 400',
             'https://example.cm','Bastos, Yaounde','Yaoundé','#16a34a',$c2->id],
            ['samuel-eto-nkeng','Samuel Eto Nkeng','Head of Logistics','Supply Chain Specialist',
             'Moving Cameroon forward, one shipment at a time','samuel@example.cm','+237 655 500 600','+237 655 500 600',
             'https://example.cm','Akwa, Douala','Douala','#0284c7',$c3->id],
            ['grace-tabi','Grace Tabi','Investment Director','Finance & Capital Markets',
             'Funding the next generation of Cameroonian enterprise','grace.tabi@example.cm','+237 681 700 800','+237 681 700 800',
             'https://example.cm','Hippodrome, Yaounde','Yaoundé','#6d28d9',$c4->id],
        ];
        foreach ($cards as $cd) {
            if (!DB::table('digital_cards')->where('slug',$cd[0])->exists()) {
                $parts = explode(' ', $cd[1]);
                $initials = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
                DB::table('digital_cards')->insert([
                    'id'=>(string)Str::uuid(),'slug'=>$cd[0],'display_name'=>$cd[1],'job_title'=>$cd[2],
                    'company_name'=>$cd[3],'tagline'=>$cd[4],'email'=>$cd[5],'phone'=>$cd[6],'whatsapp'=>$cd[7],
                    'website'=>$cd[8],'address'=>$cd[9],'city'=>$cd[10],'theme_color'=>$cd[11],'company_id'=>$cd[12],
                    'user_id'=>$userId,'initials'=>$initials,'is_public'=>1,
                    'view_count'=>rand(15,300),'share_count'=>rand(2,40),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        $this->command->info('Phase 9 seeded: '.count($projects).' innovation projects, '.count($listings).' logistics listings, '.count($cards).' digital cards.');
    }
}
