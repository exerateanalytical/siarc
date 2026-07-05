<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Persist the membership certificate so it can be looked up on the public
 * verification page (previously the number was derived on the fly and stored
 * nowhere, so verification could not read the real artisan back). Backfills every
 * existing business with the SAME derived number the certificate already shows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('certificate_no')->nullable()->after('slug');
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_no');
            $table->timestamp('certificate_expires_at')->nullable()->after('certificate_issued_at');
            $table->timestamp('certificate_revoked_at')->nullable()->after('certificate_expires_at');
            $table->index('certificate_no');
        });

        foreach (DB::table('businesses')->whereNull('deleted_at')->get(['id', 'created_at']) as $b) {
            $issued = $b->created_at ? Carbon::parse($b->created_at) : Carbon::now();
            $seed = md5('gvn-cert-' . $b->id);
            $no = 'GVN-' . $issued->year . '-' . str_pad((string) (hexdec(substr($seed, 0, 6)) % 10000000), 7, '0', STR_PAD_LEFT);
            // Rolling annual validity: the next signup-anniversary in the future.
            $expires = $issued->copy()->addYear();
            while ($expires->isPast()) {
                $expires->addYear();
            }
            DB::table('businesses')->where('id', $b->id)->update([
                'certificate_no' => $no,
                'certificate_issued_at' => $issued,
                'certificate_expires_at' => $expires,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['certificate_no']);
            $table->dropColumn(['certificate_no', 'certificate_issued_at', 'certificate_expires_at', 'certificate_revoked_at']);
        });
    }
};
