<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('business_type');
            $table->text('business_address')->nullable()->after('business_name');
            $table->string('business_contact')->nullable()->after('business_address');
            $table->string('business_opening_hours')->nullable()->after('business_contact');
            $table->text('business_description')->nullable()->after('business_opening_hours');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'business_address',
                'business_contact',
                'business_opening_hours',
                'business_description',
            ]);
        });
    }
};
