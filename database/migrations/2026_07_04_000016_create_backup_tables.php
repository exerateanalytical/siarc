<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Backups & Logs page: real backup registry + system log entries, seeded
// verbatim from the design, plus admin-editable storage/system settings.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_records', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('type', 20)->default('full');   // full | database
            $table->string('mode', 20)->default('auto');    // auto | manual
            $table->string('contents')->nullable();          // "Base de données + Fichiers"
            $table->unsignedBigInteger('size_mb')->default(0);
            $table->string('status', 20)->default('success');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 12)->default('info');    // info | warning | error
            $table->string('event');
            $table->string('description');
            $table->string('actor')->default('Système');
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });

        // Seed backups (design shows 34 total; seed a realistic recent set)
        $base = \Carbon\Carbon::create(2025, 5, 12, 2, 30, 0);
        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $day  = $base->copy()->subDays($i);
            $isManual = $i === 4;
            $rows[] = [
                'filename' => $isManual ? 'backup_2025-05-08_14-15-22.zip' : 'backup_' . $day->format('Y-m-d') . '_02-30-00.zip',
                'type'     => $isManual ? 'database' : 'full',
                'mode'     => $isManual ? 'manual' : 'auto',
                'contents' => $isManual ? 'Base de données seulement' : 'Base de données + Fichiers',
                'size_mb'  => $isManual ? 2400 : (18600 - $i * 200),
                'status'   => 'success',
                'created_at' => $day, 'updated_at' => $day,
            ];
        }
        DB::table('backup_records')->insert($rows);

        $logBase = \Carbon\Carbon::create(2025, 5, 12, 8, 45, 12);
        DB::table('backup_logs')->insert([
            ['level' => 'info',    'event' => 'Backup automatique',   'description' => 'Sauvegarde complète créée avec succès',        'actor' => 'Système', 'logged_at' => $logBase, 'created_at' => $logBase, 'updated_at' => $logBase],
            ['level' => 'info',    'event' => 'Vérification planifiée','description' => 'Vérification d\'intégrité des backups : OK',    'actor' => 'Système', 'logged_at' => $logBase->copy()->subMinutes(5), 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'warning', 'event' => 'Espace disque',        'description' => 'Espace disque utilisé à 51%',                  'actor' => 'Système', 'logged_at' => $logBase->copy()->subMinutes(10), 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'info',    'event' => 'Nettoyage automatique','description' => '3 anciens backups supprimés (plus de 30 jours)','actor' => 'Système', 'logged_at' => $logBase->copy()->subMinutes(15), 'created_at' => now(), 'updated_at' => now()],
            ['level' => 'info',    'event' => 'Backup automatique',   'description' => 'Sauvegarde complète créée avec succès',        'actor' => 'Système', 'logged_at' => $logBase->copy()->subHours(6), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Storage + system settings (admin-editable, seeded to the design values)
        $now = now();
        foreach ([
            'storage_used_gb'  => '256.8',
            'storage_total_gb' => '500',
            'backup_server'    => 'GVNA-Server-01',
            'backup_os'        => 'Ubuntu 22.04 LTS',
            'backup_db'        => 'MySQL 8.0',
            'backup_path'      => '/backups/gvna',
            'backup_retention' => '30 jours',
        ] as $k => $v) {
            DB::table('platform_settings')->updateOrInsert(['key' => $k], ['value' => $v, 'updated_at' => $now, 'created_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('backup_records');
    }
};
