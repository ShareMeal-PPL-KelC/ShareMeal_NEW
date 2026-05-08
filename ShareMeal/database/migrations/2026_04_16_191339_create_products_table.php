<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID Mitra
            $table->string('name');
            $table->string('category');
            $table->integer('price');
            $table->integer('discount_price')->default(0);
            $table->integer('stock');
            $table->dateTime('expires_at');
            $table->string('status')->default('normal'); // normal, flash-sale
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
