<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('business_pending_contact')->nullable()->after('business_contact');
            $table->string('business_contact_otp_hash')->nullable()->after('business_pending_contact');
            $table->timestamp('business_contact_otp_expires_at')->nullable()->after('business_contact_otp_hash');
            $table->timestamp('business_contact_verified_at')->nullable()->after('business_contact_otp_expires_at');
            $table->timestamp('business_contact_change_available_at')->nullable()->after('business_contact_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'business_pending_contact',
                'business_contact_otp_hash',
                'business_contact_otp_expires_at',
                'business_contact_verified_at',
                'business_contact_change_available_at',
            ]);
        });
    }
};
