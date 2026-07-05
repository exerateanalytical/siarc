<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SIARC salon / exhibition operations layer. Everything hangs off the existing
 * events + event_exhibitors + businesses backbone — no duplication of the vendor
 * (businesses) records; exhibitor identity is reused, salon-specific data is added.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Physical zones of the salon (a SIARC event has several pavilions).
        Schema::create('pavilions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('color', 9)->nullable();      // hex accent for the floor plan
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['event_id', 'sort_order']);
        });

        // Stands (booths) inside a pavilion, allocated to an exhibitor.
        Schema::create('stands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pavilion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exhibitor_id')->nullable()->constrained('event_exhibitors')->nullOnDelete();
            $table->string('code');                       // e.g. "A-12"
            $table->string('label')->nullable();
            $table->decimal('size_sqm', 6, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->enum('status', ['available', 'reserved', 'allocated'])->default('available');
            $table->integer('pos_x')->default(0);         // floor-plan coordinates
            $table->integer('pos_y')->default(0);
            $table->integer('pos_w')->default(60);
            $table->integer('pos_h')->default(40);
            $table->timestamps();
            $table->index(['event_id', 'status']);
        });

        // Salon visitors (registration → badge → check-in). Distinct from platform users.
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('country')->nullable();
            $table->enum('type', ['visitor', 'buyer', 'vip', 'press', 'staff'])->default('visitor');
            $table->string('badge_code')->nullable()->unique();
            $table->string('qr_token')->nullable()->unique();
            $table->enum('status', ['registered', 'checked_in', 'cancelled'])->default('registered');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'type', 'status']);
        });

        // Speakers / facilitators for the programme.
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role_fr')->nullable();
            $table->string('role_en')->nullable();
            $table->string('organization')->nullable();
            $table->text('bio_fr')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['event_id', 'sort_order']);
        });

        // Programme entries: sessions, workshops, keynotes, panels, ceremonies.
        Schema::create('programme_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pavilion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained('speakers')->nullOnDelete();
            $table->enum('type', ['session', 'workshop', 'keynote', 'panel', 'ceremony'])->default('session');
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('room')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('registration_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['event_id', 'type', 'starts_at']);
        });

        // Many-to-many: a session can have several speakers.
        Schema::create('session_speaker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('programme_sessions')->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained('speakers')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'speaker_id']);
        });

        // Workshop / session registrations (reuses the visitor identity when present).
        Schema::create('session_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('programme_sessions')->cascadeOnDelete();
            $table->foreignId('visitor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            $table->index('session_id');
        });

        // B2B matchmaking meetings between a buyer/visitor and an exhibitor.
        Schema::create('b2b_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
            $table->foreignId('requester_business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->foreignId('host_exhibitor_id')->nullable()->constrained('event_exhibitors')->nullOnDelete();
            $table->foreignId('stand_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedSmallInteger('duration_min')->default(30);
            $table->string('location')->nullable();
            $table->enum('status', ['requested', 'confirmed', 'declined', 'completed', 'cancelled'])->default('requested');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'status']);
        });

        // Gate check-in log (staff scanner). Subject is a visitor or an exhibitor.
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');               // 'visitor' | 'exhibitor'
            $table->unsignedBigInteger('subject_id');
            $table->string('gate')->nullable();
            $table->uuid('scanned_by')->nullable();       // users.id (uuid) — no strict FK
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'subject_type', 'subject_id']);
        });

        // Exhibitor allocation + badge/check-in fields on the existing pivot.
        Schema::table('event_exhibitors', function (Blueprint $table) {
            $table->foreignId('pavilion_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->string('badge_code')->nullable()->after('booth_number');
            $table->string('qr_token')->nullable()->after('badge_code');
            $table->timestamp('checked_in_at')->nullable()->after('registered_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_exhibitors', function (Blueprint $table) {
            if (Schema::hasColumn('event_exhibitors', 'pavilion_id')) {
                $table->dropConstrainedForeignId('pavilion_id');
            }
            $table->dropColumn(['badge_code', 'qr_token', 'checked_in_at']);
        });
        Schema::dropIfExists('check_ins');
        Schema::dropIfExists('b2b_meetings');
        Schema::dropIfExists('session_registrations');
        Schema::dropIfExists('session_speaker');
        Schema::dropIfExists('programme_sessions');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('stands');
        Schema::dropIfExists('pavilions');
    }
};
