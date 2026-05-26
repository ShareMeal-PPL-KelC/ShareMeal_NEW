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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('business_document');
            $table->string('document_ktp')->nullable()->after('is_verified');
            $table->string('document_siup')->nullable()->after('document_ktp');
            $table->string('document_nib')->nullable()->after('document_siup');
            $table->string('document_halal')->nullable()->after('document_nib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_document')->nullable();
            $table->dropColumn(['document_ktp', 'document_siup', 'document_nib', 'document_halal']);
        });
    }
};
