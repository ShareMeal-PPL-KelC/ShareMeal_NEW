<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('can_delivery')->default(false)->after('business_opening_hours');
            $table->integer('delivery_fee')->default(0)->after('can_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['can_delivery', 'delivery_fee']);
        });
    }
};
