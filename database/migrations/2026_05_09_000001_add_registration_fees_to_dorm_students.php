<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (! Schema::hasColumn('dorm_students', 'guarantee_deposit_amount')) {
                $table->unsignedInteger('guarantee_deposit_amount')->default(1000)->after('eligibility_notes');
            }

            if (! Schema::hasColumn('dorm_students', 'dorm_expense_fee_amount')) {
                $table->unsignedInteger('dorm_expense_fee_amount')->default(1000)->after('guarantee_deposit_amount');
            }

            if (! Schema::hasColumn('dorm_students', 'registration_payment_status')) {
                $table->string('registration_payment_status', 30)->default('paid')->after('dorm_expense_fee_amount');
            }

            if (! Schema::hasColumn('dorm_students', 'registration_paid_at')) {
                $table->date('registration_paid_at')->nullable()->after('registration_payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            foreach (['registration_paid_at', 'registration_payment_status', 'dorm_expense_fee_amount', 'guarantee_deposit_amount'] as $column) {
                if (Schema::hasColumn('dorm_students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
