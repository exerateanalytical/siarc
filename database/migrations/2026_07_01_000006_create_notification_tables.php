<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. new_message, quote_received
            $table->enum('type', ['email', 'sms', 'push']);
            $table->string('subject_fr')->nullable();
            $table->string('subject_en')->nullable();
            $table->text('body_fr');
            $table->text('body_en');
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->enum('channel', ['email', 'sms', 'push']);
            $table->string('recipient'); // email address or phone
            $table->enum('status', ['sent', 'delivered', 'failed', 'bounced'])->default('sent');
            $table->timestamp('sent_at')->useCurrent();
            $table->index(['user_id', 'channel']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('channel', ['email', 'sms', 'push']);
            $table->string('category'); // new_message, quote_received, verification_update, etc.
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'channel', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
    }
};
