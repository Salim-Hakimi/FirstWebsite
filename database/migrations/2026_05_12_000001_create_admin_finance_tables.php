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
                $table->string('name', 120);
                $table->string('type', 20);
                $table->string('color', 24)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('finance_projects')) {
            Schema::create('finance_projects', function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('category', 80)->nullable();
                $table->unsignedBigInteger('estimated_budget')->default(0);
                $table->string('status', 40)->default('active');
                $table->date('started_on')->nullable();
                $table->date('completed_on')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('finance_transactions')) {
            Schema::create('finance_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number', 40)->unique();
                $table->string('type', 20);
                $table->foreignId('finance_category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
                $table->foreignId('finance_project_id')->nullable()->constrained('finance_projects')->nullOnDelete();
                $table->foreignId('dorm_student_id')->nullable()->constrained('dorm_students')->nullOnDelete();
                $table->unsignedBigInteger('expected_amount')->nullable();
                $table->unsignedBigInteger('amount');
                $table->date('transaction_date');
                $table->string('source_or_payee', 180)->nullable();
                $table->string('receipt_number', 80)->nullable();
                $table->string('payment_method', 40)->default('cash');
                $table->string('status', 40)->default('paid');
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (DB::table('finance_categories')->count() === 0) {
            DB::table('finance_categories')->insert([
                ['name' => 'کمک نقدی خیرین', 'type' => 'income', 'color' => 'info', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'کمک موسسه‌ها', 'type' => 'income', 'color' => 'info', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'درآمد متفرقه', 'type' => 'income', 'color' => 'secondary', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ساخت و ساز لیلیه', 'type' => 'expense', 'color' => 'warning', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'معاش کارمندان و پرسونل', 'type' => 'expense', 'color' => 'danger', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ترمیم کتابخانه', 'type' => 'expense', 'color' => 'info', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'خریداری وسایل و نیازمندی‌ها', 'type' => 'expense', 'color' => 'secondary', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مصارف دیگر لیلیه', 'type' => 'expense', 'color' => 'secondary', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_projects');
        Schema::dropIfExists('finance_categories');
    }
};
