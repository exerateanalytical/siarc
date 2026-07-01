<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('price_type', ['retail', 'wholesale', 'negotiable', 'contact'])->default('contact')->after('moq_unit');
            $table->decimal('price_amount', 12, 2)->nullable()->after('price_type');
            $table->string('price_currency', 3)->default('XAF')->after('price_amount');
            $table->string('price_unit', 20)->nullable()->after('price_currency'); // per kg, per unit, per ton...
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'price_amount', 'price_currency', 'price_unit']);
        });
    }
};
