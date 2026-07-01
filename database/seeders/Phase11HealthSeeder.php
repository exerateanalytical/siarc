<?php
namespace Database\Seeders;

use App\Support\HealthScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase11HealthSeeder extends Seeder
{
    public function run(): void
    {
        $companies = DB::table('companies')->whereNull('deleted_at')->pluck('id');
        if ($companies->isEmpty()) { $this->command->warn('No companies found.'); return; }
        $n = 0;
        foreach ($companies as $cid) {
            HealthScore::store($cid);
            $n++;
        }
        $this->command->info("Phase 11: computed collaboration health scores for {$n} companies.");
    }
}
