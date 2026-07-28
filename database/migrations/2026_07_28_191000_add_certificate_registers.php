<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The data behind the ticks.
 *
 * The certificates were already printing reassurances — "not reported stolen",
 * "owner verified", "export ready" — with nothing underneath them. A tick with
 * no register behind it is worse than no tick at all: it is the platform
 * vouching for a fact it has never once looked up. This migration creates the
 * three registers those ticks have to read from before any of them may render.
 *
 * The shape of each was chosen so that "we do not know" stays representable:
 *
 *  - product_flags holds only raised concerns. Absence of a flag means nobody
 *    has reported anything, which is a far weaker statement than "this piece is
 *    clean" — the code that reads it is responsible for saying so, and the
 *    certificate wording follows.
 *  - the export and insurance columns live on the transfer, not the product,
 *    because they describe one shipment by two parties on one date. They are
 *    all nullable and all party-declared: the platform issues no export permit
 *    and underwrites no policy, it records what the parties state.
 *  - the identity columns store the document number encrypted and a last-four
 *    in clear. Nothing on the platform ever needs the full number again after
 *    the reviewer has checked it; keeping it readable would be collecting a
 *    national identity number in plaintext for the convenience of never having
 *    to decrypt it, which is not a trade worth making.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * A flag is a claim by somebody, with a reference to whatever they filed
         * it under — a police report, a court docket — because the platform is
         * not an investigator and its record is only as good as the paperwork it
         * points at. Flags are resolved or withdrawn, never deleted: a piece
         * that was reported stolen and cleared has a history a buyer is
         * entitled to see.
         */
        Schema::create('product_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('flag', [
                'reported_stolen', 'under_investigation', 'disputed_ownership', 'export_restricted',
            ]);
            // users.id is a UUID here, so this cannot be a foreignId.
            $table->char('raised_by_user_id', 36)->nullable();
            $table->foreign('raised_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('reference')->nullable();   // e.g. a police report number
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'resolved', 'withdrawn'])->default('active');
            $table->timestamp('raised_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Every certificate render asks exactly one question of this table:
            // "are there active flags on this product". That is this index.
            $table->index(['product_id', 'status']);
        });

        /*
         * Export, customs and insurance detail for a transfer that crosses a
         * border. Declared by the parties — a CITES reference here means the
         * seller told us they hold one, not that we saw it, and the certificate
         * says as much.
         */
        Schema::table('ownership_transfers', function (Blueprint $table) {
            $table->boolean('export_ready')->default(false)->after('accessories');
            $table->string('export_permit_no')->nullable()->after('export_ready');
            $table->string('cites_reference')->nullable()->after('export_permit_no');
            $table->char('country_of_export', 2)->nullable()->after('cites_reference');       // ISO 3166-1 alpha-2
            $table->char('country_of_destination', 2)->nullable()->after('country_of_export');
            $table->string('customs_reference')->nullable()->after('country_of_destination');
            $table->string('shipping_reference')->nullable()->after('customs_reference');

            $table->string('insurer_name')->nullable()->after('shipping_reference');
            $table->string('insurance_policy_no')->nullable()->after('insurer_name');
            $table->decimal('insurance_value', 14, 2)->nullable()->after('insurance_policy_no');
            $table->char('insurance_currency', 3)->nullable()->after('insurance_value');      // ISO 4217
            $table->date('coverage_start')->nullable()->after('insurance_currency');
            $table->date('coverage_end')->nullable()->after('coverage_start');
        });

        /*
         * Identity verification of the artisan behind a shop.
         *
         * id_document_encrypted holds a Laravel Crypt ciphertext, which is why
         * it is text and not a sized string; id_document_last4 is the only part
         * that is ever displayed or searched. id_verified_at is set by a human
         * reviewer and is the single piece of evidence the "identity verified"
         * tick is allowed to read — a document on file that nobody has looked
         * at verifies nothing.
         */
        Schema::table('businesses', function (Blueprint $table) {
            $table->enum('id_document_type', [
                'national_id', 'passport', 'residence_permit', 'driving_licence',
            ])->nullable()->after('verification_tier');
            $table->text('id_document_encrypted')->nullable()->after('id_document_type');
            $table->char('id_document_last4', 4)->nullable()->after('id_document_encrypted');
            $table->timestamp('id_verified_at')->nullable()->after('id_document_last4');
            $table->char('id_verified_by', 36)->nullable()->after('id_verified_at');
            $table->foreign('id_verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_flags');

        Schema::table('ownership_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'export_ready', 'export_permit_no', 'cites_reference',
                'country_of_export', 'country_of_destination',
                'customs_reference', 'shipping_reference',
                'insurer_name', 'insurance_policy_no', 'insurance_value',
                'insurance_currency', 'coverage_start', 'coverage_end',
            ]);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['id_verified_by']);
            $table->dropColumn([
                'id_document_type', 'id_document_encrypted', 'id_document_last4',
                'id_verified_at', 'id_verified_by',
            ]);
        });
    }
};
