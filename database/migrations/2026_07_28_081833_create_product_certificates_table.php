<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificates of Authenticity for registered products.
 *
 * A certificate is its own record rather than a few columns on `products`,
 * because it has a life of its own: it is issued once, it can be revoked, and a
 * material change to the product should supersede it with a new version rather
 * than silently rewrite what a buyer already downloaded. A certificate that
 * changes underneath the person holding it is not a certificate.
 *
 * The columns here are deliberately limited to what the platform can actually
 * stand behind. The specification this was built from also asks for AI visual
 * fingerprints, perceptual hashes, invisible watermark references, blockchain
 * provenance and cryptographic digital signatures — none of which exist. They
 * are not stubbed out here: an empty "Digital Signature" field on a document
 * headed CERTIFICATE OF AUTHENTICITY is worse than no field, because it reads
 * as a guarantee nobody made.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Human-facing reference, e.g. AHC-COA-2026-000000123.
            $table->string('certificate_no', 40)->unique();
            $table->unsignedSmallInteger('version')->default(1);

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();

            // A short code the holder reads out. The certificate number alone is
            // guessable from a sequence, so verification asks for both.
            $table->string('verification_pin', 12);

            // SHA-256 over the certified facts at issue time. Lets anyone detect
            // that the product record has since changed, without pretending to
            // be a cryptographic signature — there is no key behind it, and the
            // certificate page says so plainly.
            $table->string('content_hash', 64);

            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason', 300)->nullable();

            $table->unsignedInteger('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();

            $table->timestamps();

            // One live certificate per product; superseded ones keep their row.
            $table->index(['product_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_certificates');
    }
};
