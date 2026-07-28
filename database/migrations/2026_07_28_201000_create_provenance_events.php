<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The lifetime register behind a Product Provenance Certificate.
 *
 * The ownership chain answers "who held it". A provenance dossier has to answer
 * the rest: where it was shown, who conserved it, what it was appraised at, who
 * wrote about it, which border it crossed and on what paper. None of that had
 * any representation, so the dossier could only ever have been decorated rather
 * than reported. This creates the rows it is drawn from.
 *
 * The shape is one spine plus typed detail rather than a table per kind of
 * event. The certificate's central artefact is a single chronological timeline,
 * and a timeline assembled from a dozen tables is assembled in PHP, which means
 * it can silently drop or reorder a kind of event as the code grows. With one
 * `provenance_events` table the timeline is one ordered query and cannot omit
 * something that exists. Valuations and restorations carry enough structured
 * detail of their own to deserve companion tables, but they hang off the spine
 * rather than replacing it, so an event is never invisible to the timeline
 * because its detail lives elsewhere.
 *
 * Dates are nullable throughout. Real provenance research routinely knows that
 * a piece was in a collection without knowing when, and a register that demands
 * a date will be fed an invented one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provenance_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->enum('type', [
                'exhibition', 'museum_accession', 'gallery_representation',
                'restoration', 'conservation', 'condition_report',
                'valuation', 'publication', 'media', 'award',
                'relocation', 'import', 'export', 'loan', 'other',
            ]);

            $table->string('title');
            // The institution as it names itself, kept as free text: most of the
            // museums, galleries and journals in a dossier will never be rows in
            // this database, and forcing them to be would mean either inventing
            // records for them or losing the event.
            $table->string('organisation')->nullable();
            $table->string('venue')->nullable();
            $table->char('country', 2)->nullable();                 // ISO 3166-1 alpha-2
            $table->string('city')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('reference_no')->nullable();             // accession no, catalogue no, ISBN
            $table->string('certificate_ref')->nullable();
            $table->text('notes')->nullable();

            // users.id is char(36) UUID here, so this cannot be a foreignId().
            $table->char('recorded_by_user_id', 36)->nullable();
            $table->foreign('recorded_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unsignedInteger('evidence_count')->default(0);

            // Verified means a person at the platform checked the event against
            // the institution's own record. It defaults to false and is never
            // set as a side effect of recording, because "verified" on a
            // provenance document is precisely the claim that must not be cheap.
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'started_on']);
            $table->index(['product_id', 'type']);
        });

        /*
         * An appraisal is one named person's opinion on one day in one currency.
         * All four are stored because a figure printed without them is not a
         * valuation, it is a number.
         */
        Schema::create('provenance_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provenance_event_id')->constrained()->cascadeOnDelete();
            $table->string('appraiser');
            $table->string('appraiser_ref')->nullable();
            $table->date('valued_on');
            // Decimal, not float: money that does not round-trip exactly has no
            // business on a document an insurer may rely on.
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);                            // ISO 4217
            $table->enum('purpose', ['insurance', 'sale', 'estate', 'donation', 'customs', 'other'])
                ->default('insurance');
            $table->timestamps();
        });

        /*
         * What was done to the object, by whom, with what. Materials are kept
         * because a future conservator needs to know what is already on the
         * piece before deciding what to add to it.
         */
        Schema::create('provenance_restorations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provenance_event_id')->constrained()->cascadeOnDelete();
            $table->string('restorer');
            $table->string('restorer_ref')->nullable();
            $table->date('performed_on');
            $table->text('description')->nullable();
            $table->text('materials_used')->nullable();
            $table->json('before_images')->nullable();
            $table->json('after_images')->nullable();
            $table->timestamps();
        });

        /*
         * The supporting paper. provenance_event_id is nullable because a
         * document can support the dossier as a whole — a maker's affidavit, a
         * research file — without belonging to a single event.
         *
         * is_public defaults to false, and that default is the important part of
         * this table. A piece of evidence is very often a purchase agreement
         * carrying a private party's name, address and the price they paid.
         * Publishing that by default would be a real privacy breach committed
         * quietly, at scale, by a default value. Making a document public has to
         * be a deliberate act by someone who has read it.
         */
        Schema::create('provenance_evidence', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provenance_event_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('kind', [
                'purchase_agreement', 'ownership_agreement', 'shipping_document',
                'customs_document', 'restoration_report', 'insurance_policy',
                'exhibition_document', 'condition_report', 'photograph', 'video',
                'research', 'other',
            ]);

            $table->string('title');
            $table->string('file_path')->nullable();
            // SHA-256 of the stored file, so a dossier can show that the document
            // it cites is byte-for-byte the one that was filed.
            $table->char('content_hash', 64)->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provenance_evidence');
        Schema::dropIfExists('provenance_restorations');
        Schema::dropIfExists('provenance_valuations');
        Schema::dropIfExists('provenance_events');
    }
};
