<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (! Schema::hasColumn('dorm_students', 'application_date')) {
                $table->date('application_date')->nullable()->after('profile_photo_path');
            }

            if (! Schema::hasColumn('dorm_students', 'education_score')) {
                $table->decimal('education_score', 5, 2)->nullable()->after('application_date');
            }

            if (! Schema::hasColumn('dorm_students', 'eligibility_score')) {
                $table->unsignedTinyInteger('eligibility_score')->nullable()->after('education_score');
            }

            if (! Schema::hasColumn('dorm_students', 'eligibility_notes')) {
                $table->text('eligibility_notes')->nullable()->after('eligibility_score');
            }

            if (! Schema::hasColumn('dorm_students', 'admitted_at')) {
                $table->timestamp('admitted_at')->nullable()->after('eligibility_notes');
            }

            if (! Schema::hasColumn('dorm_students', 'admission_decision_by')) {
                $table->foreignId('admission_decision_by')->nullable()->after('admitted_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (Schema::hasColumn('dorm_students', 'admission_decision_by')) {
                $table->dropConstrainedForeignId('admission_decision_by');
            }

            foreach (['admitted_at', 'eligibility_notes', 'eligibility_score', 'education_score', 'application_date'] as $column) {
                if (Schema::hasColumn('dorm_students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
