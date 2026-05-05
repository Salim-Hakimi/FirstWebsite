<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dorm_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name', 120);
            $table->string('father_name', 120);
            $table->string('phone', 30);
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('tazkira_number', 80);
            $table->string('education_place', 160);
            $table->string('department_or_grade', 160)->nullable();
            $table->string('province', 80)->nullable();
            $table->string('room_number', 40)->nullable();
            $table->string('bed_number', 40)->nullable();
            $table->string('guarantor_name', 120)->nullable();
            $table->string('guarantor_phone', 30)->nullable();
            $table->json('document_names')->nullable();
            $table->string('status', 40)->default('active');
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dorm_students');
    }
};
