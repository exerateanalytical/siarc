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
        Schema::create('product_harvest_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('harvest_date');
            $table->unsignedInteger('expected_quantity')->nullable();
            $table->string('unit', 20)->nullable();
            $table->enum('status', ['upcoming', 'completed', 'cancelled'])->default('upcoming');
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'harvest_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_harvest_dates');
    }
};
