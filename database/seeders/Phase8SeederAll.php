<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase8SeederAll extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')->whereNull('deleted_at')->limit(10)->get();
        if ($companies->isEmpty()) { $this->command->warn('No companies found.'); return; }
        $c1 = $companies[0]; $c2 = $companies[1] ?? $c1; $c3 = $companies[2] ?? $c1;
        $c4 = $companies[3] ?? $c1; $c5 = $companies[4] ?? $c1;
        $userId = DB::table('users')->value('id') ?? 'system';

        // ── EVENTS ───────────────────────────────────────────────────────────
        $events = [
            [(string)Str::uuid(),'cameroon-business-summit-2026','Cameroon Business Summit 2026',
             'The premier annual gathering of business leaders, investors, and government officials to chart the economic course of Cameroon.',
             'conference','in_person','open','2026-09-15 08:00:00','2026-09-17 18:00:00','Yaoundé','Cameroon','Palais des Congres',1,50000,500,$c1->id],
            [(string)Str::uuid(),'douala-tech-expo-2026','Douala Tech Expo 2026',
             'Showcase of technology innovations, startups, and digital solutions transforming Cameroon\'s economy.',
             'exhibition','in_person','open','2026-10-05 09:00:00','2026-10-07 17:00:00','Douala','Cameroon','Douala Grand Mall',1,25000,1000,$c2->id],
            [(string)Str::uuid(),'women-entrepreneurs-forum','Women Entrepreneurs Forum Cameroon',
             'Empowering women business owners with mentorship, funding access, and networking opportunities.',
             'networking','in_person','open','2026-08-22 09:00:00','2026-08-22 18:00:00','Yaoundé','Cameroon','Hilton Yaoundé',0,null,200,$c3->id],
            [(string)Str::uuid(),'agri-investment-day-2026','Agri-Investment Day 2026',
             'Connecting agricultural businesses with investors. Presentations from cocoa, palm oil, and food processing sectors.',
             'summit','in_person','open','2026-07-18 08:30:00','2026-07-18 17:00:00','Bafoussam','Cameroon','Hotel Residence',1,15000,150,$c4->id],
            [(string)Str::uuid(),'cameroon-startup-hackathon','Cameroon Startup Hackathon 2026',
             '48-hour innovation challenge for tech entrepreneurs. Build solutions for agriculture, health, fintech, and logistics.',
             'hackathon','in_person','open','2026-08-01 18:00:00','2026-08-03 18:00:00','Douala','Cameroon','StartupHub Bonanjo',0,null,300,$c5->id],
            [(string)Str::uuid(),'digital-marketing-webinar','Digital Marketing for SMEs — Free Webinar',
             'Learn practical digital marketing strategies tailored for Cameroonian businesses: social media, Google Ads, WhatsApp Business.',
             'webinar','virtual','open','2026-07-25 14:00:00','2026-07-25 16:00:00',null,'Cameroon',null,0,null,500,$c1->id],
            [(string)Str::uuid(),'cocoa-exporters-workshop','Cocoa Exporters Certification Workshop',
             'Hands-on training for cocoa producers and exporters on EU deforestation regulation compliance and organic certification.',
             'workshop','in_person','open','2026-08-12 09:00:00','2026-08-13 17:00:00','Bafoussam','Cameroon','Regional Chamber of Agriculture',1,30000,80,$c2->id],
        ];

        foreach ($events as $e) {
            if (!DB::table('events')->where('slug',$e[1])->exists()) {
                DB::table('events')->insert([
                    'id'=>$e[0],'title'=>$e[2],'slug'=>$e[1],'description'=>$e[3],
                    'category'=>$e[4],'format'=>$e[5],'status'=>$e[6],
                    'start_date'=>$e[7],'end_date'=>$e[8],
                    'location_city'=>$e[9],'location_country'=>$e[10],'venue_name'=>$e[11],
                    'is_paid'=>$e[12],'ticket_price'=>$e[13],'max_attendees'=>$e[14],
                    'attendee_count'=>rand(10,80),'view_count'=>rand(50,500),
                    'organizer_company_id'=>$e[15],'organizer_user_id'=>$userId,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── COMMUNITIES ───────────────────────────────────────────────────────
        $communities = [
            ['cameroon-tech-community','Cameroon Tech Community','Where Cameroonian tech builders connect',
             'The hub for software developers, data scientists, designers, and product builders in Cameroon.',
             'ict','industry','#0284c7'],
            ['cocoa-growers-network','Cocoa Growers Network','Uniting cocoa farmers across Cameroon',
             'A space for cocoa farmers, cooperatives, and exporters to exchange best practices and market prices.',
             'cocoa','industry','#92400e'],
            ['cameroon-women-in-business','Women in Business Cameroon','Empowering female entrepreneurs nationwide',
             'A supportive community for women entrepreneurs, executives, and professionals.',
             'general','professional','#be185d'],
            ['yaunde-entrepreneurs','Yaounde Entrepreneurs Club','The capital\'s business network',
             'Monthly meetups, peer advisory sessions, and collaboration for business owners in Yaounde.',
             'general','regional','#007a33'],
            ['douala-startup-hub','Douala Startup Hub','Innovation and startups in the economic capital',
             'Early-stage founders, investors, and mentors building the next generation of Cameroonian solutions.',
             'ict','special_interest','#6d28d9'],
            ['cameroon-agri-professionals','Cameroon Agri-Food Professionals','Modernising Cameroon\'s food systems',
             'Agronomists, food technologists, agri-entrepreneurs working to transform Cameroon\'s agriculture.',
             'agriculture','professional','#16a34a'],
        ];

        foreach ($communities as [$slug,$name,$tagline,$desc,$sector,$cat,$color]) {
            if (!DB::table('communities')->where('slug',$slug)->exists()) {
                $cid = DB::table('communities')->insertGetId([
                    'slug'=>$slug,'name'=>$name,'tagline'=>$tagline,'description'=>$desc,
                    'sector'=>$sector,'category'=>$cat,'cover_color'=>$color,
                    'admin_user_id'=>$userId,'member_count'=>rand(20,300),'post_count'=>rand(5,50),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('community_posts')->insert([
                    'community_id'=>$cid,'user_id'=>$userId,'type'=>'announcement',
                    'title'=>'Welcome to '.$name,
                    'body'=>'Welcome to our community! This is the place to connect, share knowledge, and grow together.',
                    'is_pinned'=>1,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        // ── KNOWLEDGE RESOURCES ───────────────────────────────────────────────
        $kResources = [
            [
                'slug'        => 'business-registration-guide-cameroon',
                'title'       => 'How to Register a Business in Cameroon (CFCE Guide)',
                'description' => 'Step-by-step guide to registering a company at the CFCE. Covers SA, SARL, and sole proprietorship.',
                'category'    => 'guide',
                'sector'      => 'general',
                'format'      => 'article',
                'is_featured' => 1,
                'body'        => <<<'EOT'
## Business Registration in Cameroon

Registering a company in Cameroon is done at the **Centre de Formalites de Creation des Entreprises (CFCE)**, operated by the Ministry of Commerce. The CFCE offers a one-stop-shop to complete all formalities within 72 hours.

### Legal Forms Available

| Form | Minimum Capital | Best For |
|------|----------------|----------|
| SARL (Private Limited) | No minimum | SMEs, startups |
| SA (Public Limited) | XAF 10 million | Larger companies |
| SNC (General Partnership) | None | Small partnerships |
| Sole Proprietor (EI) | None | Individuals |

### Required Documents

1. Completed CFCE application form
2. Certified copy of founders National ID cards
3. Proof of registered business address (lease or ownership certificate)
4. Company statutes (Articles of Association) — 2 copies, notarised
5. Declaration of non-conviction (casier judiciaire)
6. Bank certificate of capital deposit (for SA)

### Registration Steps

**Step 1 — Prepare documents** (1-2 days)
Have your statutes drafted by a notary. For SARL, all founding partners must sign.

**Step 2 — Submit at CFCE** (Day 1)
Submit all documents at the CFCE one-stop window. Pay the registration fee (XAF 35,000 for SARL).

**Step 3 — Tax registration / NIU** (Day 1-2)
The Centre des Impots assigns your NIU (Numero Identifiant Unique) — your tax ID.

**Step 4 — Social security registration** (Day 2)
CNPS registration is required if you will have employees.

**Step 5 — Publication in JACM** (Day 2-3)
Your incorporation is published in the Journal des Annonces et Communications du Ministere du Commerce.

### Costs Summary

- CFCE filing fee: XAF 35,000
- Notary fees for statutes: XAF 50,000 - 150,000 (varies)
- Publication fee: XAF 25,000
- **Total estimate: XAF 110,000 - 210,000**

### Contact
CFCE Yaounde: +237 222 230 110 | CFCE Douala: +237 233 421 910
EOT,
            ],
            [
                'slug'        => 'cameroon-tax-guide-2026',
                'title'       => 'Cameroon Tax Obligations for SMEs 2026',
                'description' => 'Complete guide to corporate tax, VAT, payroll taxes, and withholding taxes for SMEs in Cameroon.',
                'category'    => 'guide',
                'sector'      => 'finance',
                'format'      => 'article',
                'is_featured' => 1,
                'body'        => <<<'EOT'
## Cameroon Tax Guide for SMEs 2026

### Corporate Tax (Impot sur les Societes — IS)

Standard rate: **33%** on net profits

For SMEs with turnover below XAF 100M, a reduced regime may apply via the Simplified Real Profit Regime (BRS).

**Key deductible expenses:**
- Staff salaries and social charges
- Office rent and utilities
- Professional fees and services
- Depreciation of fixed assets
- Interest on business loans

### VAT (Taxe sur la Valeur Ajoutee — TVA)

- Standard rate: **19.25%**
- Threshold for mandatory registration: turnover above XAF 50 million per year
- Monthly filing required (by the 15th of the following month)
- Zero-rated: exports, certain medical equipment, books

### Withholding Tax (Precompte)

Applied at source on payments to suppliers:
- Registered suppliers: **5.5%**
- Unregistered suppliers: **11%**
- Services to foreign companies: **15%**

### CNPS (Social Security) Contributions

| Branch | Employer | Employee |
|--------|----------|----------|
| Family benefits | 7% | — |
| Occupational risk | 1.75-5% | — |
| Old age pension | 4.2% | 2.8% |
| **Total** | **~13%** | **2.8%** |

### Key Filing Deadlines

| Tax | Deadline |
|-----|----------|
| Monthly VAT | 15th of following month |
| Monthly CNPS | 15th of following month |
| Annual IS declaration | 15 March |
| Annual payroll statement | 15 March |
EOT,
            ],
            [
                'slug'        => 'sme-financing-options-cameroon',
                'title'       => 'SME Financing Options in Cameroon — 2026 Guide',
                'description' => 'Overview of bank loans, microfinance, government funds, and investment programmes available to Cameroonian businesses.',
                'category'    => 'guide',
                'sector'      => 'finance',
                'format'      => 'article',
                'is_featured' => 0,
                'body'        => <<<'EOT'
## SME Financing Options in Cameroon

### 1. Commercial Bank Loans

Major banks offering SME products:
- **Societe Generale Cameroun** — SME loans from XAF 5M, 12-84 months
- **Afriland First Bank** — SME package with flexible collateral
- **SCB Cameroun** — Working capital and investment loans
- **BICEC** — Credit PME with ARIZ guarantee

Typical requirements: 2 years of financial statements, business plan, collateral.

### 2. Microfinance Institutions (MFIs)

For smaller businesses (XAF 500K - 5M):
- **CamCCUL** — Credit Union network, national coverage
- **MC2 Network** — Rural community savings banks
- **ADIE Cameroun** — Micro-loans for entrepreneurs without collateral

### 3. Government Programmes

- **BDEAC** — Infrastructure and industrial projects from XAF 500M
- **FOGAPE** — Partial guarantees for bank loans for SMEs
- **APME** — Business development support and financing facilitation

### 4. Development Finance

- **AFD (Agence Francaise de Developpement)** — Sustainability-linked SME financing
- **IFC (International Finance Corporation)** — Equity and loans for scaling businesses
- **AfDB (African Development Bank)** — MSME support programmes

### Tips for Loan Applications

1. Maintain clean, audited accounts for at least 2 years
2. Register with DGI and obtain NIU before applying
3. Prepare a detailed 3-year business plan and financial projections
4. Consider FOGAPE guarantee to improve your credit rating
EOT,
            ],
            [
                'slug'        => 'eu-deforestation-regulation-guide',
                'title'       => 'EU Deforestation Regulation (EUDR) — Guide for Cameroonian Exporters',
                'description' => 'What EUDR means for cocoa, timber, palm oil, and coffee exporters in Cameroon. Compliance steps and deadlines.',
                'category'    => 'regulation',
                'sector'      => 'agriculture',
                'format'      => 'article',
                'is_featured' => 1,
                'body'        => <<<'EOT'
## EU Deforestation Regulation (EUDR) Guide for Cameroon

The EU Deforestation Regulation (EU 2023/1115) requires companies selling certain commodities into the EU to prove they did not contribute to deforestation.

### Products Covered (Affecting Cameroon)

- **Cocoa** and cocoa products
- **Timber** and wood products (including furniture, paper)
- **Palm oil** and derivatives
- **Coffee**

### What Exporters Must Do

**1. Geolocation data collection**
Collect GPS coordinates (polygon data) for every plot of land where the product was grown or harvested.

**2. Due diligence statement**
Submit a due diligence statement to the EU Information System before placing products on the EU market.

**3. No deforestation after 31 December 2020**
Products must come from land that was not deforested after this date.

**4. Legality compliance**
Products must comply with all laws of the country of production (land tenure, labour, tax).

### Compliance Steps for Cameroonian Exporters

1. Map your supply chain — know every farm, cooperative, and plot
2. Collect geolocation polygons from all farmers
3. Document evidence of forest-free production
4. Establish internal traceability systems
5. Partner with a certification body (Rainforest Alliance, FSC)
6. Register with the EU EUDR portal when it opens

### Key Dates

- **30 December 2024** — Regulation enters into force for large operators
- **30 June 2025** — Extended deadline (per EU Commission)
- **30 December 2025** — Applies to SMEs
EOT,
            ],
            [
                'slug'        => 'startup-pitch-deck-template',
                'title'       => 'Startup Pitch Deck Template — Investor-Ready',
                'description' => '12-slide pitch deck template for Cameroonian startups seeking angel or VC investment.',
                'category'    => 'template',
                'sector'      => 'general',
                'format'      => 'document',
                'is_featured' => 1,
                'body'        => <<<'EOT'
## Startup Pitch Deck — 12 Slide Template

### Slide 1: Cover
- Company name and logo
- One-line tagline
- Founder names and contact

### Slide 2: Problem
Describe the problem you solve. Use data: How many people experience this? What does it cost them? Keep to 3 bullet points maximum.

### Slide 3: Solution
What does your product/service do? Show the product if possible. Explain the "aha" moment.

### Slide 4: Product Demo
Screenshot, video, or live demo. Show, do not just tell.

### Slide 5: Market Size
- TAM (Total Addressable Market) — global or continental
- SAM (Serviceable Addressable Market) — regional/sector
- SOM (Serviceable Obtainable Market) — your realistic share in 3-5 years

### Slide 6: Business Model
How do you make money? Pricing, margins, unit economics.

### Slide 7: Traction
Key milestones, revenue, users, partnerships. Lead with the most impressive number.

### Slide 8: Go-To-Market Strategy
How do you acquire customers? Channels, cost per acquisition, payback period.

### Slide 9: Competitive Landscape
2x2 matrix or list of competitors and your unique differentiation.

### Slide 10: Team
Photos, names, key experience. Why are YOU the team to solve this?

### Slide 11: Financial Projections
3-year revenue, expenses, and EBITDA projections. Key assumptions stated clearly.

### Slide 12: The Ask
- How much are you raising?
- What will you use it for? (breakdown)
- What milestones will you reach?
EOT,
            ],
            [
                'slug'        => 'whatsapp-business-guide-smes',
                'title'       => 'Using WhatsApp Business for Sales — SME Guide',
                'description' => 'How to set up and use WhatsApp Business to generate leads, manage customers, and close sales in Cameroon.',
                'category'    => 'guide',
                'sector'      => 'ict',
                'format'      => 'article',
                'is_featured' => 0,
                'body'        => <<<'EOT'
## WhatsApp Business Guide for Cameroonian SMEs

WhatsApp has over 5 million users in Cameroon, making it the most powerful sales and customer service tool available to local businesses.

### Setting Up WhatsApp Business

1. Download WhatsApp Business (free on Android/iOS)
2. Register with your business phone number
3. Complete your Business Profile: name, category, description, address, hours, website
4. Upload a professional logo as your profile photo

### Key Features to Use

**Catalogue** — List your products with photos, prices, and descriptions.

**Quick Replies** — Save responses to frequent questions (/price, /hours, /location) to reply instantly.

**Labels** — Tag conversations: New Customer, Pending Payment, Order Confirmed, Follow Up.

**Broadcast Lists** — Send offers to up to 256 saved contacts at once.

**Away Messages** — Set automatic replies when unavailable.

### Sales Workflow with WhatsApp

1. Customer sends first message (from ad, referral, or website)
2. Auto-reply confirms receipt and shares catalogue link
3. Qualify the customer (budget, timeline, specific needs)
4. Send quotation as PDF or voice note explanation
5. Follow up in 24h if no response
6. Close with payment link (MTN MoMo, Orange Money, or bank transfer)
7. After delivery, ask for a review or referral

### WhatsApp Ads (Meta)

Connect Facebook/Instagram ads to WhatsApp with "Click to WhatsApp" buttons. Cost per lead is often XAF 500-2,000.
EOT,
            ],
            [
                'slug'        => 'iso-9001-certification-guide',
                'title'       => 'ISO 9001 Certification — Guide for Cameroonian Businesses',
                'description' => 'Step-by-step guide to obtaining ISO 9001:2015 Quality Management System certification in Cameroon.',
                'category'    => 'guide',
                'sector'      => 'general',
                'format'      => 'article',
                'is_featured' => 0,
                'body'        => <<<'EOT'
## ISO 9001 Certification Guide for Cameroon

ISO 9001:2015 is the international standard for Quality Management Systems. Certification demonstrates to customers and partners that your processes meet international quality requirements.

### Benefits for Cameroonian Companies

- Access to government and international tenders (many require ISO certification)
- Improved internal processes and reduced waste
- Increased customer confidence and retention
- Required for suppliers to multinationals (Total, MTN, Guinness, Nestle)

### Steps to Certification

**Phase 1 — Gap Analysis** (1-2 months): Compare your current processes against ISO 9001 requirements.

**Phase 2 — Documentation** (2-4 months): Create Quality Manual, Quality Policy, process procedures, risk register.

**Phase 3 — Implementation** (3-6 months): Train staff, implement procedures, begin recording evidence.

**Phase 4 — Internal Audit** (1 month): Full internal audit before external audit.

**Phase 5 — Stage 1 Audit**: Certification body reviews documentation remotely.

**Phase 6 — Stage 2 Audit**: Auditors visit premises. If compliant, certificate issued.

### Approved Certification Bodies in Cameroon

- **Bureau Veritas Cameroun** (Douala/Yaounde)
- **SGS Cameroun** (Douala)
- **AFNOR Group** (via regional office)
- **Intertek Cameroon** (Douala)

### Typical Costs

- Consultancy to prepare: XAF 1.5M - 4M
- Certification audit: XAF 800K - 2M
- Annual surveillance audits: XAF 400K - 800K
- **Total Year 1: XAF 2.7M - 6.8M**

### Timeline

Typically 9-18 months from start to certification, depending on company size.
EOT,
            ],
            [
                'slug'        => 'employment-contract-template-cameroon',
                'title'       => 'Employment Contract Template — Cameroon Labour Code Compliant',
                'description' => 'Standard CDI employment contract template for Cameroon, compliant with Labour Code Law No. 92/007.',
                'category'    => 'template',
                'sector'      => 'general',
                'format'      => 'document',
                'is_featured' => 0,
                'body'        => <<<'EOT'
## Employment Contract Template (CDI)

Compliant with Cameroon Labour Code (Loi No. 92/007 du 14 Aout 1992).

---

**CONTRAT DE TRAVAIL A DUREE INDETERMINEE**

Entre les soussignes :

**L\'Employeur :**
Raison sociale : ___________________
NIU : ___________________
Represente par : ___________________ en qualite de ___________________

**Le Travailleur :**
Nom et Prenoms : ___________________
CNI No. : ___________________

### Article 1 — Engagement
Prise de fonction le ___________________.

### Article 2 — Duree
Contrat a duree indeterminee.

### Article 3 — Periode d\'essai
Periode d\'essai de ___ mois, renouvelable une fois.

### Article 4 — Remuneration
Salaire mensuel brut de XAF _______________, verse le dernier jour ouvrable.

### Article 5 — Horaires de Travail
40 heures hebdomadaires, conformement aux dispositions legales.

### Article 6 — Conges Payes
1.5 jours de conge paye par mois de travail effectif (18 jours/an).

### Article 7 — Resiliation
Preavis de ___ mois, conformement a la convention collective applicable.

Fait a ___________________, le ___________________

Signature de l\'Employeur          Signature du Travailleur
EOT,
            ],
        ];

        foreach ($kResources as $r) {
            if (!DB::table('knowledge_resources')->where('slug',$r['slug'])->exists()) {
                DB::table('knowledge_resources')->insert([
                    'slug'          => $r['slug'],
                    'title'         => $r['title'],
                    'description'   => $r['description'],
                    'category'      => $r['category'],
                    'sector'        => $r['sector'],
                    'format'        => $r['format'],
                    'is_free'       => 1,
                    'is_featured'   => $r['is_featured'],
                    'is_published'  => 1,
                    'body'          => $r['body'],
                    'download_count'=> rand(10,200),
                    'view_count'    => rand(50,800),
                    'author_user_id'=> $userId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        $this->command->info('Phase 8 seeded: '.count($events).' events, '.count($communities).' communities, '.count($kResources).' knowledge resources.');
    }
}
