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
            if (!Schema::hasColumn('users', 'document_legalitas')) {
                $table->string('document_legalitas')->nullable()->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'document_izin')) {
                $table->string('document_izin')->nullable()->after('document_legalitas');
            }
            if (!Schema::hasColumn('users', 'document_identitas')) {
                $table->string('document_identitas')->nullable()->after('document_izin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['document_legalitas', 'document_izin', 'document_identitas']);
        });
    }
};
