<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dorm_students') && Schema::hasColumn('dorm_students', 'registration_card_fee_amount')) {
            Schema::table('dorm_students', fn (Blueprint $table) => $table->dropColumn('registration_card_fee_amount'));
        }

        if (Schema::hasTable('library_members') && Schema::hasColumn('library_members', 'card_fee_amount')) {
            Schema::table('library_members', fn (Blueprint $table) => $table->dropColumn('card_fee_amount'));
        }

        if (Schema::hasTable('membership_cards') && Schema::hasColumn('membership_cards', 'fee_amount')) {
            Schema::table('membership_cards', fn (Blueprint $table) => $table->dropColumn('fee_amount'));
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dorm_students') && ! Schema::hasColumn('dorm_students', 'registration_card_fee_amount')) {
            Schema::table('dorm_students', function (Blueprint $table): void {
                $table->unsignedInteger('registration_card_fee_amount')->default(0)->after('dorm_expense_fee_amount');
            });
        }

        if (Schema::hasTable('library_members') && ! Schema::hasColumn('library_members', 'card_fee_amount')) {
            Schema::table('library_members', function (Blueprint $table): void {
                $table->unsignedInteger('card_fee_amount')->default(0)->after('membership_fee');
            });
        }

        if (Schema::hasTable('membership_cards') && ! Schema::hasColumn('membership_cards', 'fee_amount')) {
            Schema::table('membership_cards', function (Blueprint $table): void {
                $table->decimal('fee_amount', 12, 2)->default(0)->after('expires_at');
            });
        }
    }
};
