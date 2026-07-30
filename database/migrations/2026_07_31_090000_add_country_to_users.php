<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The country a member states when they sign up.
 *
 * Nullable on purpose. Every account that exists today is a SIARC import from a
 * Cameroonian competition dataset, so those are backfilled to Cameroon as a
 * statement of fact — but a future row whose country is genuinely unknown must
 * be able to say so rather than silently claim Cameroon.
 *
 * `assigned_region_id` on this table is left alone: it is an admin assignment
 * for staff coverage and means something different.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('phone')
                ->constrained('countries')->nullOnDelete();
        });

        $cameroon = DB::table('countries')->where('code', 'CM')->value('id');

        if ($cameroon) {
            DB::table('users')->whereNull('country_id')->update(['country_id' => $cameroon]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
