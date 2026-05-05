<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_cards', function (Blueprint $table) {
            $table->id();
            $table->morphs('cardable');
            $table->string('scope', 30);
            $table->string('card_number', 60)->unique();
            $table->string('holder_name', 160);
            $table->string('father_name', 160)->nullable();
            $table->date('issued_at');
            $table->date('expires_at');
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->string('payment_status', 30)->default('unpaid');
            $table->date('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_cards');
    }
};
