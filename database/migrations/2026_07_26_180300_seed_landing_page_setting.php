<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Site root can show either the marketing home or the artisan directory,
// configurable via Paramètres Généraux (admin.settings.general). Defaulting
// to the directory for launch.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'landing_page'],
            ['value' => 'directory', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'landing_page')->delete();
    }
};
