<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('group', 50)->default('general')->after('type');
            // Secret values (API credentials) are stored Crypt-encrypted and
            // rendered masked in the admin UI.
            $table->boolean('is_secret')->default(false)->after('group');
        });

        $groups = [
            'platform_name_fr'          => 'general',
            'platform_name_en'          => 'general',
            'contact_email'             => 'general',
            'max_products_per_business' => 'limits',
            'max_gallery_images'        => 'limits',
            'featured_businesses_count' => 'limits',
            'siac_event_date'           => 'siac',
            'siac_event_location'       => 'siac',
        ];
        foreach ($groups as $key => $group) {
            DB::table('system_settings')->where('key', $key)->update(['group' => $group]);
        }
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'is_secret']);
        });
    }
};
