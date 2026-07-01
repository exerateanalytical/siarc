<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase12SeederAll extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')->whereNull('deleted_at')->limit(8)->get();
        if ($companies->isEmpty()) { $this->command->warn('No companies found.'); return; }
        $userId = DB::table('users')->value('id');

        // ── COMPANY BRANCHES ──────────────────────────────────────────────────
        $branchTemplates = [
            ['headquarters','Head Office','Douala','Littoral',1],
            ['regional_office','Yaoundé Regional Office','Yaoundé','Centre',0],
            ['branch','Bafoussam Branch','Bafoussam','Ouest',0],
            ['warehouse','Douala Port Warehouse','Douala','Littoral',0],
            ['retail_outlet','Garoua Outlet','Garoua','Nord',0],
            ['service_center','Bamenda Service Center','Bamenda','Nord-Ouest',0],
        ];
        $created = 0;
        foreach ($companies as $i => $co) {
            if (DB::table('company_branches')->where('company_id',$co->id)->exists()) continue;
            // each company gets HQ + 2-4 more branches
            $count = 3 + ($i % 3);
            for ($b = 0; $b < $count && $b < count($branchTemplates); $b++) {
                $t = $branchTemplates[$b];
                DB::table('company_branches')->insert([
                    'id'           => (string)Str::uuid(),
                    'company_id'   => $co->id,
                    'name'         => $t[1],
                    'branch_type'  => $t[0],
                    'address'      => 'Quartier '.$t[2].', '.$t[3],
                    'city'         => $t[2],
                    'region'       => $t[3],
                    'country'      => 'Cameroon',
                    'phone'        => '+237 6'.rand(70000000,99999999),
                    'email'        => strtolower($t[2]).'@example.cm',
                    'manager_name' => ['Paul Ngassa','Marie Etonde','Eric Fotso','Sandra Mballa','Joseph Biya'][$b % 5],
                    'is_primary'   => $t[4],
                    'staff_count'  => rand(5,120),
                    'status'       => 'active',
                    'created_at'   => now(),'updated_at'=>now(),
                ]);
                $created++;
            }
        }

        // ── SALARY REPORTS ────────────────────────────────────────────────────
        // [job_title, sector, experience_level, monthly_min, monthly_max]
        $roles = [
            ['Software Developer','ict','mid',350000,750000],
            ['Senior Software Engineer','ict','senior',700000,1500000],
            ['Accountant','finance','mid',250000,500000],
            ['Marketing Manager','retail','senior',450000,900000],
            ['Sales Representative','retail','junior',150000,350000],
            ['Civil Engineer','construction','mid',400000,800000],
            ['Agronomist','agriculture','mid',300000,600000],
            ['Project Manager','ict','senior',600000,1200000],
            ['Human Resources Officer','general','mid',280000,550000],
            ['Data Analyst','ict','mid',400000,800000],
            ['Bank Teller','finance','entry','180000','320000'],
            ['Operations Director','general','executive',1200000,2500000],
            ['Logistics Coordinator','transport','mid',300000,600000],
            ['Nurse','health','mid',180000,400000],
            ['Customer Support Agent','ict','entry',120000,280000],
        ];
        $cities = ['Douala','Yaoundé','Bafoussam','Garoua','Bamenda','Buea'];
        $expLevels = ['entry','junior','mid','senior','lead','executive'];
        $created2 = 0;
        if (DB::table('salary_reports')->count() === 0) {
            foreach ($roles as $r) {
                [$title,$sector,$lvl,$min,$max] = $r;
                $slug = Str::slug($title);
                // generate 4-9 reports per role with spread within range
                $n = rand(4,9);
                for ($k = 0; $k < $n; $k++) {
                    $monthly = rand((int)$min, (int)$max);
                    $period = rand(0,4) === 0 ? 'annual' : 'monthly';
                    $amount = $period === 'annual' ? $monthly * 12 : $monthly;
                    $annual = $monthly * 12;
                    $co = $companies[rand(0, $companies->count()-1)];
                    DB::table('salary_reports')->insert([
                        'id'              => (string)Str::uuid(),
                        'company_id'      => rand(0,1) ? $co->id : null,
                        'user_id'         => $userId,
                        'job_title'       => $title,
                        'job_slug'        => $slug,
                        'sector'          => $sector,
                        'employment_type' => 'full_time',
                        'experience_level'=> $expLevels[array_rand($expLevels)],
                        'city'            => $cities[array_rand($cities)],
                        'salary_amount'   => $amount,
                        'period'          => $period,
                        'annual_amount'   => $annual,
                        'currency'        => 'XAF',
                        'bonus_annual'    => rand(0,1) ? rand(100000,2000000) : null,
                        'years_experience'=> rand(1,15),
                        'source'          => 'employee',
                        'is_anonymous'    => 1,
                        'status'          => 'published',
                        'created_at'      => now()->subDays(rand(1,120)),'updated_at'=>now(),
                    ]);
                    $created2++;
                }
            }
        }

        $this->command->info("Phase 12 seeded: {$created} branches, {$created2} salary reports across ".count($roles).' roles.');
    }
}
