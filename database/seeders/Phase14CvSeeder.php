<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase14CvSeeder extends Seeder
{
    public function run(): void
    {
        $user = DB::table('users')->first();
        if (!$user) { $this->command->warn('No users.'); return; }

        if (!DB::table('employee_profiles')->where('user_id', $user->id)->exists()) {
            DB::table('employee_profiles')->insert([
                'id'            => Str::uuid()->toString(),
                'user_id'       => $user->id,
                'headline'      => 'Senior Software Engineer & Technical Lead',
                'summary'       => 'Results-driven software engineer with 8+ years building scalable web platforms and leading cross-functional teams across Central Africa. Specialised in PHP/Laravel, distributed systems, and fintech integrations (MTN MoMo, Orange Money). Passionate about mentoring and shipping reliable products.',
                'location'      => 'Douala, Cameroon',
                'phone'         => '+237 677 123 456',
                'linkedin_url'  => 'https://www.linkedin.com/in/sample',
                'github_url'    => 'https://github.com/sample',
                'portfolio_url' => 'https://portfolio.example.cm',
                'skills'        => json_encode(['PHP','Laravel','JavaScript','Vue.js','MySQL','Redis','Docker','AWS','REST APIs','Git','CI/CD','Agile']),
                'languages'     => json_encode(['English (Fluent)','French (Native)','German (Basic)']),
                'experience'    => json_encode([
                    ['title'=>'Senior Software Engineer','company'=>'MTN Cameroun','location'=>'Douala','start'=>'2021','end'=>null,'description'=>'Lead a team of 6 engineers building the mobile money API platform serving 8M+ users. Reduced transaction latency by 40% and introduced automated testing across services.'],
                    ['title'=>'Full-Stack Developer','company'=>'Afriland First Bank','location'=>'Yaounde','start'=>'2018','end'=>'2021','description'=>'Built internal lending and KYC tools used by 200+ branch staff. Integrated core banking APIs and delivered a customer onboarding portal.'],
                    ['title'=>'Web Developer','company'=>'StartupHub Bonanjo','location'=>'Douala','start'=>'2016','end'=>'2018','description'=>'Delivered e-commerce and booking platforms for early-stage Cameroonian startups.'],
                ]),
                'education'     => json_encode([
                    ['degree'=>'MSc Computer Science','institution'=>'University of Buea','year'=>'2016'],
                    ['degree'=>'BSc Computer Science','institution'=>'University of Yaounde I','year'=>'2014'],
                ]),
                'certifications'=> json_encode(['AWS Certified Solutions Architect','Laravel Certified Developer','Scrum Master (PSM I)']),
                'open_to_work'  => 1,
                'created_at'    => now(),'updated_at'=>now(),
            ]);
        }

        if (!DB::table('user_cvs')->where('user_id', $user->id)->exists()) {
            DB::table('user_cvs')->insert([
                'id'            => Str::uuid()->toString(),
                'user_id'       => $user->id,
                'title'         => 'My Professional CV',
                'template'      => 'classic',
                'color_scheme'  => 'green',
                'language'      => 'en',
                'is_public'     => 1,
                'public_slug'   => 'sample-professional-cv',
                'created_at'    => now(),'updated_at'=>now(),
            ]);
        }

        if (!DB::table('cover_letters')->where('public_slug', 'sample-cover-letter')->exists()) {
            $body = "I am writing to express my strong interest in the Senior Software Engineer position at MTN Cameroun. As a Senior Software Engineer & Technical Lead, I am confident that my experience and skills make me a strong candidate for this opportunity.\n\n"
                . "In my previous roles I have developed a solid track record of delivering results, working effectively within teams, and meeting demanding objectives. I am particularly drawn to MTN Cameroun and the opportunity to contribute to your continued success.\n\n"
                . "I would welcome the opportunity to discuss my application in more detail. Thank you for your time and consideration; I look forward to hearing from you.";
            DB::table('cover_letters')->insert([
                'id'             => Str::uuid()->toString(),
                'user_id'        => $user->id,
                'title'          => 'Application to MTN',
                'recipient_name' => 'Hiring Manager',
                'company_name'   => 'MTN Cameroun',
                'job_title'      => 'Senior Software Engineer',
                'body'           => $body,
                'tone'           => 'formal',
                'template'       => 'modern',
                'accent_color'   => '#0056b3',
                'is_public'      => 1,
                'public_slug'    => 'sample-cover-letter',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
        }

        $this->command->info('Phase 14: seeded sample profile, public CV, and cover letter (/cover-letter/sample-cover-letter/view).');
    }
}
