<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Room to record what was actually measured about the certified photograph.
 *
 * The existing image_phash column holds a single difference hash. That one
 * hash answers "has the picture changed" but nothing more, and it can be
 * fooled: a difference hash is blind to anything the resampling to 9×8 throws
 * away. Storing the DCT and block hashes alongside it lets verification
 * require agreement before it accuses anyone of a swap.
 *
 * Every column is nullable because a certificate issued for a product whose
 * photograph cannot be read must still be issued. An absent fingerprint is a
 * fact worth recording; a placeholder would be a lie on a legal document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_certificates', function (Blueprint $table) {
            $table->char('fp_dct', 16)->nullable()->after('image_phash');
            $table->char('fp_block', 16)->nullable()->after('fp_dct');
            // The short human-quotable form, e.g. AHF-xxxx-xxxx-xxxx.
            $table->string('fp_id', 32)->nullable()->after('fp_block');
            // The reference embedded in delivered copies of the image.
            $table->string('watermark_ref', 40)->nullable()->after('fp_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_certificates', function (Blueprint $table) {
            $table->dropColumn(['fp_dct', 'fp_block', 'fp_id', 'watermark_ref']);
        });
    }
};
