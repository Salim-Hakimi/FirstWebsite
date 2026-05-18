<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_categories') && ! Schema::hasColumn('finance_categories', 'color')) {
            Schema::table('finance_categories', function (Blueprint $table) {
                $table->string('color', 24)->nullable()->after('type');
            });
        }

        if (Schema::hasTable('finance_projects')) {
            Schema::table('finance_projects', function (Blueprint $table) {
                if (! Schema::hasColumn('finance_projects', 'category')) {
                    $table->string('category', 80)->nullable()->after('name');
                }

                if (! Schema::hasColumn('finance_projects', 'estimated_budget')) {
                    $table->unsignedBigInteger('estimated_budget')->default(0)->after('category');
                }

                if (! Schema::hasColumn('finance_projects', 'started_on')) {
                    $table->date('started_on')->nullable()->after('status');
                }

                if (! Schema::hasColumn('finance_projects', 'completed_on')) {
                    $table->date('completed_on')->nullable()->after('started_on');
                }

                if (! Schema::hasColumn('finance_projects', 'notes')) {
                    $table->text('notes')->nullable()->after('completed_on');
                }
            });
        }

        if (Schema::hasTable('finance_transactions')) {
            Schema::table('finance_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('finance_transactions', 'source_or_payee')) {
                    $table->string('source_or_payee', 180)->nullable()->after('transaction_date');
                }

                if (! Schema::hasColumn('finance_transactions', 'receipt_number')) {
                    $table->string('receipt_number', 80)->nullable()->after('source_or_payee');
                }

                if (! Schema::hasColumn('finance_transactions', 'status')) {
                    $table->string('status', 40)->default('paid')->after('payment_method');
                }

                if (! Schema::hasColumn('finance_transactions', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
