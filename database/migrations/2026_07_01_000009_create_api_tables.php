<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_consumers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('company')->nullable();
            $table->string('country', 2)->nullable();
            $table->text('purpose')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained('api_consumers')->cascadeOnDelete();
            $table->string('key_hash', 64)->unique(); // SHA-256 of the raw key
            $table->string('key_prefix', 8); // first 8 chars shown in UI
            $table->string('name'); // label e.g. "Production", "Staging"
            $table->json('scopes')->nullable(); // ['businesses:read','products:read']
            $table->unsignedSmallInteger('rate_limit_per_minute')->default(600);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_id')->constrained('api_keys')->cascadeOnDelete();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('response_time_ms');
            $table->string('ip', 45)->nullable();
            $table->timestamp('called_at')->useCurrent();
            $table->index(['key_id', 'called_at']);
        });

        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumer_id')->constrained('api_consumers')->cascadeOnDelete();
            $table->string('url');
            $table->json('events'); // ['business.published','product.published']
            $table->string('secret_hash', 64); // HMAC secret, hashed
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_delivery_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('api_consumers');
    }
};
