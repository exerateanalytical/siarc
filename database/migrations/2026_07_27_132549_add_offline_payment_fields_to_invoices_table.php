<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The platform processes no payments — buyer and seller settle between
 * themselves by transfer, mobile money or cash. So an invoice cannot know it
 * has been paid; it can only record that someone said so.
 *
 * Before this, `status` flipped to 'paid' and nothing recorded who flipped it.
 * Either party could mark the other's invoice paid, silently. These columns
 * make the claim attributable and give the counterparty a way to confirm or
 * dispute it, which is the most a platform can honestly offer when the money
 * never passes through it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('invoices', 'payment_note')) {
                $table->string('payment_note', 500)->nullable()->after('payment_reference');
            }
            // Who entered the payment. Nullable, so invoices settled before this
            // migration keep their paid_at without a false attribution.
            if (! Schema::hasColumn('invoices', 'recorded_by')) {
                $table->uuid('recorded_by')->nullable()->after('payment_note');
                $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            }
            // The counterparty agreeing the money actually arrived.
            if (! Schema::hasColumn('invoices', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('recorded_by');
            }
            if (! Schema::hasColumn('invoices', 'confirmed_by')) {
                $table->uuid('confirmed_by')->nullable()->after('confirmed_at');
                $table->foreign('confirmed_by')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('invoices', 'disputed_at')) {
                $table->timestamp('disputed_at')->nullable()->after('confirmed_by');
                $table->string('dispute_reason', 500)->nullable()->after('disputed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach (['recorded_by', 'confirmed_by'] as $fk) {
                if (Schema::hasColumn('invoices', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }
            $columns = array_values(array_filter([
                'payment_reference', 'payment_note', 'recorded_by',
                'confirmed_at', 'confirmed_by', 'disputed_at', 'dispute_reason',
            ], fn ($c) => Schema::hasColumn('invoices', $c)));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
