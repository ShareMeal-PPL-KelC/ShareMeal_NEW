<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('consumer')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('status')->default('active')->after('phone');
            $table->string('organization_name')->nullable()->after('status');
            $table->date('joined_at')->nullable()->after('organization_name');
            $table->unsignedInteger('transactions_count')->default(0)->after('joined_at');
            $table->unsignedInteger('warnings_count')->default(0)->after('transactions_count');
            $table->boolean('is_verified')->default(false)->after('warnings_count');
            $table->date('last_warning_at')->nullable()->after('is_verified');
            $table->text('warning_reason')->nullable()->after('last_warning_at');
            $table->date('blocked_at')->nullable()->after('warning_reason');
            $table->text('block_reason')->nullable()->after('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'status',
                'organization_name',
                'joined_at',
                'transactions_count',
                'warnings_count',
                'is_verified',
                'last_warning_at',
                'warning_reason',
                'blocked_at',
                'block_reason',
            ]);
        });
    }
};
