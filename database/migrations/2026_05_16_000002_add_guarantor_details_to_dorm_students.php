<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            if (! Schema::hasColumn('dorm_students', 'guarantor_relation')) {
                $table->string('guarantor_relation', 80)->nullable()->after('guarantor_name');
            }

            if (! Schema::hasColumn('dorm_students', 'guarantor_tazkira_number')) {
                $table->string('guarantor_tazkira_number', 80)->nullable()->after('guarantor_phone');
            }

            if (! Schema::hasColumn('dorm_students', 'guarantor_job')) {
                $table->string('guarantor_job', 120)->nullable()->after('guarantor_tazkira_number');
            }

            if (! Schema::hasColumn('dorm_students', 'guarantor_permanent_address')) {
                $table->string('guarantor_permanent_address', 255)->nullable()->after('guarantor_job');
            }

            if (! Schema::hasColumn('dorm_students', 'guarantor_current_address')) {
                $table->string('guarantor_current_address', 255)->nullable()->after('guarantor_permanent_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            foreach ([
                'guarantor_current_address',
                'guarantor_permanent_address',
                'guarantor_job',
                'guarantor_tazkira_number',
                'guarantor_relation',
            ] as $column) {
                if (Schema::hasColumn('dorm_students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
