<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase15TalentSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            [
                'headline' => 'Digital Marketing Manager',
                'summary'  => 'Creative marketing professional with 6 years driving growth for Cameroonian brands. Specialist in social media, SEO, and WhatsApp commerce. Grew one retailer\'s online revenue by 3x in 12 months.',
                'location' => 'Douala, Cameroon',
                'skills'   => ['Digital Marketing','SEO','Social Media','Brand Strategy','Google Ads','WhatsApp Business','Content Marketing','Analytics'],
                'languages'=> ['French (Native)','English (Fluent)'],
                'experience' => [
                    ['title'=>'Marketing Manager','company'=>'Carrefour Market','location'=>'Douala','start'=>'2021','end'=>null,'description'=>'Lead all digital channels and campaigns across Littoral region.'],
                    ['title'=>'Social Media Lead','company'=>'Orange Cameroun','location'=>'Douala','start'=>'2018','end'=>'2021','description'=>'Managed community of 500k+ followers and paid campaigns.'],
                ],
                'education' => [['degree'=>'BSc Marketing','institution'=>'University of Douala','year'=>'2017']],
                'certs'     => ['Google Ads Certified','Meta Blueprint'],
                'job_type'  => 'full_time',
            ],
            [
                'headline' => 'Chartered Accountant & Finance Analyst',
                'summary'  => 'Detail-oriented accountant with OHADA expertise and 8 years in audit and financial reporting. Skilled in tax optimisation, SAP, and IFRS. Open to senior finance roles.',
                'location' => 'Yaoundé, Cameroon',
                'skills'   => ['Accounting','Audit','Taxation','OHADA','IFRS','SAP','Excel','Financial Analysis'],
                'languages'=> ['French (Native)','English (Professional)'],
                'experience' => [
                    ['title'=>'Senior Accountant','company'=>'Afriland First Bank','location'=>'Yaoundé','start'=>'2019','end'=>null,'description'=>'Oversee financial reporting and statutory audits.'],
                    ['title'=>'Audit Associate','company'=>'PwC Cameroon','location'=>'Douala','start'=>'2016','end'=>'2019','description'=>'Conducted audits for banking and telecom clients.'],
                ],
                'education' => [['degree'=>'MSc Accounting & Finance','institution'=>'University of Yaoundé II','year'=>'2015']],
                'certs'     => ['DSCG','Certified Internal Auditor (CIA)'],
                'job_type'  => 'full_time',
            ],
            [
                'headline' => 'Product Designer (UX/UI)',
                'summary'  => 'Designer crafting human-centred digital products for African markets. 5 years across fintech and e-commerce. Lead designer on apps with 1M+ downloads.',
                'location' => 'Buea, Cameroon',
                'skills'   => ['Figma','UX Research','UI Design','Prototyping','Design Systems','User Testing','Wireframing','Accessibility'],
                'languages'=> ['English (Native)','French (Intermediate)'],
                'experience' => [
                    ['title'=>'Product Designer','company'=>'StartupHub Bonanjo','location'=>'Douala','start'=>'2020','end'=>null,'description'=>'Design lead for fintech and logistics products.'],
                    ['title'=>'UI Designer','company'=>'Freelance','location'=>'Remote','start'=>'2019','end'=>'2020','description'=>'Designed web and mobile experiences for startups.'],
                ],
                'education' => [['degree'=>'BSc Computer Science','institution'=>'University of Buea','year'=>'2018']],
                'certs'     => ['Google UX Design Certificate'],
                'job_type'  => 'full_time',
            ],
            [
                'headline' => 'Civil Engineer & Project Manager',
                'summary'  => 'Civil engineer with 7 years delivering infrastructure across Northern Cameroon. Expertise in structural design, site management, and procurement. PMP certified.',
                'location' => 'Garoua, Cameroon',
                'skills'   => ['Civil Engineering','AutoCAD','Project Management','Structural Design','Procurement','Site Supervision','Cost Estimation','Revit'],
                'languages'=> ['French (Native)','English (Professional)','Fulfulde (Native)'],
                'experience' => [
                    ['title'=>'Project Engineer','company'=>'CAMRAIL','location'=>'Garoua','start'=>'2019','end'=>null,'description'=>'Manage rail infrastructure projects in the North region.'],
                    ['title'=>'Site Engineer','company'=>'Razel-Bec','location'=>'Maroua','start'=>'2017','end'=>'2019','description'=>'Supervised road construction works.'],
                ],
                'education' => [['degree'=>'Diploma in Civil Engineering','institution'=>'ENSP Yaoundé','year'=>'2016']],
                'certs'     => ['PMP','OSHA Safety'],
                'job_type'  => 'full_time',
            ],
        ];

        $users = DB::table('users')->orderBy('created_at')->get(['id']);
        $i = 0; $created = 0;
        foreach ($users as $u) {
            if (DB::table('employee_profiles')->where('user_id', $u->id)->exists()) continue;
            if ($i >= count($candidates)) break;
            $c = $candidates[$i++];
            DB::table('employee_profiles')->insert([
                'id'            => Str::uuid()->toString(),
                'user_id'       => $u->id,
                'headline'      => $c['headline'],
                'summary'       => $c['summary'],
                'location'      => $c['location'],
                'skills'        => json_encode($c['skills']),
                'languages'     => json_encode($c['languages']),
                'experience'    => json_encode($c['experience']),
                'education'     => json_encode($c['education']),
                'certifications'=> json_encode($c['certs']),
                'open_to_work'  => 1,
                'job_type_preference' => $c['job_type'],
                'created_at'    => now(), 'updated_at' => now(),
            ]);
            $created++;
        }
        // ensure the sample CV user is also discoverable
        DB::table('employee_profiles')->where('open_to_work', 0)->limit(1)->update(['open_to_work' => 1]);

        $this->command->info("Phase 15 seeded: {$created} open-to-work candidate profiles.");
    }
}
