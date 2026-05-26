<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receiving_method')->default('pickup')->after('status'); // pickup, delivery
            $table->integer('delivery_fee')->default(0)->after('receiving_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['receiving_method', 'delivery_fee']);
        });
    }
};
