<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_collections', function (Blueprint $table) {
            $table->dropForeign(['dorm_student_id']);
            $table->foreignId('dorm_student_id')->nullable()->change();
            $table->foreign('dorm_student_id')->references('id')->on('dorm_students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_collections', function (Blueprint $table) {
            $table->dropForeign(['dorm_student_id']);
            $table->foreignId('dorm_student_id')->nullable(false)->change();
            $table->foreign('dorm_student_id')->references('id')->on('dorm_students')->cascadeOnDelete();
        });
    }
};
