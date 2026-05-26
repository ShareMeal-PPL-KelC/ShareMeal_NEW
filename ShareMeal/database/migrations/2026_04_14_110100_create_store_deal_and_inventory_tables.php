<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->string('distance')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->string('address');
            $table->string('image')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('item');
            $table->unsignedInteger('original_price');
            $table->unsignedInteger('discount_price');
            $table->unsignedInteger('stock')->default(0);
            $table->string('expires_in')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->unsignedInteger('price');
            $table->unsignedInteger('discount_price')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->dateTime('expires_at');
            $table->string('status')->default('normal');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('stores');
    }
};
