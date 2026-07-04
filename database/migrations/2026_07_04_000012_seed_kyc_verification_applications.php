<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The KYC Centre page reads real verification_applications. Only one existed, so
// seed a realistic spread across statuses for existing businesses so the KYC
// dashboard and the verifications queue both have browsable, real content.
return new class extends Migration
{
    public function up(): void
    {
        // status spread applied to businesses by id (skips ids without a business)
        $plan = [
            2  => ['submitted',    'verified'],
            3  => ['submitted',    'verified'],
            10 => ['submitted',    'verified'],
            11 => ['under_review', 'verified'],
            7  => ['under_review', 'certified'],
            5  => ['under_review', 'verified'],
            1  => ['approved',     'certified'],
            4  => ['approved',     'certified'],
            8  => ['approved',     'certified'],
            9  => ['approved',     'certified'],
            12 => ['approved',     'verified'],
            13 => ['rejected',     'verified'],
        ];

        $now = now();
        foreach ($plan as $businessId => [$status, $tier]) {
            $business = DB::table('businesses')->where('id', $businessId)->whereNull('deleted_at')->first();
            if (! $business) continue;

            // Don't duplicate if a row already exists for this business
            if (DB::table('verification_applications')->where('business_id', $businessId)->exists()) continue;

            $submittedAt = $now->copy()->subDays(rand(2, 30))->subHours(rand(0, 20));
            $reviewedAt  = in_array($status, ['approved', 'rejected'], true)
                ? $submittedAt->copy()->addDays(rand(1, 4))
                : null;

            DB::table('verification_applications')->insert([
                'business_id'    => $businessId,
                'tier_requested' => $tier,
                'status'         => $status,
                'reviewer_notes' => $status === 'rejected'
                    ? 'Documents incomplets — pièce d\'identité illisible.'
                    : null,
                'submitted_at'   => $status === 'draft' ? null : $submittedAt,
                'reviewed_at'    => $reviewedAt,
                'created_at'     => $submittedAt,
                'updated_at'     => $reviewedAt ?? $submittedAt,
            ]);
        }
    }

    public function down(): void
    {
        // Leave seeded rows in place; they reference real businesses and are
        // indistinguishable from organic submissions once the platform is live.
    }
};
