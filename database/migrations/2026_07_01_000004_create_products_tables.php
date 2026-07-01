<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique()->nullable();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('origin_region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->longText('description_fr')->nullable();
            $table->longText('description_en')->nullable();
            $table->unsignedInteger('quantity_available')->nullable();
            $table->string('quantity_unit', 20)->nullable(); // kg, ton, unit, litre
            $table->unsignedInteger('moq')->nullable(); // minimum order quantity
            $table->string('moq_unit', 20)->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_export_ready')->default(false);
            $table->boolean('is_custom_order')->default(false);
            $table->boolean('is_wholesale')->default(false);
            $table->boolean('is_retail')->default(true);
            $table->boolean('is_organic')->default(false);
            $table->boolean('is_certified')->default(false);
            $table->enum('status', ['draft', 'published', 'rejected'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['business_id', 'status']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption_fr')->nullable();
            $table->string('caption_en')->nullable();
            $table->boolean('is_cover')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['spec_sheet', 'lab_report', 'certificate', 'catalogue', 'other']);
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->string('file_path');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_template_id')->constrained()->cascadeOnDelete();
            $table->text('value_fr')->nullable();
            $table->text('value_en')->nullable();
            $table->decimal('numeric_value', 12, 4)->nullable(); // for filterable numbers
            $table->string('unit', 20)->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_template_id']);
        });

        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->enum('type', ['youtube', 'vimeo', 'upload']);
            $table->string('caption_fr')->nullable();
            $table->string('caption_en')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('saved_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('viewer_ip', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['product_id', 'viewed_at']);
        });

        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->uuid('reporter_id')->nullable();
            $table->foreign('reporter_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('reason', ['spam', 'misleading', 'inappropriate', 'duplicate', 'other']);
            $table->text('details')->nullable();
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->timestamps();
        });

        // Deferred FK: business_contact_submissions.product_id → products.id
        Schema::table('business_contact_submissions', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('business_contact_submissions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::dropIfExists('product_reports');
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('saved_products');
        Schema::dropIfExists('product_videos');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_documents');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
