<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The Data Export Centre design (Data Export Centre.png) needs a real export
// registry: every row is a downloadable dataset export (CSV streamed from the
// live tables). sort_order pins the design's 8 rows to page 1 in design order.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dataset', 30);                       // artisans|produits|utilisateurs|transactions|kyc|rapports|medias|evenements
            $table->string('format', 8)->default('csv');         // csv|xlsx|pdf|zip
            $table->string('status', 20)->default('reussi');     // reussi|en_cours|echoue|planifie
            $table->unsignedBigInteger('records')->default(0);
            $table->boolean('counts_files')->default(false);     // "fichiers" instead of "enregistrements"
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_exports');
    }
};
