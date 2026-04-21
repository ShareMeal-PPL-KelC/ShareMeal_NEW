<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consumer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('store_name');
            $table->string('deal_item');
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default('pending');
            $table->string('consumer_name');
            $table->dateTime('booking_date');
            $table->string('pickup_time')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('review')->nullable();
            $table->string('pickup_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
