<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The register behind the Workshop Verification Certificate.
 *
 * The document asks the platform to attest that a production facility was
 * inspected and meets a standard. Until now there was nothing underneath that
 * claim at all: `businesses` holds an address, a pair of coordinates, a founding
 * year and a headcount, and none of those describe a workshop. A certificate
 * printed from that would have been a design mock with a signature on it.
 *
 * Two decisions shape every table here.
 *
 * The first is that almost every measurement is nullable, and that is not
 * laziness about defaults — it is the central rule. A workshop nobody has
 * measured must not report a floor area of zero square metres, because zero is a
 * measurement and "unmeasured" is not. The same goes for room counts, worker
 * counts and every inspection sub-score: a dimension nobody scored is null, and
 * the assessment reads null as "cannot judge" rather than as a nought that drags
 * an average down or, worse, is quietly skipped so the percentage stays high.
 *
 * The second is the compliance default of `unassessed` rather than `valid`. A
 * licence row created when a workshop is opened records that the platform knows
 * such a licence should exist, not that it has seen one. Defaulting to valid
 * would mean every workshop ever opened reports a full set of good standing on
 * the day it is created, which is the single most damaging lie this register
 * could tell.
 *
 * What is deliberately absent: any column for satellite imagery verification, AI
 * image matching or a fraud-risk score. The design shows a panel for them. There
 * is no model, no imagery feed and no fraud system behind this platform, and a
 * nullable column named `satellite_verified_at` is an invitation to fill it with
 * something that was never checked. A *human* inspection is a real, recordable
 * event, so that is what `workshop_inspections` models, in full, with the
 * inspector named and answerable.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * The workshop itself. Distinct from the business: one artisan business
         * may run a compound with several production sites, and a certificate
         * attests to a place, not to a company.
         */
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // AH237-GWN-CM-0000000000, the reserved format in docs/ahts/10-identifiers.md.
            $table->string('gwn', 40)->nullable()->unique();
            $table->string('name');
            $table->string('registration_no')->nullable();
            $table->string('workshop_type')->nullable();
            $table->enum('legal_status', [
                'sole_trader', 'registered_business', 'cooperative', 'company', 'informal',
            ])->nullable();
            $table->date('established_on')->nullable();

            /* Location. Cameroon's administrative ladder is region → division →
             * subdivision → village, and the platform's own region/city tables
             * only cover the top and the towns, so the intermediate levels are
             * free text rather than forced into a taxonomy that does not exist. */
            $table->char('country', 2)->default('CM');
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('division')->nullable();
            $table->string('subdivision')->nullable();
            $table->string('village')->nullable();
            $table->string('community')->nullable();
            $table->string('address')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->integer('altitude_m')->nullable();
            // Set only when somebody stood at the coordinates. A pin dropped on
            // a map by the owner is a claim, not a verification.
            $table->timestamp('geo_verified_at')->nullable();

            /* Infrastructure — every field nullable. See the class note: an
             * unmeasured workshop must not read as a workshop measured at zero. */
            $table->decimal('total_area_m2', 8, 2)->nullable();
            $table->unsignedTinyInteger('production_rooms')->nullable();
            $table->unsignedTinyInteger('finishing_areas')->nullable();
            $table->unsignedTinyInteger('storage_areas')->nullable();
            $table->unsignedTinyInteger('drying_areas')->nullable();
            $table->unsignedTinyInteger('packaging_areas')->nullable();
            $table->unsignedTinyInteger('display_areas')->nullable();
            $table->string('water_supply')->nullable();
            $table->string('electricity_supply')->nullable();
            $table->string('internet')->nullable();
            $table->enum('accessibility', ['poor', 'fair', 'good', 'excellent'])->nullable();
            // Nullable booleans, three-valued on purpose: false means an
            // inspector looked and found none, null means nobody looked.
            $table->boolean('fire_safety_equipment')->nullable();
            $table->boolean('emergency_exits')->nullable();

            /* Workforce. Nullable for the same reason: "no apprentices recorded"
             * and "recorded as having no apprentices" are different findings. */
            $table->unsignedTinyInteger('master_artisans')->nullable();
            $table->unsignedTinyInteger('skilled_workers')->nullable();
            $table->unsignedTinyInteger('apprentices')->nullable();
            $table->unsignedTinyInteger('female_workers')->nullable();
            $table->unsignedTinyInteger('youth_workers')->nullable();

            $table->unsignedInteger('max_monthly_capacity')->nullable();
            $table->string('production_standards')->nullable();
            $table->string('packaging_standards')->nullable();

            // users.id is a char(36) UUID here, so this cannot be a foreignId().
            $table->char('owner_user_id', 36)->nullable();
            $table->foreign('owner_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('manager_name')->nullable();

            /* Sustainability. Nullable booleans again — an unanswered question
             * about waste management must not print as "no waste management". */
            $table->boolean('renewable_materials')->nullable();
            $table->boolean('waste_management')->nullable();
            $table->boolean('recycling')->nullable();
            $table->boolean('water_conservation')->nullable();
            $table->enum('energy_efficiency', ['poor', 'fair', 'good', 'excellent'])->nullable();
            $table->string('carbon_note')->nullable();

            $table->enum('status', [
                'draft', 'submitted', 'under_inspection', 'verified', 'suspended', 'revoked', 'archived',
            ])->default('draft');
            // Null until an inspection supports a level. A default of 1 would be
            // the register awarding a tier it never assessed.
            $table->unsignedTinyInteger('verification_level')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('next_inspection_on')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        /*
         * Equipment, one row per item rather than a count column, because the
         * certificate lists an inventory and a bare number cannot be listed.
         */
        Schema::create('workshop_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->enum('category', [
                'major_machine', 'hand_tool', 'carving_tool', 'power_tool',
                'kiln_or_oven', 'drying', 'extraction', 'safety', 'digital', 'other',
            ]);
            $table->string('label');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'category']);
        });

        /*
         * Licences and permits. The default status is `unassessed`, never
         * `valid` — a row here means the obligation is known, not that the
         * document has been seen. verified_at and verified_by stay null until a
         * named person actually checked it.
         */
        Schema::create('workshop_compliance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', [
                'business_licence', 'tax_registration', 'environmental', 'fire_safety',
                'health_safety', 'labour', 'insurance', 'export_packaging',
            ]);
            $table->string('reference')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->enum('status', ['valid', 'expired', 'pending', 'not_applicable', 'unassessed'])
                ->default('unassessed');
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'kind']);
        });

        /*
         * The human inspection. This is the only evidence in the whole register
         * that can raise a workshop to verified, and every sub-score is nullable
         * so that a dimension the inspector did not reach reports as unscored
         * rather than as a zero the workshop did not earn.
         *
         * Each score is out of 20, including documentation, so the seven
         * dimensions total 140 when all are present — but the assessment never
         * assumes they all are.
         */
        Schema::create('workshop_inspections', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->date('inspected_on');
            $table->string('inspector_name')->nullable();
            $table->string('inspector_ref')->nullable();
            // Named honestly: a document review and a site visit are not the
            // same evidence, and the certificate should be able to say which.
            $table->enum('method', ['on_site', 'remote', 'document_review', 'photographic'])
                ->default('on_site');
            $table->text('findings')->nullable();

            foreach ([
                'infrastructure_score', 'equipment_score', 'workforce_score', 'safety_score',
                'compliance_score', 'sustainability_score', 'documentation_score',
            ] as $column) {
                $table->unsignedTinyInteger($column)->nullable();
            }

            $table->enum('outcome', ['passed', 'passed_with_conditions', 'failed', 'inconclusive'])->nullable();
            $table->date('next_due_on')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'inspected_on']);
        });

        /*
         * The Workshop Verification Certificate itself. Mirrors the shape of the
         * other certificate tables so verification, signing and the audit trail
         * work identically across all of them.
         *
         * expires_at is real rather than decorative: an inspection lapses, and a
         * certificate that outlives the inspection behind it is asserting a
         * present-tense fact from stale evidence.
         */
        Schema::create('workshop_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('certificate_no', 48)->unique();   // AH237-WVC-CM-2026-0000000012
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()
                ->constrained('workshop_inspections')->nullOnDelete();

            $table->unsignedTinyInteger('level')->default(1);
            // Stored as maps, not columns, so a check the platform never
            // performs is absent from the document rather than a false tick.
            $table->json('checks')->nullable();
            $table->json('metrics')->nullable();

            $table->enum('status', ['active', 'suspended', 'expired', 'revoked'])->default('active');
            $table->string('revoked_reason')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('signature', 64)->nullable();
            $table->text('ca_signature')->nullable();
            $table->string('ca_kid', 64)->nullable();
            $table->string('verification_pin', 12)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_certificates');
        Schema::dropIfExists('workshop_inspections');
        Schema::dropIfExists('workshop_compliance');
        Schema::dropIfExists('workshop_equipment');
        Schema::dropIfExists('workshops');
    }
};
