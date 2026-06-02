<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('library_members') && ! Schema::hasColumn('library_members', 'card_fee_amount')) {
            Schema::table('library_members', function (Blueprint $table) {
                $table->unsignedInteger('card_fee_amount')->default(50)->after('membership_fee');
            });
        }

        $this->addUniqueWhenClean('library_members', 'phone', 'library_members_phone_unique');
        $this->addUniqueWhenClean('library_members', 'email', 'library_members_email_unique');
        $this->addUniqueWhenClean('library_members', 'tazkira_number', 'library_members_tazkira_number_unique');
        $this->addUniqueWhenClean('dorm_students', 'phone', 'dorm_students_phone_unique');
        $this->addUniqueWhenClean('dorm_students', 'email', 'dorm_students_email_unique');
        $this->addUniqueWhenClean('dorm_students', 'tazkira_number', 'dorm_students_tazkira_number_unique');
    }

    public function down(): void
    {
        if (Schema::hasTable('library_members') && Schema::hasColumn('library_members', 'card_fee_amount')) {
            Schema::table('library_members', function (Blueprint $table) {
                $table->dropColumn('card_fee_amount');
            });
        }
    }

    private function addUniqueWhenClean(string $table, string $column, string $index): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $hasDuplicates = DB::table($table)
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $index) {
                $tableBlueprint->unique($column, $index);
            });
        } catch (Throwable) {
            //
        }
    }
};
