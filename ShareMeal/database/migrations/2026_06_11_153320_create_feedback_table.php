<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // e.g. 'fitur', 'bug', 'ui_ux', 'other'
            $table->string('subject');
            $table->text('description');
            $table->unsignedTinyInteger('rating')->nullable(); // 1 to 5 stars
            $table->text('screenshots')->nullable(); // JSON array of screenshot paths
            $table->string('status')->default('pending'); // pending, resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
