<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('dorm_students', 'registration_paid_amount')) {
            Schema::table('dorm_students', function (Blueprint $table): void {
                $table->unsignedInteger('registration_paid_amount')->default(0)->after('registration_payment_status');
            });
        }

        DB::table('dorm_students')
            ->where('registration_payment_status', 'paid')
            ->where(function ($query): void {
                $query
                    ->whereNull('registration_paid_amount')
                    ->orWhere('registration_paid_amount', 0);
            })
            ->update([
                'registration_paid_amount' => DB::raw('COALESCE(dorm_expense_fee_amount, 1000)'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('dorm_students', 'registration_paid_amount')) {
            Schema::table('dorm_students', function (Blueprint $table): void {
                $table->dropColumn('registration_paid_amount');
            });
        }
    }
};
