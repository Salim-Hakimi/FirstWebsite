<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dorm_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40);
            $table->string('title', 160);
            $table->decimal('amount', 12, 2);
            $table->date('spent_on');
            $table->string('paid_to', 160)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dorm_expenses');
    }
};
