<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for hot filter columns + soft deletes on tables whose rows
 * back dispute/audit trails and must survive user-facing deletion.
 *
 * Guarded with hasIndex/hasColumn so a partially-applied run (or an
 * index that already exists from an earlier migration) is skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Public listings filter on status alone; an existing
        // (business_id, status) composite can't serve that
        $this->addIndex('products', ['status']);
        $this->addIndex('conversations', ['status']);
        $this->addIndex('conversations', ['business_id', 'status']);
        $this->addIndex('api_keys', ['is_active']);
        $this->addIndex('support_tickets', ['status']);

        foreach (['conversations', 'messages', 'support_tickets'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }
    }

    public function down(): void
    {
        $this->dropIndex('products', ['status']);
        $this->dropIndex('conversations', ['status']);
        $this->dropIndex('conversations', ['business_id', 'status']);
        $this->dropIndex('api_keys', ['is_active']);
        $this->dropIndex('support_tickets', ['status']);

        foreach (['conversations', 'messages', 'support_tickets'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }
    }

    private function addIndex(string $table, array $columns): void
    {
        if (! Schema::hasIndex($table, $this->indexName($table, $columns))) {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns));
        }
    }

    private function dropIndex(string $table, array $columns): void
    {
        if (Schema::hasIndex($table, $this->indexName($table, $columns))) {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex($columns));
        }
    }

    private function indexName(string $table, array $columns): string
    {
        return $table . '_' . implode('_', $columns) . '_index';
    }
};
