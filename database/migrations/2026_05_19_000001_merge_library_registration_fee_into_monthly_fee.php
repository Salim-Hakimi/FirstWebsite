<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_categories') || ! Schema::hasTable('finance_transactions')) {
            return;
        }

        $oldName = 'کتابخانه - فیس ثبت‌نام کتابخانه';
        $newName = 'کتابخانه - فیس ماهانه کتابخانه';

        $oldCategory = DB::table('finance_categories')
            ->where('type', 'income')
            ->where('name', $oldName)
            ->first();

        $monthlyCategory = DB::table('finance_categories')
            ->where('type', 'income')
            ->where('name', $newName)
            ->first();

        if (! $oldCategory && ! $monthlyCategory) {
            $monthlyCategoryId = DB::table('finance_categories')->insertGetId($this->categoryPayload($newName, 'library-monthly-fee'));
        } elseif (! $monthlyCategory && $oldCategory) {
            DB::table('finance_categories')
                ->where('id', $oldCategory->id)
                ->update($this->categoryPayload($newName, 'library-monthly-fee', false));

            $monthlyCategoryId = $oldCategory->id;
            $oldCategory = null;
        } else {
            $monthlyCategoryId = $monthlyCategory->id;
        }

        if ($oldCategory) {
            DB::table('finance_transactions')
                ->where('finance_category_id', $oldCategory->id)
                ->update(['finance_category_id' => $monthlyCategoryId]);

            DB::table('finance_categories')
                ->where('id', $oldCategory->id)
                ->delete();
        }
    }

    public function down(): void
    {
        //
    }

    private function categoryPayload(string $name, string $slug, bool $withCreatedAt = true): array
    {
        $payload = [
            'name' => $name,
            'type' => 'income',
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($withCreatedAt) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('finance_categories', 'color')) {
            $payload['color'] = '#0ea5a4';
        }

        if (Schema::hasColumn('finance_categories', 'slug')) {
            $payload['slug'] = $slug;
        }

        if (Schema::hasColumn('finance_categories', 'description')) {
            $payload['description'] = 'Library monthly fee income.';
        }

        return $payload;
    }
};
