<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (! Schema::hasColumn('dorm_students', 'registration_card_fee_amount')) {
                $table->unsignedInteger('registration_card_fee_amount')->default(50)->after('dorm_expense_fee_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (Schema::hasColumn('dorm_students', 'registration_card_fee_amount')) {
                $table->dropColumn('registration_card_fee_amount');
            }
        });
    }
};
