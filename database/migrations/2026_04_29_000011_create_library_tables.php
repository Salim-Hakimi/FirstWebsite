<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name', 120);
            $table->string('father_name', 120)->nullable();
            $table->string('phone', 30);
            $table->string('email', 120)->nullable();
            $table->string('tazkira_number', 80)->nullable();
            $table->string('education_place', 160)->nullable();
            $table->unsignedInteger('membership_fee')->default(0);
            $table->date('joined_at')->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->string('author', 160)->nullable();
            $table->string('category', 120)->nullable();
            $table->string('shelf_code', 80)->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->string('status', 40)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('borrowed_at');
            $table->date('due_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->unsignedInteger('fine_amount')->default(0);
            $table->string('status', 40)->default('borrowed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('books');
        Schema::dropIfExists('library_members');
    }
};
