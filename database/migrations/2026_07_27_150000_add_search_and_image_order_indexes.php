<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two hot paths gained ordering work and had no index behind it:
     *
     *  - product_images is read once per product card to resolve the primary
     *    image, now sorted by (is_cover, sort_order); without this the engine
     *    filesorts the whole child set on every row of a 24-item grid.
     *  - name_fr/name_en carry the relevance CASE. A leading-wildcard LIKE
     *    cannot use an index, but the exact and prefix tiers can, and the
     *    directory's `sort=name` ordering uses it outright.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'is_cover', 'sort_order'], 'product_images_product_cover_order_index');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->index('name_fr', 'businesses_name_fr_index');
            $table->index('name_en', 'businesses_name_en_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('name_fr', 'products_name_fr_index');
            $table->index('name_en', 'products_name_en_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_cover_order_index');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex('businesses_name_fr_index');
            $table->dropIndex('businesses_name_en_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_name_fr_index');
            $table->dropIndex('products_name_en_index');
        });
    }
};
