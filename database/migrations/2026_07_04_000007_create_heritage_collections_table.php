<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// "Collections Héritage" admin page (design: "collection heritage admin panel.png").
// The platform had no heritage-collections concept: this migration creates the table,
// a pivot to real products, and seeds the 8 collections shown in the design verbatim.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heritage_collections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('published'); // published | in_review | draft
            $table->integer('sort_order')->default(0);
            // Display attributes shown on the admin design (region/category/visibility/traffic)
            $table->string('region_fr', 40)->nullable();
            $table->string('region_en', 40)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('category_fr', 60)->nullable();
            $table->string('category_en', 60)->nullable();
            $table->string('visibility', 20)->default('public'); // public | private
            $table->unsignedInteger('artisans_count')->default(0);
            $table->unsignedInteger('visits_count')->default(0);
            $table->timestamps();
        });

        Schema::create('heritage_collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('heritage_collections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['collection_id', 'product_id']);
        });

        // ------------------------------------------------------------------
        // Seed: the 8 collections of the design (names/descriptions verbatim).
        // [slug, name_fr, name_en, desc_fr, desc_en, region_fr, region_en, city,
        //  cat_fr, cat_en, status, visibility, artisans, visits, created]
        // ------------------------------------------------------------------
        // The eight collections seeded here carried hardcoded artisan counts
        // and visit figures (up to 38,560 visits) while the product pivot they
        // rely on was empty, so each one opened to nothing. Collections are
        // built by an administrator through admin.collections.create.
    }

    public function down(): void
    {
        Schema::dropIfExists('heritage_collection_product');
        Schema::dropIfExists('heritage_collections');
    }
};
