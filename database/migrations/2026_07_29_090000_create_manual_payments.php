<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual settlement of the platform's own fees.
 *
 * The platform charges for registration, membership, renewal and verification,
 * and there is no payment integration. Money moves by mobile money outside our
 * systems entirely; all we can hold is an intent, a claim that it was sent, and
 * an administrator's confirmation that it arrived. These two tables are shaped
 * around that ignorance rather than pretending it away.
 *
 * `payments` is the mutable working record — status moves as the claim is made
 * and reviewed. `payment_events` is the append-only trail beside it. The split
 * exists because money changing hands needs a record of who said what and when
 * that cannot be edited in place: if a dispute arises months later about whether
 * a fee was ever confirmed, a status column alone can be quietly overwritten and
 * proves nothing. The trail is the evidence; the status is only the summary.
 *
 * Note what deliberately does not exist here: no balance, no ledger, no
 * automatic settlement. A confirmed payment is a human assertion, timestamped
 * and attributed. That is the whole of what the platform can honestly claim.
 *
 * SCHEMA NOTE: users.id is char(36). foreignId() would emit a bigint column and
 * the foreign key would fail, so every user reference is char(36) with an
 * explicit foreign() clause.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Human-quotable, e.g. AH237-PAY-2026-000123. The payer types this
            // into the operator's "reason" field, so it is the only link between
            // an MTN transaction record and this row. Unique because a collision
            // would attach one person's money to another person's account.
            $table->string('reference', 40)->unique();

            // Nullable morph: a payment may be for a subscription, a
            // verification application, or nothing at all yet — a registration
            // fee is often taken before the thing it pays for exists.
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();

            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();

            $table->char('user_id', 36)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->enum('purpose', [
                'registration', 'membership', 'renewal',
                'verification', 'workshop_inspection', 'other',
            ])->default('other');

            // Decimal, never float. XAF has no minor unit in practice but the
            // scale is kept so a fee expressed to the centime round-trips
            // exactly; a float would make 25000.00 print as 24999.999999.
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('XAF');

            // Matches a key in config('payments.methods'). Not a foreign key:
            // methods are configuration, not data, and a method retired from the
            // config must not orphan or delete historical payments made with it.
            $table->string('method_code', 40);

            $table->enum('status', [
                'awaiting_payment', 'reported', 'under_review',
                'confirmed', 'rejected', 'cancelled', 'expired',
            ])->default('awaiting_payment');

            // Everything the payer states. All of it is a claim, none of it is
            // verified by us, and the column names should keep reading that way.
            $table->string('payer_name')->nullable();
            $table->string('payer_number', 40)->nullable();
            $table->string('payer_reference', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->string('proof_path')->nullable();

            $table->char('reviewed_by', 36)->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->string('rejection_reason', 500)->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // The reviewer queue reads status ordered by age; the business
            // dashboard reads everything for one business.
            $table->index(['status', 'created_at']);
            $table->index('business_id');
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();

            $table->string('event', 40);

            $table->char('actor_user_id', 36)->nullable();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('note', 500)->nullable();

            // Both ends of every move are stored rather than only the
            // destination, so the trail can be read on its own without
            // replaying it in order to work out where each step came from.
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['payment_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
