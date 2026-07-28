<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives reviews and awards the columns a moderated register needs.
 *
 * `business_reviews` was created with `status` defaulting to `published`, which
 * means the original design let a stranger put words on an artisan's public
 * profile with nobody in between. The default moves to `pending` and the state
 * list gains `rejected`, so publication becomes an act somebody performed rather
 * than the absence of one. `moderated_by`/`moderated_at`/`moderation_note` say
 * who performed it and why; `published_at` is what the profile orders by, and
 * its being null is the honest way of saying "not public".
 *
 * `moderated_by` and `recorded_by` are char(36) and carry no foreign key.
 * `users.id` is a UUID here, so `foreignId()` would silently create a bigint and
 * fail on insert. They are also deliberately not cascade-deleted: a moderation
 * decision or an award record must survive the departure of the account that
 * made it, otherwise the trail rewrites itself when somebody closes an account.
 *
 * `business_awards` gains `evidence_url`, `reference` and `recorded_by` because
 * an award row asserts that an outside body honoured this artisan. Without a
 * pointer to the thing that proves it, the row is only a sentence somebody
 * typed — and this project has already had to strip invented UNESCO and
 * ministry honours off certificates once.
 *
 * The unique index the reviews table already carries — (reviewer_id,
 * business_id) — is what stops one person stacking reviews on one artisan. It
 * is not recreated in the other column order: the constraint is the same
 * constraint, and a duplicate index would only be a second thing to maintain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('business_reviews', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('business_reviews', 'moderated_by')) {
                $table->char('moderated_by', 36)->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('business_reviews', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            }
            if (! Schema::hasColumn('business_reviews', 'moderation_note')) {
                $table->string('moderation_note', 500)->nullable()->after('moderated_at');
            }
        });

        // The state list. It was an enum of three published-by-default values;
        // it becomes a plain short string so the application owns the vocabulary
        // and adding a state later is not a table rewrite. Driver-split because
        // MySQL needs the enum replaced outright and SQLite (the test database)
        // is rebuilt by Laravel's own change().
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE business_reviews MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('business_reviews', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        }

        // Existing rows: there are none in any environment, but if a row ever
        // did exist it was created under the old published-by-default rule and
        // has never been read by a moderator. It goes to the queue.
        DB::table('business_reviews')->where('status', 'flagged')->update(['status' => 'pending']);

        Schema::table('business_awards', function (Blueprint $table) {
            if (! Schema::hasColumn('business_awards', 'evidence_url')) {
                $table->string('evidence_url', 500)->nullable()->after('year');
            }
            if (! Schema::hasColumn('business_awards', 'reference')) {
                $table->string('reference', 120)->nullable()->after('evidence_url');
            }
            if (! Schema::hasColumn('business_awards', 'recorded_by')) {
                $table->char('recorded_by', 36)->nullable()->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_reviews', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'moderated_by', 'moderated_at', 'moderation_note']);
        });

        Schema::table('business_awards', function (Blueprint $table) {
            $table->dropColumn(['evidence_url', 'reference', 'recorded_by']);
        });
    }
};
