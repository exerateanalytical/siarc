<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The register behind the Export Authenticity Certificate.
 *
 * An export is not a change of ownership, and conflating the two was the trap
 * worth avoiding here. A piece can be exported without changing hands — a loan
 * to an exhibition, a return to a restorer, a museum shipping its own holding —
 * and it can change hands without ever crossing a border. ownership_transfers
 * already carries the insurance and customs columns for the transfer that
 * happens to involve a shipment; what it has no room for is the export event
 * itself, with its own importer, its own permit, its own inspection and its own
 * approval, which may be granted or refused independently of any sale.
 *
 * So these tables describe the consignment: who is sending, who is receiving,
 * under what declarations, on what aircraft, in what crate, in what condition.
 * Nothing here duplicates the transfer columns; the two records point at the
 * same product and are read together.
 *
 * Note that almost every compliance column is nullable, and that the enums have
 * an explicit "unassessed" or "pending" member rather than defaulting to the
 * happy answer. That is deliberate and it is the whole ethic of this register: a
 * cultural heritage declaration nobody has made must be storable as *not made*.
 * A column defaulting to "compliant" would let a certificate print a clearance
 * that no human ever gave, and the scoring in ExportRegister leans on the
 * difference between "assessed and clean" and "never assessed".
 */
return new class extends Migration
{
    /** The five-point condition scale, shared by every graded facet. */
    private const CONDITION = ['excellent', 'very_good', 'good', 'fair', 'poor'];

    public function up(): void
    {
        /*
         * The exporting party. Usually the artisan's own business, but modelled
         * separately because an export licence belongs to whoever holds it — a
         * cooperative or a freight agent may export on the maker's behalf, and
         * that party has a legal name and a licence number the business record
         * has no place for.
         */
        Schema::create('exporters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            // users.id is a char(36) UUID on this schema, so foreignId() cannot
            // be used against it.
            $table->char('user_id', 36)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('legal_name');
            $table->string('trading_name')->nullable();
            $table->char('country', 2)->nullable();                // ISO 3166-1 alpha-2
            $table->string('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 32)->nullable();
            // Nullable, and never filled in on creation: the platform issues no
            // export licences and has verified none. A number here means someone
            // supplied one, not that we checked it.
            $table->string('export_licence_no')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('business_id');
        });

        /*
         * One row per export event.
         *
         * The status ladder is draft → submitted → under_review → approved →
         * shipped → delivered, with rejected, cancelled and revoked as exits.
         * The ladder is enforced in ExportRegister rather than by the database,
         * but it is written down here because the enum is the only place a
         * reader can see the complete set of states a consignment may hold.
         */
        Schema::create('export_consignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('certificate_no', 48)->unique();        // AH237-EAC-CM-2026-000000000125
            // The global export certificate number: quoted on customs paperwork
            // and by the receiving institution, and distinct from the
            // certificate number so that a reissued certificate can reference
            // the same consignment.
            $table->string('gecn', 48)->unique();                  // AH237-GECN-CM-2026-000000000125
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exporter_id')->nullable()->constrained('exporters')->nullOnDelete();
            // Which holder is sending it. Nullable because a consignment may be
            // opened before the ownership chain has been reconciled, and a wrong
            // link is worse than none.
            $table->foreignId('owner_ownership_id')->nullable()->constrained('product_ownerships')->nullOnDelete();

            $table->string('importer_name');
            $table->enum('importer_type', [
                'individual', 'company', 'gallery', 'museum', 'government', 'foundation', 'auction_house',
            ])->default('individual');
            $table->char('importer_country', 2)->nullable();
            $table->string('importer_city')->nullable();
            $table->string('importer_address')->nullable();

            $table->enum('intended_purpose', [
                'sale', 'museum_acquisition', 'exhibition_loan', 'gift', 'auction',
                'restoration', 'research', 'cultural_exchange',
            ])->default('sale');

            /*
             * Compliance. Every one of these is a declaration made by a named
             * party, not a finding of ours — the platform inspects nothing and
             * issues nothing. The enums keep "pending" and "unassessed" as first
             * class answers so the register can hold the true state of "nobody
             * has looked at this yet".
             */
            $table->char('country_of_origin', 2)->nullable();
            $table->string('origin_certificate_ref')->nullable();
            $table->enum('cultural_heritage_declaration', ['compliant', 'not_applicable', 'pending', 'restricted'])->nullable();
            $table->enum('ethical_sourcing_declaration', ['compliant', 'not_applicable', 'pending', 'restricted'])->nullable();
            // Defaults to unassessed rather than none. "We do not know whether
            // this contains ivory, ebony or pangolin scale" is the honest state
            // of a piece nobody has examined, and it must never read as "clean".
            $table->enum('protected_materials', ['none', 'cites_listed', 'restricted', 'unassessed'])->default('unassessed');
            $table->string('export_permit_no')->nullable();
            $table->string('customs_declaration_no')->nullable();
            $table->enum('inspection_status', ['pending', 'approved', 'rejected', 'not_required'])->nullable();
            $table->timestamp('inspected_at')->nullable();

            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'approved', 'rejected',
                'shipped', 'delivered', 'cancelled', 'revoked',
            ])->default('draft');
            $table->string('rejected_reason')->nullable();

            $table->string('content_hash', 64)->nullable();
            $table->string('signature', 64)->nullable();
            // The authority's Ed25519 signature and the key that made it. Wider
            // than the HMAC column because a base64url Ed25519 signature is 86
            // characters.
            $table->string('ca_signature', 128)->nullable();
            $table->string('ca_kid', 64)->nullable();
            $table->string('verification_pin', 12)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });

        /*
         * The physical movement. Separate from the consignment because one
         * export can be re-booked — a cancelled flight, a changed carrier —
         * without the approval, the declarations or the certificate number
         * changing, and because a consignment awaiting approval has no shipment
         * at all. That absence is meaningful: it is what makes the packaging and
         * logistics readiness categories unassessable rather than zero.
         */
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('export_consignment_id')->constrained()->cascadeOnDelete();
            $table->string('carrier')->nullable();
            $table->string('service')->nullable();
            $table->string('awb_no', 64)->nullable();              // air waybill
            $table->string('bill_of_lading_no', 64)->nullable();   // sea freight
            $table->string('tracking_no', 64)->nullable();
            $table->string('flight_or_vessel', 64)->nullable();
            $table->string('port_of_exit')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('expected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedSmallInteger('package_count')->nullable();
            $table->string('crate_ref', 64)->nullable();
            $table->decimal('gross_weight_kg', 8, 3)->nullable();
            $table->decimal('net_weight_kg', 8, 3)->nullable();
            $table->string('dimensions')->nullable();
            // Booleans rather than an enum: each protection is bought
            // separately and a crate may have one without the others. False
            // means "not specified as provided", which the scoring treats as
            // absent rather than as a defect.
            $table->boolean('shock_protection')->default(false);
            $table->boolean('climate_protection')->default(false);
            $table->boolean('humidity_protection')->default(false);
            $table->timestamps();

            $table->index('export_consignment_id');
        });

        /*
         * Condition at a moment in time.
         *
         * Deliberately hung off the product rather than the consignment, with
         * the consignment link nullable, because a condition report is worth
         * having whether or not anything is being exported — the provenance
         * certificate wants the same rows, and an export that is later cancelled
         * should not take the inspection record with it.
         *
         * Every facet is nullable: an inspector who looked only at the surface
         * must be able to record exactly that, and a blank facet must never be
         * read as "excellent".
         */
        Schema::create('condition_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('export_consignment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->string('inspector_name')->nullable();
            // The inspector's own reference, which is what makes the report
            // checkable by somebody other than us.
            $table->string('inspector_ref', 64)->nullable();
            $table->enum('surface', self::CONDITION)->nullable();
            $table->enum('structural', self::CONDITION)->nullable();
            $table->enum('finish', self::CONDITION)->nullable();
            $table->enum('preservation', self::CONDITION)->nullable();
            $table->enum('packaging', self::CONDITION)->nullable();
            $table->enum('overall', self::CONDITION)->nullable();
            $table->text('notes')->nullable();
            $table->string('report_ref', 64)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'inspected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_reports');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('export_consignments');
        Schema::dropIfExists('exporters');
    }
};
