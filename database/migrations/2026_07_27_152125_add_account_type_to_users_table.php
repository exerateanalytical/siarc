<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which kind of account someone signed up for.
 *
 * The platform asked this question in two places and accepted two different
 * answers: quick signup offered Buyer / Artisan, while the onboarding wizard
 * offered Individual Artisan / Cooperative / SME / Large Enterprise. Neither
 * answer was stored, so whichever the member picked was discarded and the shop
 * record later defaulted to `artisan` regardless of what they said.
 *
 * One vocabulary now, captured at signup and used to prefill the shop's
 * vendor_type when they create it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type', 30)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'account_type')) {
                $table->dropColumn('account_type');
            }
        });
    }
};
