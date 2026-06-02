<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->time('pickup_start_time')->nullable()->after('expires_at');
            $table->time('pickup_end_time')->nullable()->after('pickup_start_time');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->time('pickup_start_time')->nullable()->after('pickup_time');
            $table->time('pickup_end_time')->nullable()->after('pickup_start_time');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->time('pickup_start_time')->nullable()->after('pickup_time');
            $table->time('pickup_end_time')->nullable()->after('pickup_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pickup_start_time', 'pickup_end_time']);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['pickup_start_time', 'pickup_end_time']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pickup_start_time', 'pickup_end_time']);
        });
    }
};
