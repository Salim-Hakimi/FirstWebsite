<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_donors')) {
            Schema::create('finance_donors', function (Blueprint $table) {
                $table->id();
                $table->string('name', 160);
                $table->string('phone', 60)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('finance_transactions')) {
            Schema::table('finance_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('finance_transactions', 'finance_donor_id')) {
                    $table->foreignId('finance_donor_id')->nullable()->after('finance_category_id')->constrained('finance_donors')->nullOnDelete();
                }

                if (! Schema::hasColumn('finance_transactions', 'payer_name')) {
                    $table->string('payer_name', 180)->nullable()->after('source_or_payee');
                }

                if (! Schema::hasColumn('finance_transactions', 'payee_name')) {
                    $table->string('payee_name', 180)->nullable()->after('payer_name');
                }

                if (! Schema::hasColumn('finance_transactions', 'description')) {
                    $table->text('description')->nullable()->after('notes');
                }

                if (! Schema::hasColumn('finance_transactions', 'attachment_required')) {
                    $table->boolean('attachment_required')->default(false)->after('description');
                }

                if (! Schema::hasColumn('finance_transactions', 'payment_month')) {
                    $table->unsignedTinyInteger('payment_month')->nullable()->after('attachment_required');
                }

                if (! Schema::hasColumn('finance_transactions', 'payment_year')) {
                    $table->unsignedSmallInteger('payment_year')->nullable()->after('payment_month');
                }

                if (! Schema::hasColumn('finance_transactions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (! Schema::hasTable('student_payments')) {
            Schema::create('student_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dorm_student_id')->constrained('dorm_students')->cascadeOnDelete();
                $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
                $table->unsignedTinyInteger('payment_month');
                $table->unsignedSmallInteger('payment_year');
                $table->unsignedBigInteger('expected_amount')->default(0);
                $table->unsignedBigInteger('paid_amount')->default(0);
                $table->unsignedBigInteger('remaining_amount')->default(0);
                $table->string('status', 40)->default('pending');
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('finance_attachments')) {
            Schema::create('finance_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_transaction_id')->constrained('finance_transactions')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('finance_audit_logs')) {
            Schema::create('finance_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
                $table->string('action', 40);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $categories = [
            ['name' => 'کمک نقدی خیرین', 'slug' => 'cash-donation', 'type' => 'income', 'color' => 'info'],
            ['name' => 'کمک موسسه‌ها', 'slug' => 'organization-support', 'type' => 'income', 'color' => 'info'],
            ['name' => 'درآمدهای دیگر', 'slug' => 'other-income', 'type' => 'income', 'color' => 'secondary'],
            ['name' => 'ساخت و ساز لیلیه', 'slug' => 'dorm-construction', 'type' => 'expense', 'color' => 'warning'],
            ['name' => 'معاش کارمندان و پرسونل', 'slug' => 'staff-salary', 'type' => 'expense', 'color' => 'danger'],
            ['name' => 'ترمیم و تجهیز کتابخانه', 'slug' => 'library-repair-equipment', 'type' => 'expense', 'color' => 'info'],
            ['name' => 'خریداری وسایل و نیازمندی‌ها', 'slug' => 'purchases-supplies', 'type' => 'expense', 'color' => 'secondary'],
            ['name' => 'مصارف دیگر لیلیه', 'slug' => 'other-dorm-expenses', 'type' => 'expense', 'color' => 'secondary'],
        ];

        $hasSlug = Schema::hasColumn('finance_categories', 'slug');

        foreach ($categories as $category) {
            $values = [
                'name' => $category['name'],
                'type' => $category['type'],
                'color' => $category['color'],
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            if ($hasSlug) {
                $values['slug'] = $category['slug'];
            }

            DB::table('finance_categories')->updateOrInsert(
                $hasSlug ? ['slug' => $category['slug']] : ['name' => $category['name'], 'type' => $category['type']],
                $values
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_audit_logs');
        Schema::dropIfExists('finance_attachments');
        Schema::dropIfExists('student_payments');
    }
};
