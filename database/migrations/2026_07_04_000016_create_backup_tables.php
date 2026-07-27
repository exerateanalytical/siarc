<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Backups & Logs page: real backup registry + system log entries, plus
// admin-editable storage/system settings. Ships empty.
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

        // No seed data. Backup records, log lines and storage/system settings are
        // written by real operations (admin.backups.create) or entered by an
        // operator in Settings — the platform must never invent its own
        // infrastructure facts or a history of backups that never ran.
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('backup_records');
    }
};
