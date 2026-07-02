<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();           // encrypted TOTP secret
            $table->timestamp('two_factor_confirmed_at')->nullable(); // TOTP enrollment confirmed
            $table->text('two_factor_recovery_codes')->nullable();    // encrypted JSON array of hashed codes
            $table->string('two_factor_channel', 20)->nullable();     // email | sms | whatsapp (OTP fallback channel)
        });

        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('credential_id', 255)->unique(); // base64url
            $table->text('public_key');                      // PEM
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // Reuse the existing otp_verifications table: codes become sha256
        // hashes (64 chars) and rows learn which channel delivered them.
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->string('code', 64)->change();
            $table->string('channel', 20)->nullable()->after('type'); // email | sms | whatsapp
        });
        DB::statement("ALTER TABLE otp_verifications MODIFY COLUMN type ENUM('email_verification','phone_verification','login','password_reset','enroll') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->dropColumn('channel');
            $table->string('code', 6)->change();
        });
        Schema::dropIfExists('user_passkeys');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes', 'two_factor_channel']);
        });
    }
};
