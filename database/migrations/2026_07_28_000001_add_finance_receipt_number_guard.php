<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_transactions') || ! Schema::hasColumn('finance_transactions', 'receipt_number')) {
            return;
        }

        if ($this->indexExists('finance_transactions', 'finance_transactions_receipt_number_unique')) {
            return;
        }

        $hasDuplicates = DB::table('finance_transactions')
            ->select('receipt_number')
            ->whereNotNull('receipt_number')
            ->where('receipt_number', '!=', '')
            ->groupBy('receipt_number')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            return;
        }

        Schema::table('finance_transactions', function (Blueprint $table): void {
            $table->unique('receipt_number', 'finance_transactions_receipt_number_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('finance_transactions') || ! $this->indexExists('finance_transactions', 'finance_transactions_receipt_number_unique')) {
            return;
        }

        Schema::table('finance_transactions', function (Blueprint $table): void {
            $table->dropIndex('finance_transactions_receipt_number_unique');
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $name);
    }
};
