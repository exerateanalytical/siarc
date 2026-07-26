<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (! Schema::hasColumn('cities', 'slug')) {
                $table->string('slug')->nullable()->after('name_en');
            }
            if (! Schema::hasColumn('cities', 'latitude')) {
                $table->decimal('latitude', 10, 6)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('cities', 'longitude')) {
                $table->decimal('longitude', 10, 6)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('cities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['slug', 'latitude', 'longitude', 'is_active']);
        });
    }
};
