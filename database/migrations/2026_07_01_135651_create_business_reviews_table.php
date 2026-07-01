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
        Schema::create('business_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('reviewer_id');
            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_verified_contact')->default(false); // reviewer had a real conversation with this business
            $table->enum('status', ['published', 'flagged', 'hidden'])->default('published');
            $table->timestamps();
            $table->unique(['reviewer_id', 'business_id']);
            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_reviews');
    }
};
