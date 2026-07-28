<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the certificate log tamper-evident and gives every certificate a real
 * authority signature alongside the HMAC it already carried.
 *
 * The HMAC stays: it is cheap, and it still detects our own accidental
 * corruption. What it could never do is let an outsider check a document
 * without trusting us, which is what the Ed25519 signature and the published
 * public key are for. Both are stored so an old certificate keeps verifying.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_events', function (Blueprint $table) {
            $table->char('prev_hash', 64)->nullable()->after('note');
            $table->char('entry_hash', 64)->nullable()->after('prev_hash');
            $table->index('entry_hash');
        });

        // Ed25519 detached signature (base64url, 86 chars) plus the key id it
        // was made with, so a rotated key does not orphan old certificates.
        foreach (['product_certificates', 'ownership_transfers', 'artisan_verifications'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->string('ca_signature', 128)->nullable()->after('signature');
                $table->string('ca_kid', 64)->nullable()->after('ca_signature');
            });
        }
    }

    public function down(): void
    {
        Schema::table('certificate_events', function (Blueprint $table) {
            $table->dropIndex(['entry_hash']);
            $table->dropColumn(['prev_hash', 'entry_hash']);
        });

        foreach (['product_certificates', 'ownership_transfers', 'artisan_verifications'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn(['ca_signature', 'ca_kid']);
            });
        }
    }
};
