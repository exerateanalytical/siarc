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
        Schema::table('product_videos', function (Blueprint $table) {
            $table->enum('category', ['overview', 'production', 'harvest', 'packaging', 'inspection', 'tour', 'other'])
                ->default('overview')->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_videos', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
