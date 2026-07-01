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
            // Overview
            $table->string('sku')->nullable()->after('slug');
            $table->string('brand')->nullable()->after('sku');
            $table->string('scientific_name')->nullable()->after('brand');
            $table->string('local_names')->nullable()->after('scientific_name');
            $table->decimal('gps_lat', 10, 7)->nullable()->after('origin_region_id');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->string('batch_number')->nullable()->after('gps_lng');

            // Quality
            $table->enum('grade', ['a', 'b', 'c', 'premium'])->nullable()->after('is_certified');
            $table->text('quality_notes')->nullable()->after('grade');

            // Purchase limits
            $table->unsignedInteger('max_order')->nullable()->after('moq_unit');

            // Production
            $table->string('harvest_method')->nullable()->after('max_order');
            $table->timestamp('next_harvest_at')->nullable()->after('harvest_method');
            $table->unsignedInteger('daily_production')->nullable()->after('next_harvest_at');
            $table->unsignedInteger('monthly_production')->nullable()->after('daily_production');
            $table->unsignedInteger('annual_production')->nullable()->after('monthly_production');
            $table->string('production_unit', 20)->nullable()->after('annual_production');

            // Packaging & storage
            $table->string('packaging_type')->nullable()->after('production_unit');
            $table->unsignedInteger('shelf_life_days')->nullable()->after('packaging_type');
            $table->string('storage_conditions')->nullable()->after('shelf_life_days');

            // Logistics & payment
            $table->unsignedInteger('delivery_radius_km')->nullable()->after('storage_conditions');
            $table->unsignedInteger('lead_time_days')->nullable()->after('delivery_radius_km');
            $table->string('payment_terms')->nullable()->after('lead_time_days');

            // Live inventory freshness
            $table->timestamp('quantity_updated_at')->nullable()->after('quantity_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sku', 'brand', 'scientific_name', 'local_names',
                'gps_lat', 'gps_lng', 'batch_number',
                'grade', 'quality_notes',
                'max_order',
                'harvest_method', 'next_harvest_at',
                'daily_production', 'monthly_production', 'annual_production', 'production_unit',
                'packaging_type', 'shelf_life_days', 'storage_conditions',
                'delivery_radius_km', 'lead_time_days', 'payment_terms',
                'quantity_updated_at',
            ]);
        });
    }
};
