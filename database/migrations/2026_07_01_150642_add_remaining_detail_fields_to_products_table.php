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
            // Overview — species as its own field (distinct from category)
            $table->string('species')->nullable()->after('scientific_name');

            // Quality
            $table->enum('inspection_status', ['not_inspected', 'pending', 'passed', 'failed'])->default('not_inspected')->after('quality_notes');
            $table->unsignedTinyInteger('quality_score')->nullable()->after('inspection_status'); // 0-100, computed

            // Health status
            $table->date('veterinary_inspection_at')->nullable()->after('quality_score');
            $table->decimal('mortality_rate', 5, 2)->nullable()->after('veterinary_inspection_at'); // %

            // Packaging (structured)
            $table->string('package_sizes')->nullable()->after('packaging_type'); // e.g. "5kg, 10kg, 25kg"
            $table->boolean('is_custom_packaging')->default(false)->after('package_sizes');
            $table->boolean('is_ice_packed')->default(false)->after('is_custom_packaging');
            $table->boolean('is_vacuum_packed')->default(false)->after('is_ice_packed');
            $table->boolean('is_live_transport')->default(false)->after('is_vacuum_packed');
            $table->boolean('is_bulk_packaging')->default(false)->after('is_live_transport');

            // Logistics
            $table->boolean('pickup_available')->default(true)->after('lead_time_days');
            $table->boolean('delivery_available')->default(true)->after('pickup_available');
            $table->boolean('is_cold_chain')->default(false)->after('delivery_available');
            $table->string('shipping_company')->nullable()->after('is_cold_chain');
            $table->string('warehouse_location')->nullable()->after('shipping_company');
            $table->boolean('ready_for_shipment')->default(true)->after('warehouse_location');
            $table->boolean('container_loading')->default(false)->after('ready_for_shipment');
            $table->string('shipping_methods')->nullable()->after('container_loading');

            // Payment (structured)
            $table->string('accepted_currencies')->default('XAF')->after('payment_terms');
            $table->string('payment_methods')->nullable()->after('accepted_currencies');
            $table->boolean('deposit_required')->default(false)->after('payment_methods');
            $table->boolean('trade_finance_support')->default(false)->after('deposit_required');

            // Traceability
            $table->string('pond_number')->nullable()->after('batch_number');
            $table->date('stocking_date')->nullable()->after('pond_number');
            $table->text('feed_history')->nullable()->after('stocking_date');
            $table->text('treatments_administered')->nullable()->after('feed_history');
            $table->date('packaging_date')->nullable()->after('treatments_administered');
            $table->string('delivery_route')->nullable()->after('packaging_date');

            // Sustainability
            $table->string('water_usage')->nullable()->after('delivery_route');
            $table->string('energy_source')->nullable()->after('water_usage');
            $table->string('carbon_footprint')->nullable()->after('energy_source');
            $table->string('waste_management')->nullable()->after('carbon_footprint');
            $table->string('environmental_certifications')->nullable()->after('waste_management');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'species',
                'inspection_status', 'quality_score',
                'veterinary_inspection_at', 'mortality_rate',
                'package_sizes', 'is_custom_packaging', 'is_ice_packed', 'is_vacuum_packed', 'is_live_transport', 'is_bulk_packaging',
                'pickup_available', 'delivery_available', 'is_cold_chain', 'shipping_company', 'warehouse_location', 'ready_for_shipment', 'container_loading', 'shipping_methods',
                'accepted_currencies', 'payment_methods', 'deposit_required', 'trade_finance_support',
                'pond_number', 'stocking_date', 'feed_history', 'treatments_administered', 'packaging_date', 'delivery_route',
                'water_usage', 'energy_source', 'carbon_footprint', 'waste_management', 'environmental_certifications',
            ]);
        });
    }
};
