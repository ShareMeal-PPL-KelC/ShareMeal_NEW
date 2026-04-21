<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('distance')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->string('status')->default('available');
            $table->dateTime('claimed_at')->nullable();
            $table->string('tracking_status')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donation_items', function (Blueprint $table) {
            $table->id();
            $table->string('donation_id');
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->foreign('donation_id')->references('id')->on('donations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_items');
        Schema::dropIfExists('donations');
    }
};
