<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the two digital-identity values the certificate design asks for that
 * the platform can actually compute and check.
 *
 * The design also asks for an "AI visual fingerprint" and a "watermark
 * reference". Neither exists — there is no model and no watermarking pipeline —
 * so no column is added for them and the certificate does not print them.
 *
 * These two are different. A perceptual hash of the cover photograph is a real
 * number derived from real pixels, and it is what lets verification say "the
 * photograph on this listing is no longer the one that was certified" — a
 * substitution the SHA-256 content hash alone would catch only if the file path
 * changed too. The signature is an HMAC over the certified facts keyed on the
 * application key, so a certificate printed from a scraped copy of the page
 * cannot be re-issued by anyone who does not hold that key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_certificates', function (Blueprint $table) {
            // 64-bit dHash of the cover photograph, hex. Null when the product
            // has no image, or when the file could not be read at issue time.
            $table->string('image_phash', 16)->nullable()->after('content_hash');
            // HMAC-SHA256 over the certified facts. Truncated for printing, but
            // stored in full so verification compares the whole value.
            $table->string('signature', 64)->nullable()->after('image_phash');
        });
    }

    public function down(): void
    {
        Schema::table('product_certificates', function (Blueprint $table) {
            $table->dropColumn(['image_phash', 'signature']);
        });
    }
};
