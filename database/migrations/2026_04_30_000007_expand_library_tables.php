<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_members', function (Blueprint $table) {
            $table->string('member_code', 60)->nullable()->unique()->after('registered_by');
            $table->string('address', 220)->nullable()->after('education_place');
            $table->string('department_or_grade', 160)->nullable()->after('education_place');
            $table->date('membership_expires_at')->nullable()->after('joined_at');
            $table->string('payment_status', 30)->default('unpaid')->after('membership_fee');
            $table->date('last_paid_at')->nullable()->after('payment_status');
            $table->date('next_payment_due_at')->nullable()->after('last_paid_at');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn', 40)->nullable()->after('registered_by');
            $table->string('publisher', 160)->nullable()->after('author');
            $table->string('language', 80)->nullable()->after('publisher');
            $table->string('edition', 80)->nullable()->after('language');
            $table->unsignedSmallInteger('published_year')->nullable()->after('edition');
            $table->unsignedSmallInteger('pages')->nullable()->after('published_year');
            $table->string('barcode', 80)->nullable()->unique()->after('shelf_code');
        });

        Schema::table('book_loans', function (Blueprint $table) {
            $table->string('loan_code', 60)->nullable()->unique()->after('recorded_by');
            $table->string('condition_out', 120)->nullable()->after('due_at');
            $table->string('condition_in', 120)->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            $table->dropColumn(['loan_code', 'condition_out', 'condition_in']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['isbn', 'publisher', 'language', 'edition', 'published_year', 'pages', 'barcode']);
        });

        Schema::table('library_members', function (Blueprint $table) {
            $table->dropColumn(['member_code', 'address', 'department_or_grade', 'membership_expires_at', 'payment_status', 'last_paid_at', 'next_payment_due_at']);
        });
    }
};
