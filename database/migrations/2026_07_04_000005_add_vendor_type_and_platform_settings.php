<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Backend concepts that existed on the frontend only: the vendor-type filter
// on the product directory, and the landing hero statistics band.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('vendor_type', 20)->default('artisan')->after('verification_tier');
        });

        // Classify existing businesses from their names; everything else stays 'artisan'
        DB::table('businesses')->where('name_fr', 'like', '%Coopérative%')->update(['vendor_type' => 'cooperative']);
        DB::table('businesses')
            ->where(fn ($q) => $q
                ->where('name_fr', 'like', '%SARL%')
                ->orWhere('name_fr', 'like', '%Excellence%')
                ->orWhere('name_fr', 'like', '%Plantation%')
                ->orWhere('name_fr', 'like', '%ÉPICAM%'))
            ->update(['vendor_type' => 'entreprise']);

        // Key-value store for admin-editable display settings (landing hero stats)
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        $now = now();
        DB::table('platform_settings')->insert([
            ['key' => 'stat_communities', 'value' => '250+',    'created_at' => $now, 'updated_at' => $now],
            ['key' => 'stat_artisans',    'value' => '10 000+', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'stat_products',    'value' => '50 000+', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('businesses', fn (Blueprint $table) => $table->dropColumn('vendor_type'));
        Schema::dropIfExists('platform_settings');
    }
};
