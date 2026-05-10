<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_members', function (Blueprint $table) {
            if (! Schema::hasColumn('library_members', 'monthly_fee_daily_fine')) {
                $table->unsignedInteger('monthly_fee_daily_fine')->default(20)->after('membership_fee');
            }

            if (! Schema::hasColumn('library_members', 'monthly_fee_fine_amount')) {
                $table->unsignedInteger('monthly_fee_fine_amount')->default(0)->after('monthly_fee_daily_fine');
            }

            if (! Schema::hasColumn('library_members', 'last_fee_reminder_at')) {
                $table->date('last_fee_reminder_at')->nullable()->after('next_payment_due_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('library_members', function (Blueprint $table) {
            foreach (['last_fee_reminder_at', 'monthly_fee_fine_amount', 'monthly_fee_daily_fine'] as $column) {
                if (Schema::hasColumn('library_members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
