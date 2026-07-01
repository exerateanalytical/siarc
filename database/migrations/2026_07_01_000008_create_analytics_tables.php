<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->json('filters_applied')->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->timestamp('searched_at')->useCurrent();
            $table->index(['query', 'searched_at']);
        });

        Schema::create('platform_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // page_view, button_click, etc.
            $table->string('entity_type')->nullable(); // business, product
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['event_type', 'occurred_at']);
        });

        Schema::create('popular_searches_cache', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->unsignedInteger('count')->default(0);
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent();
            $table->unique('query');
        });

        Schema::create('admin_dashboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->json('metrics'); // total businesses, products, users, messages, searches
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_dashboard_snapshots');
        Schema::dropIfExists('popular_searches_cache');
        Schema::dropIfExists('platform_events');
        Schema::dropIfExists('search_queries');
    }
};
