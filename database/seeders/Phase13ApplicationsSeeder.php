<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase13ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('job_applications')->count() > 0) {
            $this->command->info('Applications already exist — skipping.');
            return;
        }
        $users = DB::table('users')->pluck('id')->all();
        if (count($users) < 1) { $this->command->warn('No users.'); return; }
        $jobs = DB::table('job_postings')->whereNull('deleted_at')->where('status','open')
            ->limit(12)->get(['id','company_id','title_en']);
        if ($jobs->isEmpty()) { $this->command->warn('No open jobs.'); return; }

        $statuses = ['submitted','submitted','submitted','shortlisted','shortlisted','interview','offered','rejected'];
        $letters = [
            'I am excited to apply for this position. With several years of relevant experience in the Cameroonian market, I believe I can contribute meaningfully to your team and help drive results from day one.',
            'Having followed your company for years, I would be honoured to join. My background aligns closely with the requirements, and I bring a strong track record of delivery, collaboration, and measurable impact.',
            'Please consider my application. I am a motivated professional with hands-on experience, fluent in both English and French, and eager to grow with a leading organisation in Cameroon.',
            'I am writing to express my strong interest in this role. I have led similar initiatives, manage stakeholders well, and consistently exceed targets. I would welcome the opportunity to discuss how I can add value.',
        ];
        $created = 0;
        foreach ($jobs as $ji => $job) {
            // owners of this job's company should not apply to it
            $owners = DB::table('company_users')->where('company_id',$job->company_id)
                ->where('is_active',1)->pluck('user_id')->all();
            $applicants = array_values(array_diff($users, $owners));
            if (empty($applicants)) $applicants = $users;
            // 1-3 applicants per job
            $n = min(count($applicants), 1 + ($ji % 3));
            shuffle($applicants);
            for ($k = 0; $k < $n; $k++) {
                $uid = $applicants[$k];
                if (DB::table('job_applications')->where('job_id',$job->id)->where('user_id',$uid)->exists()) continue;
                DB::table('job_applications')->insert([
                    'id'           => Str::uuid()->toString(),
                    'job_id'       => $job->id,
                    'user_id'      => $uid,
                    'cover_letter' => $letters[array_rand($letters)],
                    'status'       => $statuses[array_rand($statuses)],
                    'created_at'   => now()->subDays(rand(1,30)),
                    'updated_at'   => now(),
                ]);
                $created++;
            }
        }
        $this->command->info("Phase 13 seeded: {$created} job applications across {$jobs->count()} jobs.");
    }
}
