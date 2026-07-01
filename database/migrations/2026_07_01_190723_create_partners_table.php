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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->enum('tier', ['institutional', 'platinum', 'gold', 'silver', 'partner'])->default('partner');
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
