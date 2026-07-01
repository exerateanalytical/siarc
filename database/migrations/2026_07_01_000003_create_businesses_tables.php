<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->string('tagline_fr', 160)->nullable();
            $table->string('tagline_en', 160)->nullable();
            $table->longText('description_fr')->nullable();
            $table->longText('description_en')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address_fr')->nullable();
            $table->string('address_en')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->unsignedSmallInteger('year_established')->nullable();
            $table->unsignedSmallInteger('employee_count')->nullable();
            $table->enum('ownership_type', ['private', 'women_owned', 'youth_owned', 'cooperative', 'government'])->default('private');
            $table->json('export_countries')->nullable(); // array of ISO country codes
            $table->json('languages_spoken')->nullable(); // ['fr','en','fulfulde']
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->enum('verification_tier', ['unverified', 'basic', 'verified', 'certified'])->default('unverified');
            $table->enum('status', ['draft', 'published', 'suspended', 'rejected'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedTinyInteger('response_time_hours')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'verification_tier']);
            $table->index(['industry_id', 'region_id']);
        });

        Schema::create('business_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['linkedin', 'facebook', 'instagram', 'twitter', 'youtube', 'tiktok']);
            $table->string('url');
            $table->timestamps();
            $table->unique(['business_id', 'platform']);
        });

        Schema::create('business_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('file_path');
            $table->string('caption_fr')->nullable();
            $table->string('caption_en')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('business_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['registration', 'tax_certificate', 'license', 'award', 'certification', 'other']);
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->string('file_path');
            $table->string('issued_by')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('business_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('certificate_file')->nullable();
            $table->enum('status', ['pending', 'verified', 'expired'])->default('pending');
            $table->timestamps();
            $table->unique(['business_id', 'certification_id']);
        });

        Schema::create('business_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->string('issuer')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->timestamps();
        });

        Schema::create('business_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('tag', 50);
            $table->timestamps();
            $table->unique(['business_id', 'tag']);
        });

        Schema::create('business_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('viewer_ip', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['business_id', 'viewed_at']);
        });

        Schema::create('saved_businesses', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'business_id']);
        });

        Schema::create('business_contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable(); // FK added in products migration
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('company')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'closed'])->default('new');
            $table->timestamps();
        });

        Schema::create('verification_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->enum('tier_requested', ['basic', 'verified', 'certified']);
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->uuid('reviewer_id')->nullable();
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('verification_applications')->cascadeOnDelete();
            $table->enum('type', ['rccm', 'niu', 'anor', 'cnps', 'cmf', 'id_director', 'financials', 'product_cert', 'other']);
            $table->string('file_path');
            $table->string('original_name');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_documents');
        Schema::dropIfExists('verification_applications');
        Schema::dropIfExists('business_contact_submissions');
        Schema::dropIfExists('saved_businesses');
        Schema::dropIfExists('business_views');
        Schema::dropIfExists('business_tags');
        Schema::dropIfExists('business_awards');
        Schema::dropIfExists('business_certifications');
        Schema::dropIfExists('business_documents');
        Schema::dropIfExists('business_gallery');
        Schema::dropIfExists('business_social_links');
        Schema::dropIfExists('businesses');
    }
};
