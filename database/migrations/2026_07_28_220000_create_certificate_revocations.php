<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public revocation list.
 *
 * Every certificate table already carried somewhere to write "this one is
 * withdrawn" — a revoked_at, or a status enum with `revoked` in it. None of
 * them carried a way to say it, a vocabulary for why, or anywhere a member of
 * the public could look it up. A revocation nobody can find is not a
 * revocation; it is a private note in a database, and the instruction printed
 * on every certificate we issue — check the live register before relying on
 * this sheet — is empty without it.
 *
 * So this table exists beside the certificate rows rather than inside them,
 * for three reasons that each cost something.
 *
 * It is one table across all five registers because the person searching has
 * one number in their hand and does not know, and should not need to know,
 * which register minted it. A per-register revocation column would mean five
 * lookups and five shapes for one question.
 *
 * The reason is an enum rather than free text because a public statement about
 * a named artisan's work has to come from a fixed, reviewable vocabulary. Free
 * text on a public list is how an investigation's details, or somebody's name,
 * ends up published by accident. The free-text explanation lives in
 * reason_note, which is deliberately not public.
 *
 * is_public and published_at are separate because "revoked" and "announced"
 * are different acts. A revocation made an hour ago on a court order that has
 * not yet been served should stop the certificate verifying immediately and
 * appear on the list when the officer says so, not before.
 *
 * Rows here are never deleted. A revocation entered in error is reinstated —
 * a second recorded event on the same row — because the fact that a
 * certificate was once publicly withdrawn is itself part of its history, and
 * erasing it would leave a holder who saw the list with no way to find out
 * what happened.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_revocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // The five registers that issue numbered documents: certificate of
            // authenticity, ownership transfer, artisan verification, export
            // authenticity, workshop verification.
            $table->enum('certificate_type', ['coa', 'otc', 'avc', 'eac', 'wvc']);
            $table->unsignedBigInteger('certificate_id');

            // Denormalised on purpose. The list is searched by the number
            // printed on the sheet in the reader's hand, and that search must
            // not depend on first working out which of five tables to join.
            // It is also the only durable handle if a certificate row is ever
            // cascade-deleted with its product: the revocation outlives it.
            $table->string('certificate_no', 64)->index();

            $table->enum('reason', [
                'fraud',
                'forgery',
                'administrative_error',
                'court_order',
                'owner_request',
                'security_breach',
                'superseded_by_reissue',
                'other',
            ]);

            // Never shown publicly. It may name an investigator, a docket, a
            // complainant — the detail that makes the file useful internally
            // and would be a disclosure if published.
            $table->text('reason_note')->nullable();

            $table->timestamp('revoked_at');

            // users.id is a char(36) UUID in this schema, so foreignId() would
            // create a bigint that can never match it and leave the migration
            // half-applied. Declared explicitly, and nullable so a revocation
            // survives the officer's account being removed.
            $table->char('revoked_by_user_id', 36)->nullable();
            $table->foreign('revoked_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->boolean('is_public')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // One live revocation per certificate. Revoking twice is a repeated
            // instruction, not a second withdrawal, and two rows would show the
            // same number twice on a public list.
            $table->unique(['certificate_type', 'certificate_id']);

            // The list's own ordering: published entries, newest first.
            $table->index(['is_public', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_revocations');
    }
};
