<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Two demo accounts were seeded (before SiacAdminSeeder was corrected to "SIARC")
// with the old brand in their display name: "Administrateur SIAC" and
// "Modérateur SIAC". The brand is SIARC — "SIAC" must never appear in
// user-visible text (it shows top-right on every admin page). Fix existing rows;
// fresh seeds already produce "SIARC".
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->where('name', 'like', '% SIAC')->orWhere('name', 'like', '% SIAC %')->get() as $u) {
            DB::table('users')->where('id', $u->id)
                ->update(['name' => str_replace(' SIAC', ' SIARC', $u->name)]);
        }
    }

    public function down(): void
    {
        // Not reversible to the incorrect "SIAC" text on purpose.
    }
};
