<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// The platform brand is SIARC (Salon International de l'Artisanat du Cameroun).
// The seeded flagship event and demo accounts carried the old SIAC (aquaculture)
// branding — rename them in place so existing databases match the new seeders.
return new class extends Migration
{
    public function up(): void
    {
        $artisanatId = DB::table('industries')->where('slug', 'artisanat')->value('id');

        DB::table('events')->where('slug', 'siac-2026')->update([
            'slug'           => 'siarc-2026',
            'industry_id'    => $artisanatId,
            'name_fr'        => "Salon International de l'Artisanat du Cameroun (SIARC) 2026",
            'name_en'        => 'Cameroon International Craft Fair (SIARC) 2026',
            'description_fr' => "Le plus grand salon dédié à l'artisanat au Cameroun, réunissant artisans, coopératives, acheteurs professionnels, investisseurs et institutions autour des filières artisanales nationales.",
            'description_en' => "Cameroon's largest craft trade fair, bringing together artisans, cooperatives, professional buyers, investors and institutions around the national craft industries.",
        ]);

        foreach (DB::table('users')->where('email', 'like', '%@siac2026.cm')->get(['id', 'email']) as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'email'    => str_replace('@siac2026.cm', '@siarc2026.cm', $user->email),
                'password' => Hash::make('Demo@SIARC2026'),
            ]);
        }

        DB::table('users')->where('email', 'admin@artisanatcameroun.cm')
            ->update(['password' => Hash::make('Admin@SIARC2026')]);
        DB::table('users')->where('email', 'moderateur@artisanatcameroun.cm')
            ->update(['password' => Hash::make('Modo@SIARC2026')]);
    }

    public function down(): void
    {
        DB::table('events')->where('slug', 'siarc-2026')->update(['slug' => 'siac-2026']);

        foreach (DB::table('users')->where('email', 'like', '%@siarc2026.cm')->get(['id', 'email']) as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'email'    => str_replace('@siarc2026.cm', '@siac2026.cm', $user->email),
                'password' => Hash::make('Demo@SIAC2026'),
            ]);
        }

        DB::table('users')->where('email', 'admin@artisanatcameroun.cm')
            ->update(['password' => Hash::make('Admin@SIAC2026')]);
        DB::table('users')->where('email', 'moderateur@artisanatcameroun.cm')
            ->update(['password' => Hash::make('Modo@SIAC2026')]);
    }
};
