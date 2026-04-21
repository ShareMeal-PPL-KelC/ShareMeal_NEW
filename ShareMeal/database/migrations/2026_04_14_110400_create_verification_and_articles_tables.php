<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->dateTime('submitted_at');
            $table->string('status')->default('pending');
            $table->string('business_type');
            $table->text('description')->nullable();
            $table->unsignedInteger('beneficiaries')->nullable();
            $table->json('documents')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('status')->default('Draft');
            $table->date('published_on')->nullable();
            $table->string('author')->default('Admin System');
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('read_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('verification_applications');
    }
};
