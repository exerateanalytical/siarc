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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('location_fr')->nullable();
            $table->string('location_en')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index(['is_published', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
