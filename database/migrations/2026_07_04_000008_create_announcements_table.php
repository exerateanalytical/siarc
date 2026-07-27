<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * News & Announcements ("Actualités & Annonces") — backing table for the admin
 * page replicated from "gestion d'actualites et annonces.png". The platform had
 * no announcements concept before this migration; the articles shown in the
 * design are seeded inside up() so the admin view never hardcodes rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->text('excerpt_fr')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('body_fr')->nullable();
            $table->text('body_en')->nullable();
            $table->string('category')->nullable();
            // Design's "Type" column: Actualité / Article / Annonce
            $table->string('type')->nullable();
            $table->string('status')->default('published'); // published | draft | scheduled
            $table->timestamp('published_at')->nullable();
            $table->string('author_name')->nullable();
            $table->string('cover_image')->nullable();
            // Design's "Vues" column
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        // ---- Seed: the exact publications shown in the design (top to bottom) ----
        $now = now();
        // The eight articles seeded here were fabricated — announcements of a
        // National Craft Museum and an International Craft Festival that do not
        // exist, bylined to invented authors. A platform whose own legal copy
        // disclaims government affiliation cannot announce national
        // institutions. News is written by an administrator.
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
