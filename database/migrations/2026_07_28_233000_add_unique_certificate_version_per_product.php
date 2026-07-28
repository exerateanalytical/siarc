<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One certificate per product per version, enforced by the database.
 *
 * Certificates were issued lazily on first view, from a lookup followed by an
 * insert with nothing in between. Two visitors opening the same product at the
 * same moment both found no certificate and both went on to write one. Nothing
 * in the application stopped the second.
 *
 * The unique index on `certificate_no` happened to catch most of it, because
 * the number is derived from the product id and the version — but only within
 * one calendar year, since the year is part of the number, and it caught it by
 * throwing an unhandled query error at a visitor rather than by expressing the
 * rule. This index states the rule the register actually holds: a product has
 * at most one certificate at any given version. Superseded and revoked rows
 * keep their place, because they carry later version numbers.
 *
 * A partial index over live rows only was the other candidate. MySQL has none,
 * and a unique index over (product_id, revoked_at) would not work in its place:
 * NULLs do not collide, so two unrevoked rows would still be permitted — which
 * is exactly the case that needed preventing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Any duplicate already in the table would make the index uncreatable.
        // There should be none — the certificate_no index made them unlikely —
        // but a migration that fails halfway on a live register is worse than
        // one that says what it found, so retire the extras rather than delete
        // them: a certificate somebody downloaded must not vanish from the log.
        $duplicates = DB::table('product_certificates')
            ->select('product_id', 'version')
            ->groupBy('product_id', 'version')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $group) {
            $ids = DB::table('product_certificates')
                ->where('product_id', $group->product_id)
                ->where('version', $group->version)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            // The earliest is the one that was handed out first; the rest get a
            // later version so they remain readable but no longer read as live.
            foreach (array_slice($ids, 1) as $offset => $id) {
                DB::table('product_certificates')->where('id', $id)->update([
                    'version'    => $group->version + $offset + 1,
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('product_certificates', function (Blueprint $table) {
            $table->unique(['product_id', 'version'], 'product_certificates_product_version_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_certificates', function (Blueprint $table) {
            $table->dropUnique('product_certificates_product_version_unique');
        });
    }
};
