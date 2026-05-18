<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_audit_logs') && ! Schema::hasColumn('finance_audit_logs', 'performed_by')) {
            Schema::table('finance_audit_logs', function (Blueprint $table) {
                $table->foreignId('performed_by')
                    ->nullable()
                    ->after('new_values')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('finance_audit_logs') && Schema::hasColumn('finance_audit_logs', 'performed_by')) {
            Schema::table('finance_audit_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('performed_by');
            });
        }
    }
};
