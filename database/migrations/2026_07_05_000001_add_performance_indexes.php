<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('dorm_students', ['status', 'created_at'], 'dorm_students_status_created_idx');
        $this->addIndex('dorm_students', ['status', 'application_date'], 'dorm_students_status_application_idx');
        $this->addIndex('dorm_students', ['dorm_room_id', 'status'], 'dorm_students_room_status_idx');
        $this->addIndex('dorm_students', ['room_number', 'status'], 'dorm_students_room_number_status_idx');

        $this->addIndex('library_members', ['status', 'next_payment_due_at'], 'library_members_status_due_idx');
        $this->addIndex('library_members', ['payment_status', 'next_payment_due_at'], 'library_members_payment_due_idx');

        $this->addIndex('book_loans', ['status', 'due_at'], 'book_loans_status_due_idx');
        $this->addIndex('book_loans', ['library_member_id', 'status'], 'book_loans_member_status_idx');
        $this->addIndex('book_loans', ['book_id', 'status'], 'book_loans_book_status_idx');
        $this->addIndex('book_loans', ['book_copy_id', 'status'], 'book_loans_copy_status_idx');

        $this->addIndex('finance_transactions', ['type', 'transaction_date'], 'finance_transactions_type_date_perf_idx');
        $this->addIndex('finance_transactions', ['finance_category_id', 'transaction_date'], 'finance_transactions_category_date_idx');
        $this->addIndex('finance_transactions', ['dorm_student_id', 'transaction_date'], 'finance_transactions_student_date_idx');

        $this->addIndex('student_collections', ['type', 'collected_at'], 'student_collections_type_date_idx');
        $this->addIndex('student_collections', ['dorm_student_id', 'type'], 'student_collections_student_type_idx');

        $this->addIndex('food_finances', ['type', 'recorded_at'], 'food_finances_type_date_idx');
        $this->addIndex('food_finances', ['dorm_student_id', 'type'], 'food_finances_student_type_idx');

        $this->addIndex('membership_cards', ['scope', 'expires_at'], 'membership_cards_scope_expires_idx');
        $this->addIndex('membership_cards', ['cardable_type', 'cardable_id', 'scope'], 'membership_cards_cardable_scope_idx');
    }

    public function down(): void
    {
        $this->dropIndex('membership_cards', 'membership_cards_cardable_scope_idx');
        $this->dropIndex('membership_cards', 'membership_cards_scope_expires_idx');

        $this->dropIndex('food_finances', 'food_finances_student_type_idx');
        $this->dropIndex('food_finances', 'food_finances_type_date_idx');

        $this->dropIndex('student_collections', 'student_collections_student_type_idx');
        $this->dropIndex('student_collections', 'student_collections_type_date_idx');

        $this->dropIndex('finance_transactions', 'finance_transactions_student_date_idx');
        $this->dropIndex('finance_transactions', 'finance_transactions_category_date_idx');
        $this->dropIndex('finance_transactions', 'finance_transactions_type_date_perf_idx');

        $this->dropIndex('book_loans', 'book_loans_copy_status_idx');
        $this->dropIndex('book_loans', 'book_loans_book_status_idx');
        $this->dropIndex('book_loans', 'book_loans_member_status_idx');
        $this->dropIndex('book_loans', 'book_loans_status_due_idx');

        $this->dropIndex('library_members', 'library_members_payment_due_idx');
        $this->dropIndex('library_members', 'library_members_status_due_idx');

        $this->dropIndex('dorm_students', 'dorm_students_room_number_status_idx');
        $this->dropIndex('dorm_students', 'dorm_students_room_status_idx');
        $this->dropIndex('dorm_students', 'dorm_students_status_application_idx');
        $this->dropIndex('dorm_students', 'dorm_students_status_created_idx');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name): void {
            $blueprint->dropIndex($name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $name);
    }
};
