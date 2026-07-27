<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// 2026_07_04_000016 shipped a fabricated backup history and invented
// infrastructure facts (AH237-Server-01, Ubuntu 22.04 LTS, MySQL 8.0,
// /backups/gvna, 256.8 GB of 500). The admin screens presented them as live
// records. Databases already migrated still carry those rows, so remove them:
// a backup that never ran must not appear in the registry, and the platform
// cannot report a disk quota nobody measured.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('backup_records')->whereIn('filename', array_merge(
            ['backup_2025-05-08_14-15-22.zip'],
            collect(range(0, 11))->map(fn ($i) => 'backup_'
                . \Carbon\Carbon::create(2025, 5, 12)->subDays($i)->format('Y-m-d')
                . '_02-30-00.zip')->all()
        ))->delete();

        DB::table('backup_logs')->where('actor', 'Système')
            ->where('logged_at', '<', '2025-06-01')->delete();

        DB::table('platform_settings')->whereIn('key', [
            'storage_used_gb', 'storage_total_gb',
            'backup_server', 'backup_os', 'backup_db', 'backup_path', 'backup_retention',
        ])->delete();
    }

    public function down(): void
    {
        // Nothing to restore: the removed rows were fabrications.
    }
};
