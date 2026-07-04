<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The Subscriptions design (Subscriptions.png) shows 8 specific subscriber rows
// in a fixed order that no started_at sort can produce (row 5 is dated 2024).
// A nullable sort_order pins the seeded design rows to page 1 in design order;
// all other subscriptions keep sort_order NULL and sort by started_at after them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->nullable()->after('next_payment_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('business_subscriptions', fn (Blueprint $table) => $table->dropColumn('sort_order'));
    }
};
