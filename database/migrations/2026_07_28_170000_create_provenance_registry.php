<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The register behind the certificates.
 *
 * Three of the four documents the platform issues — product registration,
 * ownership transfer, artisan verification — are records of something, and a
 * certificate whose record does not exist is only a picture of one. Ownership
 * in particular had no representation at all: no owner rows, no second party,
 * no transfer. That is what this creates.
 *
 * Three permanent identifiers are introduced, each deliberately outliving the
 * things that reference it:
 *
 *  - PRN, the product's registry number. The product's marketplace id can be
 *    reissued or re-slugged; the registry entry never is.
 *  - OLN, the ownership ledger number. Every transfer certificate for a product
 *    quotes the same OLN, so a chain can be reconstructed from any single
 *    certificate in it without holding the others.
 *  - GAN, the artisan's number. It survives a renamed business, a changed
 *    email, a claimed profile.
 *
 * Country segments use ISO 3166-1 alpha-2 and timestamps are stored UTC, so the
 * numbers stay meaningful if the platform ever operates outside Cameroon.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // AH237-GAN-CM-0000000458
            $table->string('gan', 32)->nullable()->unique()->after('siarc_code');
        });

        Schema::table('products', function (Blueprint $table) {
            // AH237-PRN-CM-2026-000000004587
            $table->string('prn', 40)->nullable()->unique()->after('uuid');
            // AH237-OLN-0000004589
            $table->string('oln', 32)->nullable()->unique()->after('prn');
            $table->timestamp('registered_at')->nullable()->after('oln');
        });

        /*
         * The ownership chain. One row per holder, in sequence, with the open
         * row (owned_until null) being the current owner.
         *
         * owner_user_id is nullable on purpose: a gallery in Douala or a museum
         * in Paris may hold a registered work without ever holding an account
         * here, and a provenance register that can only describe its own users
         * is not a provenance register.
         */
        Schema::create('product_ownerships', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            // users.id is a UUID here, so this cannot be a foreignId.
            $table->char('owner_user_id', 36)->nullable();
            $table->foreign('owner_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('owner_ref', 32)->nullable();          // OWN-AH237-00002345
            $table->string('legal_name');
            $table->enum('entity_type', [
                'individual', 'company', 'gallery', 'museum', 'government', 'foundation',
            ])->default('individual');
            $table->char('country_code', 2)->nullable();           // ISO 3166-1 alpha-2
            $table->string('address')->nullable();
            $table->enum('verification_level', ['unverified', 'declared', 'verified', 'institution'])
                ->default('declared');
            $table->boolean('is_original_creator')->default(false);
            $table->timestamp('owned_from');
            $table->timestamp('owned_until')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'sequence']);
            $table->index(['product_id', 'owned_until']);
        });

        /*
         * A transfer moves the open ownership row to a new one. It is a record
         * of a change that the parties declare, not a payment the platform
         * takes: settlement here is offline, so a payment reference is
         * something the parties supply rather than something we can vouch for.
         */
        Schema::create('ownership_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('certificate_no', 48)->unique();        // AH237-OTC-CM-2026-000000000245
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_ownership_id')->nullable()->constrained('product_ownerships')->nullOnDelete();
            $table->foreignId('to_ownership_id')->nullable()->constrained('product_ownerships')->nullOnDelete();

            $table->enum('transfer_type', [
                'sale', 'gift', 'donation', 'inheritance', 'museum_acquisition',
                'gallery_acquisition', 'exchange', 'court_order', 'other',
            ])->default('sale');
            $table->timestamp('transferred_at')->nullable();
            $table->string('transfer_city')->nullable();
            $table->char('transfer_country', 2)->nullable();

            // Declared by the parties. The platform is not a party to the sale.
            $table->string('transaction_ref')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('payment_ref')->nullable();
            $table->char('currency', 3)->nullable();               // ISO 4217
            $table->decimal('declared_value', 14, 2)->nullable();
            $table->boolean('value_is_private')->default(false);

            $table->enum('condition', ['excellent', 'very_good', 'good', 'fair', 'poor'])->nullable();
            $table->text('condition_notes')->nullable();
            $table->text('accessories')->nullable();

            // pending -> approved -> active; then superseded by the next transfer,
            // or revoked/cancelled.
            $table->enum('status', ['pending', 'approved', 'active', 'superseded', 'revoked', 'cancelled'])
                ->default('pending');
            $table->string('revoked_reason')->nullable();

            $table->string('content_hash', 64)->nullable();
            $table->string('signature', 64)->nullable();
            $table->string('verification_pin', 12)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });

        /*
         * The artisan verification certificate's own record. The checks are
         * stored as a map rather than columns so that a check the platform does
         * not yet perform is simply absent — never a false tick.
         */
        Schema::create('artisan_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('certificate_no', 48)->unique();        // AH237-AVC-CM-2026-0000000458
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verification_application_id')->nullable();

            // 1 identity, 2 professional, 3 workshop, 4 certified,
            // 5 master, 6 heritage master, 7 nationally recognised.
            $table->unsignedTinyInteger('level')->default(1);
            $table->json('checks')->nullable();
            $table->json('metrics')->nullable();

            $table->enum('status', ['active', 'suspended', 'expired', 'revoked'])->default('active');
            $table->string('revoked_reason')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('signature', 64)->nullable();
            $table->string('verification_pin', 12)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });

        /*
         * The audit trail every one of the four certificates shows. Kept in one
         * table so a certificate cannot display a lifecycle its record does not
         * hold — each line on the printed trail is a row here or it does not
         * appear.
         */
        Schema::create('certificate_events', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_type', 8);                 // coa | otc | prc | avc
            $table->unsignedBigInteger('certificate_id');
            $table->string('event', 40);                           // issued, verified, approved, ...
            $table->char('actor_user_id', 36)->nullable();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['certificate_type', 'certificate_id', 'occurred_at'], 'cert_events_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_events');
        Schema::dropIfExists('artisan_verifications');
        Schema::dropIfExists('ownership_transfers');
        Schema::dropIfExists('product_ownerships');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['prn', 'oln', 'registered_at']);
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('gan');
        });
    }
};
