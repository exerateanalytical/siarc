<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase10SeederAll extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')->whereNull('deleted_at')->limit(10)->get();
        if ($companies->isEmpty()) { $this->command->warn('No companies found.'); return; }
        $c1 = $companies[0]; $c2 = $companies[1] ?? $c1; $c3 = $companies[2] ?? $c1;
        $c4 = $companies[3] ?? $c1; $c5 = $companies[4] ?? $c1;
        $userId = DB::table('users')->value('id') ?? 'system';

        // ── SHARED ASSETS ─────────────────────────────────────────────────────
        $assets = [
            ['warehouse-douala-bonaberi-2000m2','Warehouse Space — Douala Bonaberi (2,000 m²)','warehouse',
             'Secure covered warehouse with loading dock, 24/7 security, and forklift access. Ideal for import/export storage near the port.',
             'monthly',3500000,'Douala','Excellent','2,000 m² covered + 500 m² yard',$c1->id],
            ['refrigerated-truck-15t','Refrigerated Truck 15T — Available for Hire','vehicle',
             'Well-maintained 15-tonne refrigerated truck with experienced driver. Available for perishable transport across Cameroon.',
             'daily',180000,'Douala','Good','15 tonnes, -18°C to +4°C',$c2->id],
            ['industrial-generator-500kva','Industrial Generator 500 KVA','generator',
             'Caterpillar 500 KVA diesel generator available for event or backup power rental. Includes delivery and technician.',
             'daily',250000,'Yaoundé','Excellent','500 KVA, diesel, sound-proofed',$c3->id],
            ['cold-storage-bafoussam','Cold Storage Facility — Bafoussam','cold_storage',
             'Modern cold storage rooms for agricultural produce. Multiple temperature zones, ideal for vegetables, fruits, and dairy.',
             'monthly',1200000,'Bafoussam','Excellent','300 m³, multi-zone 0-8°C',$c4->id],
            ['office-space-akwa-coworking','Office Space — Akwa Douala (Furnished)','office_space',
             'Furnished private offices and co-working desks in central Akwa. High-speed internet, meeting rooms, and reception included.',
             'monthly',450000,'Douala','Excellent','Up to 12 desks + 2 meeting rooms',$c5->id],
            ['excavator-cat-320','Excavator CAT 320 — Construction Hire','machinery',
             'CAT 320 hydraulic excavator with operator for construction and earthworks. Daily or weekly hire across Littoral and West.',
             'daily',320000,'Douala','Good','20-tonne class, operator included',$c1->id],
            ['event-hall-yaounde-500pax','Event Hall — Yaoundé (500 capacity)','event_space',
             'Air-conditioned event hall for conferences, weddings, and corporate events. Stage, sound system, and catering kitchen.',
             'per_use',800000,'Yaoundé','Excellent','500 seated, AV + catering kitchen',$c2->id],
        ];
        foreach ($assets as $a) {
            if (!DB::table('shared_assets')->where('slug',$a[0])->exists()) {
                DB::table('shared_assets')->insert([
                    'id'=>(string)Str::uuid(),'slug'=>$a[0],'title'=>$a[1],'category'=>$a[2],'description'=>$a[3],
                    'pricing_model'=>$a[4],'price'=>$a[5],'location_city'=>$a[6],'condition'=>$a[7],'capacity_spec'=>$a[8],
                    'company_id'=>$a[9],'user_id'=>$userId,'contact_phone'=>'+237 6'.rand(70000000,99999999),
                    'availability'=>'available','status'=>'active',
                    'inquiry_count'=>rand(0,8),'view_count'=>rand(20,300),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── COMPLIANCE REQUIREMENTS ───────────────────────────────────────────
        $reqs = [
            [
                'slug'=>'monthly-vat-declaration','title'=>'Monthly VAT Declaration (TVA)','category'=>'tax',
                'authority'=>'DGI (Direction Générale des Impôts)','frequency'=>'monthly','applies_to'=>'all','sector'=>'general',
                'penalty'=>'10% penalty + 1.5% monthly interest on late payment',
                'desc'=>'Businesses with turnover above XAF 50M must file monthly VAT returns by the 15th.',
                'body'=> <<<'EOT'
## Monthly VAT Declaration (TVA)

Businesses registered for VAT must declare and remit collected VAT to the DGI every month.

### Who must file
- Any business with annual turnover above **XAF 50 million**
- Standard VAT rate: **19.25%**

### Deadline
By the **15th** of the month following the taxable period.

### How to file
1. Log into the DGI e-services portal (teledeclaration)
2. Complete the monthly VAT return (declaration de TVA)
3. Declare output VAT (collected) and input VAT (deductible)
4. Pay the net VAT due via bank transfer or at the tax centre

### Penalties for non-compliance
- 10% penalty on the amount due
- 1.5% interest per month of delay
- Possible suspension of NIU for repeated default
EOT,
            ],
            [
                'slug'=>'monthly-cnps-contributions','title'=>'Monthly CNPS Social Security Contributions','category'=>'labour',
                'authority'=>'CNPS (Caisse Nationale de Prévoyance Sociale)','frequency'=>'monthly','applies_to'=>'all','sector'=>'general',
                'penalty'=>'Penalties and surcharges on late or unpaid contributions',
                'desc'=>'Employers must register employees and remit social contributions monthly.',
                'body'=> <<<'EOT'
## Monthly CNPS Contributions

All employers must register with CNPS and remit social security contributions for every employee.

### Contribution rates
| Branch | Employer | Employee |
|--------|----------|----------|
| Family benefits | 7% | — |
| Occupational risk | 1.75-5% | — |
| Old age / pension | 4.2% | 2.8% |

### Deadline
By the **15th** of the following month.

### Requirements
1. Register the company and each employee with CNPS
2. Declare salaries via the CNPS portal
3. Pay employer + employee contributions
4. Keep proof of payment for inspections

### Penalties
Late payment incurs surcharges. Non-registration of employees is a serious offence under the Labour Code.
EOT,
            ],
            [
                'slug'=>'annual-corporate-tax-return','title'=>'Annual Corporate Income Tax Return (IS)','category'=>'tax',
                'authority'=>'DGI','frequency'=>'annual','applies_to'=>'sarl','sector'=>'general',
                'penalty'=>'Penalties up to 100% for fraud; interest on late filing',
                'desc'=>'Companies must file annual corporate income tax returns by 15 March.',
                'body'=> <<<'EOT'
## Annual Corporate Income Tax Return (Impot sur les Societes)

Companies file an annual declaration of results (DSF - Declaration Statistique et Fiscale).

### Rate
- Standard corporate tax: **33%** of net profit
- Minimum tax applies even at a loss (based on turnover)

### Deadline
**15 March** of the year following the financial year.

### Required documents
1. Balance sheet and income statement
2. Tax computation (liasse fiscale)
3. Annexes per OHADA accounting standards

### Penalties
- Late filing: interest + 10% penalty
- Under-declaration or fraud: penalties up to 100% of the evaded tax
EOT,
            ],
            [
                'slug'=>'rccm-annual-update','title'=>'RCCM / Trade Register Update','category'=>'corporate',
                'authority'=>'Greffe du Tribunal de Commerce','frequency'=>'as_needed','applies_to'=>'all','sector'=>'general',
                'penalty'=>'Fines and inability to transact legally if not updated',
                'desc'=>'Any change in company structure must be filed at the trade register.',
                'body'=> <<<'EOT'
## RCCM / Trade Register Updates

The Registre du Commerce et du Credit Mobilier (RCCM) records your company's legal existence and key facts.

### When to file an update
- Change of directors or managers
- Change of registered address
- Capital increase or decrease
- Change of company name or activity
- Cessation or transfer of business

### Process
File the modification at the Greffe (commercial court registry) with supporting documents (minutes, updated statutes).

### Why it matters
An out-of-date RCCM can invalidate contracts, block bank operations, and create personal liability for directors.
EOT,
            ],
            [
                'slug'=>'environmental-impact-notice','title'=>'Environmental Compliance Notice / Audit','category'=>'environmental',
                'authority'=>'MINEPDED','frequency'=>'annual','applies_to'=>'specific_sector','sector'=>'general',
                'penalty'=>'Suspension of operations; fines for environmental damage',
                'desc'=>'Industrial and extractive businesses require environmental authorisations.',
                'body'=> <<<'EOT'
## Environmental Compliance (MINEPDED)

Businesses with environmental impact (manufacturing, mining, timber, agro-processing) must hold valid environmental authorisations.

### Key obligations
- **Environmental Impact Assessment (EIA)** before starting operations
- Annual environmental audit for classified installations
- Waste management and discharge permits
- Reforestation obligations (timber sector)

### Authority
Ministere de l'Environnement, de la Protection de la Nature et du Developpement Durable (MINEPDED).

### Penalties
Operating without authorisation can lead to suspension, heavy fines, and criminal liability for serious pollution.
EOT,
            ],
            [
                'slug'=>'business-licence-patente','title'=>'Business Licence (Patente) Renewal','category'=>'tax',
                'authority'=>'Commune / DGI','frequency'=>'annual','applies_to'=>'all','sector'=>'general',
                'penalty'=>'Surcharge and closure risk for trading without a patente',
                'desc'=>'Annual business operating licence based on turnover and activity.',
                'body'=> <<<'EOT'
## Business Licence (Patente)

The patente is an annual licence authorising commercial activity, payable to the local council and tax administration.

### How it is calculated
Based on turnover, activity type, and rental value of premises.

### Deadline
Within the **first two months** of the year, or within 2 months of starting a new business.

### Display requirement
The patente certificate must be displayed at your place of business and shown on request.

### Penalties
Trading without a valid patente exposes the business to surcharges and possible administrative closure.
EOT,
            ],
        ];
        foreach ($reqs as $r) {
            if (!DB::table('compliance_requirements')->where('slug',$r['slug'])->exists()) {
                DB::table('compliance_requirements')->insert([
                    'slug'=>$r['slug'],'title'=>$r['title'],'description'=>$r['desc'],'body'=>$r['body'],
                    'category'=>$r['category'],'authority'=>$r['authority'],'frequency'=>$r['frequency'],
                    'applies_to'=>$r['applies_to'],'sector'=>$r['sector'],'penalty_info'=>$r['penalty'],
                    'is_published'=>1,'view_count'=>rand(40,500),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── COMPLIANCE TRACKER (sample for first company) ─────────────────────
        if (DB::table('compliance_tracker')->where('company_id',$c1->id)->count() === 0) {
            $items = [
                ['Monthly VAT Declaration — June 2026','tax','compliant','2026-06-15','2026-06-12'],
                ['CNPS Contributions — June 2026','labour','compliant','2026-06-15','2026-06-14'],
                ['Annual Corporate Tax Return 2025','tax','compliant','2026-03-15','2026-03-10'],
                ['Business Licence (Patente) 2026','tax','in_progress','2026-02-28',null],
                ['Environmental Audit 2026','environmental','pending','2026-09-30',null],
                ['Monthly VAT Declaration — July 2026','tax','pending','2026-07-15',null],
            ];
            foreach ($items as $it) {
                DB::table('compliance_tracker')->insert([
                    'company_id'=>$c1->id,'user_id'=>$userId,'title'=>$it[0],'category'=>$it[1],
                    'status'=>$it[2],'due_date'=>$it[3],'completed_date'=>$it[4],
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── PRM SAMPLE PARTNERS (for first user/company) ──────────────────────
        if (DB::table('partners')->where('user_id',$userId)->count() === 0) {
            $partners = [
                ['Brasseries du Cameroun','supplier','active','strategic','Paul Ngassa','p.ngassa@example.cm','+237 677 111 222',45000000,'Primary raw materials supplier. Quarterly contracts.'],
                ['Carrefour Market Douala','customer','active','preferred','Marie Etonde','m.etonde@example.cm','+237 699 333 444',28000000,'Key retail distribution channel in Littoral.'],
                ['TransCam Logistics','vendor','active','standard','Eric Fotso','e.fotso@example.cm','+237 655 555 666',12000000,'Freight and last-mile delivery partner.'],
                ['AgroExport SARL','distributor','prospect','trial','Sandra Mballa','s.mballa@example.cm','+237 681 777 888',null,'Potential export distributor for West Africa. In negotiation.'],
                ['FinTech Partners Cameroun','strategic','active','strategic','Joseph Biya','j.biya@example.cm','+237 670 999 000',60000000,'Digital payment integration partner.'],
            ];
            foreach ($partners as $i => $p) {
                $pid = DB::table('partners')->insertGetId([
                    'owner_company_id'=>$c1->id,'user_id'=>$userId,'partner_name'=>$p[0],
                    'relationship_type'=>$p[1],'status'=>$p[2],'tier'=>$p[3],
                    'contact_name'=>$p[4],'contact_email'=>$p[5],'contact_phone'=>$p[6],
                    'value_estimate'=>$p[7],'notes'=>$p[8],
                    'last_interaction_date'=>now()->subDays(rand(2,40))->toDateString(),
                    'interaction_count'=>rand(1,5),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('partner_interactions')->insert([
                    'partner_id'=>$pid,'user_id'=>$userId,'type'=>'meeting',
                    'subject'=>'Initial partnership review',
                    'summary'=>'Reviewed terms, pricing, and next-quarter commitments.',
                    'interaction_date'=>now()->subDays(rand(2,40))->toDateString(),
                    'created_at'=>now(),
                ]);
            }
        }

        $this->command->info('Phase 10 seeded: '.count($assets).' shared assets, '.count($reqs).' compliance requirements + tracker, 5 PRM partners.');
    }
}
