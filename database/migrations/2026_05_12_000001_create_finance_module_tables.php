<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_categories')) {
            Schema::create('finance_categories', function (Blueprint $table) {
                $table->id();
                $table->string('type', 20);
                $table->string('name', 120);
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('finance_categories', 'slug')) {
            Schema::table('finance_categories', function (Blueprint $table) {
                $table->string('slug', 120)->nullable()->unique()->after('name');
            });
        }

        if (! Schema::hasColumn('finance_categories', 'description')) {
            Schema::table('finance_categories', function (Blueprint $table) {
                $table->text('description')->nullable()->after('slug');
            });
        }

        if (! Schema::hasTable('finance_transactions')) {
            Schema::create('finance_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number', 40)->unique();
                $table->string('type', 20);
                $table->foreignId('finance_category_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('dorm_student_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('amount');
                $table->unsignedInteger('expected_amount')->nullable();
                $table->date('transaction_date');
                $table->string('period', 80)->nullable();
                $table->string('payment_method', 40)->default('cash');
                $table->string('payment_status', 40)->default('completed');
                $table->string('payer_name', 160)->nullable();
                $table->string('payee_name', 160)->nullable();
                $table->string('donor_name', 160)->nullable();
                $table->string('donor_phone', 60)->nullable();
                $table->string('project_name', 160)->nullable();
                $table->string('attachment_path')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['type', 'transaction_date']);
                $table->index(['payment_status', 'transaction_date']);
            });
        }

        $now = now();
        foreach ([
            ['type' => 'income', 'name' => 'Student monthly fee', 'slug' => 'student-monthly-fee', 'description' => 'Dorm student monthly payments.'],
            ['type' => 'income', 'name' => 'Student registration fee', 'slug' => 'student-registration-fee', 'description' => 'Registration and admission related income.'],
            ['type' => 'income', 'name' => 'Donor contribution', 'slug' => 'donor-contribution', 'description' => 'Cash or estimated value of donor support.'],
            ['type' => 'income', 'name' => 'Organization support', 'slug' => 'organization-support', 'description' => 'Institutional support and grants.'],
            ['type' => 'income', 'name' => 'Other income', 'slug' => 'other-income', 'description' => 'Other dorm income.'],
            ['type' => 'expense', 'name' => 'Construction and repair', 'slug' => 'construction-and-repair', 'description' => 'Building, room, electrical, plumbing, and repair costs.'],
            ['type' => 'expense', 'name' => 'Guard salary', 'slug' => 'guard-salary', 'description' => 'Guard salary payments.'],
            ['type' => 'expense', 'name' => 'Staff salary', 'slug' => 'staff-salary', 'description' => 'Staff and worker salary payments.'],
            ['type' => 'expense', 'name' => 'Library repair', 'slug' => 'library-repair', 'description' => 'Library repair, books, labels, furniture, and equipment.'],
            ['type' => 'expense', 'name' => 'Food and kitchen', 'slug' => 'food-and-kitchen', 'description' => 'Food, kitchen, and daily supplies.'],
            ['type' => 'expense', 'name' => 'Utilities', 'slug' => 'utilities', 'description' => 'Electricity, water, internet, and services.'],
            ['type' => 'expense', 'name' => 'Other expense', 'slug' => 'other-expense', 'description' => 'Other dorm expenses.'],
        ] as $category) {
            DB::table('finance_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_categories');
    }
};
