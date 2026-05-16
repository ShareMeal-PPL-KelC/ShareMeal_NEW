<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('pending_phone')->nullable()->after('phone');
            $table->string('phone_otp_hash')->nullable()->after('pending_phone');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_hash');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_otp_expires_at');
            $table->timestamp('phone_change_available_at')->nullable()->after('phone_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'pending_phone',
                'phone_otp_hash',
                'phone_otp_expires_at',
                'phone_verified_at',
                'phone_change_available_at',
            ]);
        });
    }
};
