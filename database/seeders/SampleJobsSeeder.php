<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SampleJobsSeeder extends Seeder
{
    public function run(): void
    {
        $mtn  = DB::table('companies')->where('slug','mtn-cameroun-sa')->value('id');
        $afr  = DB::table('companies')->where('slug','afriland-first-bank')->value('id');
        $eneo = DB::table('companies')->where('slug','eneo-cameroon-sa')->value('id');
        $camtel = DB::table('companies')->where('slug','cameroon-telecommunications')->value('id');
        $user = DB::table('users')->first();
        if (!$mtn || !$user) { $this->command->info('No companies/users found, skipping'); return; }
        if (!$eneo) $eneo = $camtel;

        $jobs = [
            ['company_id'=>$mtn,'title_en'=>'Senior Network Engineer','title_fr'=>'Ingénieur Réseau Senior','type'=>'full_time','location'=>'Douala','department'=>'Technology','salary_min'=>450000,'salary_max'=>650000,'description_en'=>'Lead MTN Cameroon 5G rollout across Central and Littoral regions. Design, deploy and maintain core network infrastructure.','description_fr'=>'Diriger le déploiement 5G de MTN Cameroun dans les régions du Centre et du Littoral.','status'=>'open'],
            ['company_id'=>$mtn,'title_en'=>'Digital Marketing Manager','title_fr'=>'Responsable Marketing Digital','type'=>'full_time','location'=>'Yaoundé','department'=>'Marketing','salary_min'=>350000,'salary_max'=>500000,'description_en'=>'Drive MTN Cameroon digital campaigns across social, SEO, and mobile channels.','description_fr'=>'Piloter les campagnes digitales de MTN Cameroun sur les réseaux sociaux.','status'=>'open'],
            ['company_id'=>$afr,'title_en'=>'Credit Analyst','title_fr'=>'Analyste Crédit','type'=>'full_time','location'=>'Douala','department'=>'Finance','salary_min'=>300000,'salary_max'=>450000,'description_en'=>'Assess commercial and retail credit applications, prepare risk reports for the credit committee.','description_fr'=>'Évaluer les demandes de crédit commercial et retail, préparer des rapports de risque.','status'=>'open'],
            ['company_id'=>$afr,'title_en'=>'IT Security Intern','title_fr'=>'Stagiaire Sécurité Informatique','type'=>'internship','location'=>'Douala','department'=>'IT','salary_min'=>80000,'salary_max'=>120000,'description_en'=>'6-month internship in the IT Security team. Assist with vulnerability assessments and incident response.','description_fr'=>'Stage de 6 mois au sein de l\'équipe Sécurité Informatique.','status'=>'open'],
            ['company_id'=>$eneo,'title_en'=>'Electrical Engineer','title_fr'=>'Ingénieur Électricien','type'=>'full_time','location'=>'Bafoussam','department'=>'Operations','salary_min'=>400000,'salary_max'=>600000,'description_en'=>'Manage ENEO distribution networks in the West region. Coordinate with maintenance teams and local authorities.','description_fr'=>'Gérer les réseaux de distribution ENEO dans la région de l\'Ouest.','status'=>'open'],
            ['company_id'=>$mtn,'title_en'=>'Data Scientist','title_fr'=>'Data Scientist','type'=>'full_time','location'=>'Douala','department'=>'Technology','salary_min'=>500000,'salary_max'=>750000,'description_en'=>'Analyze subscriber data to drive product decisions and network optimization for MTN Cameroon.','description_fr'=>'Analyser les données des abonnés pour orienter les décisions produit et l\'optimisation réseau.','status'=>'open'],
        ];

        foreach ($jobs as $j) {
            DB::table('job_postings')->insertOrIgnore(array_merge($j, [
                'id'         => Str::uuid()->toString(),
                'posted_by'  => $user->id,
                'deadline'   => now()->addDays(rand(30,90))->toDateString(),
                'created_at' => now()->subDays(rand(1,14)),
                'updated_at' => now(),
            ]));
        }

        // Blog posts (bigint PK — no id field)
        if (DB::table('blog_posts')->count() === 0) DB::table('blog_posts')->insert([
            [
                'slug' => 'cameroon-capital-markets-2026',
                'author_id' => null, 'category_id' => 1,
                'title_en' => 'Cameroon Capital Markets: 2026 Outlook',
                'title_fr' => 'Marchés de Capitaux au Cameroun : Perspectives 2026',
                'body_en' => '<p>The Douala Stock Exchange (DSX) is poised for significant growth in 2026, driven by renewed investor appetite, regulatory reforms by the CMF, and several high-profile share offerings in telecoms and energy.</p><p>MTN Cameroon and ENEO have both filed preliminary prospectuses. This article examines the macro backdrop, CMF approval timelines, and what investors should watch.</p><h3>Key Sectors to Watch</h3><ul><li><strong>Telecoms:</strong> 5G rollout driving CapEx needs</li><li><strong>Energy:</strong> Power infrastructure privatisation</li><li><strong>Banking:</strong> Loan book expansion post-pandemic</li></ul>',
                'body_fr' => '<p>La Bourse des Valeurs Mobilières de Douala est en bonne voie pour une croissance significative en 2026, portée par un appétit renouvelé des investisseurs et plusieurs offres publiques majeures dans les secteurs des télécommunications et de l\'énergie.</p>',
                'excerpt_en' => 'The DSX is set for strong growth in 2026, with major share offerings expected in telecoms and energy.',
                'excerpt_fr' => 'La DSX est prête pour une forte croissance en 2026, avec des offres majeures attendues.',
                'published_at' => now()->subDays(3), 'is_published' => 1, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'author_id' => null, 'category_id' => 1, 'slug' => 'how-to-invest-cameroon-beginners-guide',
                'title_en' => 'How to Invest in Cameroonian Companies: A Beginner Guide',
                'title_fr' => 'Comment Investir dans les Entreprises Camerounaises : Guide du Débutant',
                'body_en' => '<p>Investing in Cameroonian companies has never been more accessible. With Galerie virtuelle de l\'artisanat du Cameroun, you can browse verified companies, review CMF-approved share offerings, and pledge from as little as 50,000 XAF.</p><h3>Step 1: Create Your Account</h3><p>Sign up and complete your investor profile including KYC documents.</p><h3>Step 2: Browse Offerings</h3><p>Filter by sector, minimum investment, and instrument type.</p><h3>Step 3: Pledge and Pay</h3><p>Enter your pledge amount, choose MTN MoMo, Orange Money, or bank transfer, and complete within 24 hours.</p>',
                'body_fr' => '<p>Investir dans les entreprises camerounaises n\'a jamais été aussi accessible. Avec Galerie virtuelle de l\'artisanat du Cameroun, vous pouvez parcourir les entreprises vérifiées et investir dès 50 000 XAF.</p>',
                'excerpt_en' => 'A step-by-step guide to making your first investment in a Cameroonian company.',
                'excerpt_fr' => 'Un guide étape par étape pour faire votre premier investissement dans une entreprise camerounaise.',
                'published_at' => now()->subDays(7), 'is_published' => 1, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'author_id' => null, 'category_id' => 1, 'slug' => 'mtn-cameroon-bond-offering-explainer',
                'title_en' => 'MTN Cameroon Bond Offering: What You Need to Know',
                'title_fr' => 'Obligations MTN Cameroun : Ce que Vous Devez Savoir',
                'body_en' => '<p>MTN Cameroon\'s 5 billion XAF bond offering has attracted significant interest since opening. Here is everything you need to know before pledging.</p><h3>Key Terms</h3><ul><li><strong>Instrument:</strong> Bonds (fixed income)</li><li><strong>Coupon:</strong> 7.5% per annum</li><li><strong>Tenor:</strong> 5 years</li><li><strong>Minimum:</strong> 100,000 XAF</li></ul><h3>Risk Factors</h3><p>Like all investments, bonds carry risk. Review the full prospectus before committing capital.</p>',
                'body_fr' => '<p>L\'offre d\'obligations de MTN Cameroun de 5 milliards XAF a suscité un grand intérêt. Voici tout ce que vous devez savoir avant d\'investir.</p>',
                'excerpt_en' => 'Everything investors need to know about MTN Cameroon\'s 5B XAF bond offering.',
                'excerpt_fr' => 'Tout ce que les investisseurs doivent savoir sur l\'offre d\'obligations MTN Cameroun.',
                'published_at' => now()->subDays(12), 'is_published' => 1, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // Knowledge articles (bigint PK — no id field)
        if (DB::table('knowledge_articles')->count() === 0) {
            DB::table('knowledge_articles')->insert([
                ['slug'=>'how-to-complete-kyc','title_en'=>'How do I verify my identity (KYC)?','title_fr'=>'Comment vérifier mon identité (KYC) ?','body_en'=>'<p>KYC (Know Your Customer) is required before you can invest above 500,000 XAF. You need to upload a government-issued ID and a recent proof of address.</p><h3>Accepted Documents</h3><ul><li>National identity card (CNI)</li><li>Passport</li><li>Driving licence</li></ul><p>Documents are reviewed within 24-48 business hours.</p>','body_fr'=>'<p>Le KYC est requis avant d\'investir au-delà de 500 000 XAF.</p>','category_id'=>1,'view_count'=>0,'is_published'=>1,'created_at'=>now(),'updated_at'=>now()],
                ['slug'=>'how-to-claim-company','title_en'=>'How do I claim my company listing?','title_fr'=>'Comment revendiquer ma fiche d\'entreprise ?','body_en'=>'<p>If your company appears in the Galerie virtuelle de l\'artisanat du Cameroun directory, you can claim ownership to manage your profile, post jobs, and respond to reviews.</p><ol><li>Find your company in the directory</li><li>Click "Claim this company" on the company page</li><li>Submit proof of authority (RCCM extract or power of attorney)</li><li>Our team reviews within 3 business days</li></ol>','body_fr'=>'<p>Si votre entreprise figure dans l\'annuaire Galerie virtuelle de l\'artisanat du Cameroun, vous pouvez en revendiquer la propriété.</p>','category_id'=>1,'view_count'=>0,'is_published'=>1,'created_at'=>now(),'updated_at'=>now()],
                ['slug'=>'accepted-payment-methods','title_en'=>'What payment methods are accepted?','title_fr'=>'Quels modes de paiement sont acceptés ?','body_en'=>'<p>Galerie virtuelle de l\'artisanat du Cameroun accepts three payment methods:</p><ul><li><strong>MTN Mobile Money</strong> — instant confirmation</li><li><strong>Orange Money</strong> — instant confirmation</li><li><strong>Bank Transfer</strong> — 1-2 business days</li></ul><p>You have 24 hours to complete payment after pledging.</p>','body_fr'=>'<p>Galerie virtuelle de l\'artisanat du Cameroun accepte trois modes de paiement pour les engagements d\'investissement.</p>','category_id'=>1,'view_count'=>0,'is_published'=>1,'created_at'=>now(),'updated_at'=>now()],
                ['slug'=>'share-allocation-process','title_en'=>'How does the share allocation process work?','title_fr'=>'Comment fonctionne l\'allocation des actions ?','body_en'=>'<p>After an offering closes, allocation works as follows:</p><ol><li>Offering closes on deadline or when fully subscribed</li><li>CMF approves final allocations (5-10 business days)</li><li>Shares allocated proportionally if oversubscribed</li><li>Excess payments refunded within 5 business days</li></ol>','body_fr'=>'<p>Après la clôture d\'une offre, l\'allocation se déroule comme suit.</p>','category_id'=>1,'view_count'=>0,'is_published'=>1,'created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        // Announcements (bigint PK — no id field)
        if (DB::table('announcements')->count() === 0) {
            DB::table('announcements')->insert([
                ['title_en'=>'New: Job Board & CV Builder now live','title_fr'=>'Nouveau : Offres d\'emploi et CV builder maintenant disponibles','body_en'=>'Browse jobs from top Cameroonian companies and build your digital CV.','body_fr'=>'Parcourez les offres d\'emploi et créez votre CV numérique.','type'=>'info','audience'=>'all','is_published'=>1,'starts_at'=>now()->subDay(),'ends_at'=>now()->addDays(30),'created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        // Company products (bigint PK — no id field)
        if (DB::table('company_products')->count() === 0) {
            $prods = [
                [$mtn,'MTN Business Connect','MTN Business Connect','Enterprise voice and data bundles with dedicated account management.','Forfaits voix et data entreprise avec gestion de compte dédiée.',50000],
                [$mtn,'MTN MoMo for Business','MTN MoMo pour Entreprises','Merchant payment collection via Mobile Money API.','Collecte de paiements marchands via API Mobile Money.',null],
                [$afr,'Business Current Account','Compte Courant Entreprise','Zero-fee current account for SMEs with online banking.','Compte courant sans frais pour PME avec banque en ligne.',null],
                [$afr,'SME Credit Line','Ligne de Crédit PME','Revolving credit from 5M to 500M XAF for eligible businesses.','Crédit renouvelable de 5M à 500M XAF pour entreprises éligibles.',null],
            ];
            foreach ($prods as [$cid,$en,$fr,$den,$dfr,$price]) {
                DB::table('company_products')->insert(['company_id'=>$cid,'name_en'=>$en,'name_fr'=>$fr,'description_en'=>$den,'description_fr'=>$dfr,'price'=>$price,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);
            }
        }

        // Offering FAQs
        $offerings = DB::table('share_offerings')->whereNull('deleted_at')->get();
        // Truncate and re-seed offering_faqs (bigint PK, not UUID)
        DB::table('offering_faqs')->truncate();
        foreach ($offerings as $o) {
            $minStr = number_format($o->min_investment ?? 100000);
            DB::table('offering_faqs')->insert([
                ['offering_id'=>$o->id,'question_en'=>'What is the minimum investment?','question_fr'=>'Quel est l\'investissement minimum ?','answer_en'=>'The minimum investment is '.$minStr.' XAF.','answer_fr'=>'L\'investissement minimum est de '.$minStr.' XAF.','sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
                ['offering_id'=>$o->id,'question_en'=>'When will I receive my shares or bond confirmation?','question_fr'=>'Quand vais-je recevoir mes actions ou ma confirmation d\'obligation ?','answer_en'=>'Allocations are confirmed within 10 business days of the offering closing date, subject to CMF approval.','answer_fr'=>'Les attributions sont confirmées dans les 10 jours ouvrables suivant la date de clôture de l\'offre.','sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
                ['offering_id'=>$o->id,'question_en'=>'Can I withdraw my pledge?','question_fr'=>'Puis-je retirer mon engagement ?','answer_en'=>'Pledges may be cancelled within 48 hours if payment has not yet been made. Once payment is confirmed, pledges are binding under CMF regulations.','answer_fr'=>'Les engagements peuvent être annulés dans les 48 heures si le paiement n\'a pas encore été effectué.','sort_order'=>3,'created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        $this->command->info('Jobs, blog, help, announcements, products, FAQs seeded.');
    }
}
